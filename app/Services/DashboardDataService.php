<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function __construct(
        private PlatformSettingsService $settings,
        private ProfitAccrualService $profitAccrual,
    ) {}

    public function forUser(User $user): array
    {
        $this->profitAccrual->settleMatureContractsForUser($user);

        $user->load([
            'wallet',
            'contracts.plan',
            'walletTransactions',
            'rewardPeriodTotals',
            'referralProfile',
            'referralAccruals',
            'referralCommissionsEarned' => fn ($query) => $query
                ->whereHas('contract')
                ->with(['referral', 'contract.plan']),
            'supportTickets',
        ]);

        $wallet = $user->wallet;
        $primaryContract = $user->contracts->firstWhere('status', Contract::STATUS_ACTIVE);
        $plans = Plan::query()->where('is_active', true)->orderBy('sort_order')->get();

        return [
            'wallet' => $wallet,
            'plans' => $plans,
            'contracts' => $user->contracts,
            'activeContracts' => $user->contracts->where('status', Contract::STATUS_ACTIVE)->values(),
            'completedContracts' => $user->contracts
                ->where('status', Contract::STATUS_COMPLETED)
                ->sortByDesc(fn (Contract $contract) => $contract->ends_at ?? $contract->updated_at)
                ->values(),
            'transactions' => $user->walletTransactions->sortBy('sort_order')->values(),
            'periodTotals' => $user->rewardPeriodTotals->keyBy('period_key'),
            'referral' => $user->referralProfile,
            'referralAccruals' => $user->referralAccruals->sortBy('sort_order')->values(),
            'referralCommissions' => $user->referralCommissionsEarned->sortByDesc('created_at')->values(),
            'profitTransactions' => $user->walletTransactions
                ->filter(fn ($tx) => in_array($tx->type, WalletTransaction::profitHistoryTypes(), true))
                ->sortByDesc(fn ($tx) => $tx->occurred_at ?? $tx->sort_order)
                ->values(),
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
            [__('coin.sections.overview_title'), __('coin.sections.overview_sub')],
            [__('coin.sections.plans_title'), __('coin.sections.plans_sub')],
            [__('coin.sections.investments_title'), __('coin.sections.investments_sub')],
            [__('coin.sections.stats_title'), __('coin.sections.stats_sub')],
            [__('coin.sections.wallet_title'), __('coin.sections.wallet_sub')],
            [__('coin.sections.referrals_title'), __('coin.sections.referrals_sub')],
            [__('coin.sections.settings_title'), __('coin.sections.settings_sub')],
            [__('coin.sections.support_title'), __('coin.sections.support_sub')],
        ];
    }

    public function tierForPower(int $power): array
    {
        return $this->calculatorTiers()->first(
            fn (array $tier) => $power <= $tier['max'],
            $this->calculatorTiers()->last() ?? [
                'max' => PHP_INT_MAX,
                'name' => '—',
                'infra' => '—',
                'price' => '—',
                'mult' => 1.0,
            ],
        );
    }

    public function planForPower(int $power): ?Plan
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->first(fn (Plan $plan) => $power <= ($plan->max_tflops ?? PHP_INT_MAX));
    }

    public function rewardRate(): float
    {
        return $this->settings->rewardRate();
    }
}
