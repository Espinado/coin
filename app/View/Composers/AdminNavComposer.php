<?php

namespace App\View\Composers;

use App\Models\Deposit;
use App\Models\PlanChangeRequest;
use App\Models\SupportTicket;
use App\Models\Withdrawal;
use Illuminate\View\View;

class AdminNavComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'unreadSupportCount' => SupportTicket::totalUnreadForAdmin(),
            'pendingDepositsCount' => Deposit::query()->where('status', Deposit::STATUS_PENDING)->count(),
            'pendingWithdrawalsCount' => Withdrawal::pendingCountForAdmin(),
            'pendingPlanChangesCount' => PlanChangeRequest::pendingCountForAdmin(),
        ]);
    }
}
