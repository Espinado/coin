<?php

namespace App\View\Composers;

use App\Models\Admin;
use App\Models\Deposit;
use App\Models\PlanChangeRequest;
use App\Models\SupportTicket;
use App\Models\Withdrawal;
use App\Services\AdminAuthorization;
use App\Support\AdminAbility;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminNavComposer
{
    public function __construct(
        private AdminAuthorization $authorization,
    ) {}

    public function compose(View $view): void
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        $canManageDeposits = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageDeposits);
        $canManageWithdrawals = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageWithdrawals);
        $canManagePlans = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManagePlans);

        $view->with([
            'unreadSupportCount' => SupportTicket::totalUnreadForAdmin(),
            'pendingDepositsCount' => $canManageDeposits
                ? Deposit::query()->where('status', Deposit::STATUS_PENDING)->count()
                : 0,
            'pendingWithdrawalsCount' => $canManageWithdrawals
                ? Withdrawal::pendingCountForAdmin()
                : 0,
            'pendingPlanChangesCount' => PlanChangeRequest::pendingCountForAdmin(),
            'canManageDeposits' => $canManageDeposits,
            'canManageWithdrawals' => $canManageWithdrawals,
            'canManagePlans' => $canManagePlans,
            'canAccessFinance' => $canManageDeposits || $canManageWithdrawals || $canManagePlans,
            'canAccessPaymentLogs' => $canManageDeposits,
            'financeNavUrl' => $canManageDeposits
                ? route('admin.deposits.index')
                : ($canManageWithdrawals
                    ? route('admin.withdrawals.index')
                    : ($canManagePlans ? route('admin.profit-accrual.index') : route('admin.dashboard'))),
        ]);
    }
}
