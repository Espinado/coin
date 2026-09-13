<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function forUser(User $user): array
    {
        $user->load([
            'wallet',
            'contracts.plan',
            'walletTransactions',
            'rewardPeriodTotals',
            'referralProfile',
            'referralAccruals',
            'supportTickets',
        ]);

        $wallet = $user->wallet;
        $primaryContract = $user->contracts->firstWhere('status', 'active');
        $plans = Plan::query()->where('is_active', true)->orderBy('sort_order')->get();

        return [
            'wallet' => $wallet,
            'plans' => $plans,
            'contracts' => $user->contracts,
            'activeContracts' => $user->contracts->where('status', 'active')->values(),
            'completedContracts' => $user->contracts->where('status', 'completed')->values(),
            'transactions' => $user->walletTransactions->sortBy('sort_order')->values(),
            'periodTotals' => $user->rewardPeriodTotals->keyBy('period_key'),
            'referral' => $user->referralProfile,
            'referralAccruals' => $user->referralAccruals->sortBy('sort_order')->values(),
            'primaryContract' => $primaryContract,
            'primaryPlan' => $primaryContract?->plan,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function calculatorTiers(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan) => [
                'max' => $plan->max_tflops ?? PHP_INT_MAX,
                'name' => $plan->name,
                'infra' => $plan->infra,
                'price' => $plan->price_label,
                'mult' => (float) $plan->reward_multiplier,
            ]);
    }

    public function sectionMeta(): array
    {
        return [
            ['Overview', 'Account overview · epoch 20 914'],
            ['Plans', 'AI compute plans and reward calculator'],
            ['Contracts', 'Active and completed contracts'],
            ['Statistics', 'Rewards, distribution, and performance'],
            ['Wallet', 'Balance, deposits, and withdrawals'],
            ['Referrals', 'Your referral network and commission share'],
            ['Settings', 'Profile, security, and payout details'],
            ['Support', 'Contact the Coin support team'],
        ];
    }

    public function tierForPower(int $power): array
    {
        return $this->calculatorTiers()->first(
            fn (array $tier) => $power <= $tier['max'],
            $this->calculatorTiers()->last()
        );
    }

    public function rewardRate(): float
    {
        return $this->settings->rewardRate();
    }
}
