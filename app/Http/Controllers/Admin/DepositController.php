<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\PaymentWebhookLog;
use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    use AdminListQuery;

    public function index(Request $request): View
    {
        app(DepositService::class)->expireAllDuePending(PaymentStatusLog::SOURCE_ADMIN);

        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = Deposit::query()
            ->with(['user', 'confirmedBy'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $like = '%'.$search.'%';

                    $inner->whereRaw("CONCAT('TOP-', id) LIKE ?", [$like])
                        ->orWhere('external_reference', 'like', $like)
                        ->orWhere('gateway_uniq_id', 'like', $like)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('email', 'like', $like)
                            ->orWhere('name', 'like', $like)
                            ->orWhere('account_slug', 'like', $like));
                });
            });

        $this->adminApplySort($request, $query, [
            'reference' => 'id',
            'amount' => 'amount',
            'status' => 'status',
            'created_at' => 'created_at',
        ], 'created_at', 'desc', [
            'user' => fn ($depositQuery, $direction) => $this->adminOrderByRelatedUser($depositQuery, 'email', $direction),
        ]);

        return view('admin.deposits.index', [
            'deposits' => $this->adminPaginate($query, $request),
            'statuses' => $this->depositStatuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(Deposit $deposit): View
    {
        $deposit = app(DepositService::class)->expireIfDue($deposit, PaymentStatusLog::SOURCE_ADMIN) ?? $deposit;
        $deposit->load(['user.wallet', 'confirmedBy']);

        $webhookLogs = PaymentWebhookLog::journalForDeposit($deposit);
        $webhookLogsOnlyDuplicates = $webhookLogs->isNotEmpty()
            && $webhookLogs->every(fn (PaymentWebhookLog $log) => $log->isDuplicateResult());

        return view('admin.deposits.show', [
            'deposit' => $deposit,
            'webhookLogs' => $webhookLogs,
            'webhookLogsOnlyDuplicates' => $webhookLogsOnlyDuplicates,
        ]);
    }

    public function status(Deposit $deposit): JsonResponse
    {
        $deposit = app(DepositService::class)->expireIfDue($deposit, PaymentStatusLog::SOURCE_ADMIN) ?? $deposit->fresh();

        return response()->json([
            'id' => $deposit->id,
            'status' => $deposit->status,
        ]);
    }

    /** @return array<string, string> */
    private function depositStatuses(): array
    {
        return [
            Deposit::STATUS_PENDING => __('coin.admin.deposit_status_pending'),
            Deposit::STATUS_CONFIRMED => __('coin.admin.deposit_status_confirmed'),
            Deposit::STATUS_REJECTED => __('coin.admin.deposit_status_rejected'),
        ];
    }
}
