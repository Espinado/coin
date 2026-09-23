<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class WithdrawalController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = Withdrawal::query()
            ->with(['user', 'processedByAdmin'])
            ->when($status === Withdrawal::STATUS_PENDING, fn ($query) => $query->whereIn('status', Withdrawal::openStatuses()))
            ->when($status !== '' && $status !== Withdrawal::STATUS_PENDING, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'reference' => 'reference',
            'amount' => 'amount',
            'status' => 'status',
            'created_at' => 'created_at',
        ], 'created_at', 'desc', [
            'user' => fn ($withdrawalQuery, $direction) => $this->adminOrderByRelatedUser($withdrawalQuery, 'email', $direction),
        ]);

        return view('admin.withdrawals.index', [
            'withdrawals' => $this->adminPaginate($query, $request),
            'statuses' => Withdrawal::adminStatuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(Withdrawal $withdrawal): View
    {
        $withdrawal->load(['user.wallet', 'user.contracts.plan', 'processedByAdmin']);

        $pollLogs = PaymentWebhookLog::query()
            ->where('withdrawal_id', $withdrawal->id)
            ->where('event_type', 'payout_poll')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.withdrawals.show', [
            'withdrawal' => $withdrawal,
            'statuses' => $withdrawal->adminSelectableStatuses(),
            'pollLogs' => $pollLogs,
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal, WithdrawalService $withdrawals): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $withdrawal = $withdrawals->approveAndDispatch(
                $withdrawal,
                $request->user('admin'),
                $validated['admin_note'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        $flashKey = $withdrawal->status === Withdrawal::STATUS_PAID
            ? 'coin.admin.withdrawal_approved_paid'
            : 'coin.admin.withdrawal_approved_processing';

        return $this->adminSuccess($flashKey, 'admin.withdrawals.show', ['withdrawal' => $withdrawal]);
    }

    public function updateStatus(Request $request, Withdrawal $withdrawal, WithdrawalService $withdrawals): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Withdrawal::adminStatuses()))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $withdrawals->updateStatus(
                $withdrawal,
                $validated['status'],
                $request->user('admin'),
                $validated['admin_note'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.payout_status_updated', 'admin.withdrawals.show', ['withdrawal' => $withdrawal]);
    }
}
