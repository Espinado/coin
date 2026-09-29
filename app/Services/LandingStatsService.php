<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;

class LandingStatsService
{
    /** @param Collection<int, Plan> $landingPlans
     * @return array<string, mixed>
     */
    public function forLanding(Collection $landingPlans, int $activePlanCount): array
    {
        // Marketing display figures for landing metric cards (not live DB aggregates).
        $activeContractCount = 9751;
        $activeUsers = 6210;
        $totalLocked = 10_457_300.0;
        $totalRewardsPaid = 4_182_300.0;

        $activeContracts = Contract::query()->active();
        $totalPower = (int) (clone $activeContracts)->sum('tflops');

        $todayProfit = (float) WalletTransaction::query()
            ->where('type', 'Daily profit')
            ->whereDate('occurred_at', today())
            ->sum('amount');

        $referralInvited = User::query()
            ->whereNotNull('referred_by_user_id')
            ->count();

        $referralActiveContracts = Contract::query()
            ->active()
            ->whereHas('user', fn ($query) => $query->whereNotNull('referred_by_user_id'))
            ->count();

        $referralRewards = (float) WalletTransaction::query()
            ->where('type', 'Referral credit')
            ->sum('amount');

        $featuredPlan = $landingPlans->firstWhere('is_featured', true) ?? $landingPlans->first();
        $featuredPower = $featuredPlan ? (int) ($featuredPlan->tflops ?? 0) : 0;
        $featuredDaily = $this->featuredDailyEstimate($featuredPlan);

        return [
            'active_plan_count' => $activePlanCount,
            'active_contracts' => $activeContractCount,
            'active_users' => $activeUsers,
            'total_locked' => $totalLocked,
            'total_locked_label' => $this->fullIntegerAmount($totalLocked),
            'total_power' => $totalPower,
            'total_power_label' => $this->fullInteger($totalPower),
            'total_rewards_paid' => $totalRewardsPaid,
            'total_rewards_label' => $this->fullIntegerAmount($totalRewardsPaid),
            'today_profit' => $todayProfit,
            'today_profit_label' => $this->signedAmount($todayProfit),
            'referral_invited' => $referralInvited,
            'referral_active_contracts' => $referralActiveContracts,
            'referral_rewards' => $referralRewards,
            'referral_rewards_label' => $this->signedAmount($referralRewards),
            'featured_plan_name' => $featuredPlan?->displayName(),
            'featured_power' => $featuredPower,
            'featured_power_label' => $featuredPower > 0
                ? $this->fullInteger($featuredPower)
                : '—',
            'featured_daily' => $featuredDaily,
            'featured_daily_label' => $featuredDaily > 0
                ? '+'.$this->fullAmount($featuredDaily)
                : '—',
        ];
    }

    private function featuredDailyEstimate(?Plan $plan): float
    {
        if ($plan === null) {
            return 0.0;
        }

        return (float) ($plan->estimatedDailyProfit() ?? 0.0);
    }

    private function fullAmount(float $value): string
    {
        return number_format($value, 2, ',', ' ').' USDT';
    }

    private function fullIntegerAmount(float $value): string
    {
        return number_format($value, 0, ',', ' ').' USDT';
    }

    private function fullInteger(int $value): string
    {
        return number_format($value, 0, '.', ' ');
    }

    private function signedAmount(float $value): string
    {
        $prefix = $value >= 0 ? '+' : '−';

        return $prefix.$this->fullAmount(abs($value));
    }
}
