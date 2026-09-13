<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Epoch;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Withdrawal;

class AdminOverviewService
{
    public function __construct(
        private EpochService $epochs,
        private PlatformSettingsService $settings,
    ) {}

    /** @return array<string, mixed> */
    public function metrics(): array
    {
        $pendingWithdrawals = Withdrawal::query()->where('status', Withdrawal::STATUS_PENDING);
        $lastEpoch = Epoch::query()->latest('number')->first();

        return [
            'total_users' => User::query()->count(),
            'users_today' => User::query()->whereDate('created_at', today())->count(),
            'blocked_users' => User::query()->where('is_blocked', true)->count(),
            'kyc_pending' => User::query()->where('kyc_status', User::KYC_PENDING)->count(),
            'active_contracts' => Contract::query()->where('status', 'active')->count(),
            'total_tflops' => (int) Contract::query()->where('status', 'active')->sum('tflops'),
            'open_tickets' => SupportTicket::query()->where('status', SupportTicket::STATUS_OPEN)->count(),
            'pending_withdrawals_count' => (clone $pendingWithdrawals)->count(),
            'pending_withdrawals_sum' => (float) (clone $pendingWithdrawals)->sum('amount'),
            'current_epoch' => $this->epochs->currentEpochNumber(),
            'last_epoch_rewards' => $lastEpoch ? (float) $lastEpoch->total_rewards : 0.0,
            'last_epoch_number' => $lastEpoch?->number,
            'reward_rate' => $this->settings->rewardRate(),
            'epochs_per_day' => $this->settings->epochsPerDay(),
            'token_symbol' => $this->settings->tokenSymbol(),
        ];
    }
}
