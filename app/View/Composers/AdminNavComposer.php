<?php

namespace App\View\Composers;

use App\Models\SupportTicket;
use App\Models\Withdrawal;
use Illuminate\View\View;

class AdminNavComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'unreadSupportCount' => SupportTicket::totalUnreadForAdmin(),
            'pendingWithdrawalsCount' => Withdrawal::query()->where('status', Withdrawal::STATUS_PENDING)->count(),
        ]);
    }
}
