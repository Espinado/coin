<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    use AdminListQuery;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = Deposit::query()
            ->with(['user', 'confirmedBy'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    if (ctype_digit($search)) {
                        $inner->where('id', (int) $search);
                    }

                    $inner->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'id' => 'id',
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
        $deposit->load(['user.wallet', 'confirmedBy']);

        return view('admin.deposits.show', [
            'deposit' => $deposit,
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
