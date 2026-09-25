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

        $canManageUsers = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageUsers);
        $canManageDeposits = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageDeposits);
        $canManageWithdrawals = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageWithdrawals);
        $canManagePlanChanges = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManagePlanChanges);
        $canManagePlans = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManagePlans);
        $canManageSettings = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageSettings);
        $canManageAdmins = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageAdmins);
        $canManageLegal = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageLegal);
        $canManageBroadcasts = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageBroadcasts);
        $canManageSupport = $admin !== null && $this->authorization->allows($admin, AdminAbility::ManageSupport);

        $view->with([
            'unreadSupportCount' => $canManageSupport ? SupportTicket::totalUnreadForAdmin() : 0,
            'pendingDepositsCount' => $canManageDeposits
                ? Deposit::query()->where('status', Deposit::STATUS_PENDING)->count()
                : 0,
            'pendingWithdrawalsCount' => $canManageWithdrawals
                ? Withdrawal::pendingCountForAdmin()
                : 0,
            'pendingPlanChangesCount' => $canManagePlanChanges
                ? PlanChangeRequest::pendingCountForAdmin()
                : 0,
            'canManageUsers' => $canManageUsers,
            'canManageDeposits' => $canManageDeposits,
            'canManageWithdrawals' => $canManageWithdrawals,
            'canManagePlanChanges' => $canManagePlanChanges,
            'canManagePlans' => $canManagePlans,
            'canManageSettings' => $canManageSettings,
            'canManageAdmins' => $canManageAdmins,
            'canManageLegal' => $canManageLegal,
            'canManageBroadcasts' => $canManageBroadcasts,
            'canManageSupport' => $canManageSupport,
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
