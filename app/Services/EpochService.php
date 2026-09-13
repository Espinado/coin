<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Contract;
use App\Models\Epoch;
use App\Models\EpochReward;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class EpochService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function currentEpochNumber(): int
    {
        return (int) Epoch::query()->max('number');
    }

    public function nextEpochNumber(): int
    {
        return $this->currentEpochNumber() + 1;
    }

    public function runSettlement(?Admin $admin = null): Epoch
    {
        $rewardRate = $this->settings->rewardRate();
        $epochsPerDay = $this->settings->epochsPerDay();
        $symbol = $this->settings->tokenSymbol();
        $number = $this->nextEpochNumber();

        return DB::transaction(function () use ($admin, $rewardRate, $epochsPerDay, $symbol, $number) {
            $epoch = Epoch::query()->create([
                'number' => $number,
                'reward_rate' => $rewardRate,
                'epochs_per_day' => $epochsPerDay,
                'triggered_by' => $admin?->id,
                'completed_at' => now(),
            ]);

            $contracts = Contract::query()
                ->with(['user.wallet', 'plan'])
                ->where('status', 'active')
                ->get();

            $totalRewards = 0.0;
            $count = 0;

            foreach ($contracts as $contract) {
                $multiplier = (float) ($contract->plan?->reward_multiplier ?? 1);
                $amount = round($contract->tflops * $rewardRate * $multiplier, 2);

                if ($amount <= 0) {
                    continue;
                }

                $user = $contract->user;
                $wallet = $this->ensureWallet($user);

                $wallet->increment('balance', $amount);
                $wallet->increment('available', $amount);
                $wallet->increment('pending', round($amount * 0.1, 2));

                $contract->increment('accrued_amount', $amount);
                if ($contract->duration_days > 0) {
                    $contract->increment('days_elapsed');
                    $contract->update([
                        'progress_percent' => min(100, (int) round(($contract->days_elapsed / $contract->duration_days) * 100)),
                    ]);
                    if ($contract->days_elapsed >= $contract->duration_days) {
                        $contract->update(['status' => 'completed']);
                    }
                }

                EpochReward::query()->create([
                    'epoch_id' => $epoch->id,
                    'user_id' => $user->id,
                    'contract_id' => $contract->id,
                    'amount' => $amount,
                ]);

                $sortOrder = (int) WalletTransaction::query()->where('user_id', $user->id)->max('sort_order') + 1;

                WalletTransaction::query()->create([
                    'user_id' => $user->id,
                    'occurred_label' => now()->format('M j · H:i'),
                    'type' => 'Reward',
                    'source' => 'Epoch '.$number,
                    'amount_label' => '+'.number_format($amount, 2, '.', '').' '.$symbol,
                    'amount_tone' => 'positive',
                    'status_label' => 'COMPLETED',
                    'sort_order' => $sortOrder,
                ]);

                $this->refreshUserEpochStats($user, $amount, $number, $epochsPerDay);

                $totalRewards += $amount;
                $count++;
            }

            $epoch->update([
                'contracts_settled' => $count,
                'total_rewards' => $totalRewards,
            ]);

            return $epoch->fresh(['rewards', 'triggeredByAdmin']);
        });
    }

    private function ensureWallet(User $user): Wallet
    {
        return $user->wallet ?? Wallet::query()->create([
            'user_id' => $user->id,
            'balance' => 0,
            'available' => 0,
            'pending' => 0,
            'min_withdrawal_label' => number_format($this->settings->minWithdrawal(), 2, '.', ''),
        ]);
    }

    private function refreshUserEpochStats(User $user, float $epochReward, int $number, int $epochsPerDay): void
    {
        $daily = round($epochReward * $epochsPerDay, 2);
        $secondsToNext = (int) floor(86400 / max(1, $epochsPerDay));
        $timeLabel = gmdate('H:i:s', $secondsToNext);

        $user->update([
            'epoch_label' => $number.' · '.$timeLabel,
            'expected_daily_reward' => $daily,
            'avg_epoch_label' => number_format($epochReward, 2, '.', ''),
        ]);
    }
}
