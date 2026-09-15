<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;

class AdminOverviewService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    /** @return array<string, mixed> */
    public function metrics(): array
    {
        $pendingWithdrawals = Withdrawal::query()->where('status', Withdrawal::STATUS_PENDING);

        return [
            'total_users' => User::query()->count(),
            'users_today' => User::query()->whereDate('created_at', today())->count(),
            'blocked_users' => User::query()->where('is_blocked', true)->count(),
            'kyc_pending' => User::query()->where('kyc_status', User::KYC_PENDING)->count(),
            'active_contracts' => Contract::query()->active()->count(),
            'total_locked' => (float) Contract::query()->active()->sum('principal_amount'),
            'open_tickets' => SupportTicket::query()->where('status', SupportTicket::STATUS_OPEN)->count(),
            'pending_withdrawals_count' => (clone $pendingWithdrawals)->count(),
            'pending_withdrawals_sum' => (float) (clone $pendingWithdrawals)->sum('amount'),
            'today_profit' => (float) WalletTransaction::query()
                ->where('type', 'Daily profit')
                ->whereDate('occurred_at', today())
                ->sum('amount'),
            'token_symbol' => $this->settings->tokenSymbol(),
        ];
    }
}
