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
        $activeContracts = Contract::query()->active();
        $activeContractCount = (clone $activeContracts)->count();
        $totalLocked = (float) (clone $activeContracts)->sum('principal_amount');
        $totalPower = (int) (clone $activeContracts)->sum('tflops');

        $activeUsers = User::query()
            ->whereHas('contracts', fn ($query) => $query->active())
            ->count();

        $totalRewardsPaid = (float) WalletTransaction::query()
            ->whereIn('type', ['Daily profit', 'Referral credit'])
            ->sum('amount');

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
            'total_locked_compact' => $this->compactAmount($totalLocked),
            'total_power' => $totalPower,
            'total_power_compact' => $this->compactUnits($totalPower),
            'total_rewards_paid' => $totalRewardsPaid,
            'total_rewards_compact' => $this->compactAmount($totalRewardsPaid),
            'today_profit' => $todayProfit,
            'today_profit_label' => $this->signedAmount($todayProfit),
            'referral_invited' => $referralInvited,
            'referral_active_contracts' => $referralActiveContracts,
            'referral_rewards' => $referralRewards,
            'referral_rewards_label' => $this->signedAmount($referralRewards),
            'featured_plan_name' => $featuredPlan?->displayName(),
            'featured_power' => $featuredPower,
            'featured_power_label' => $featuredPower > 0
                ? number_format($featuredPower, 0, '.', ' ').' ед.'
                : '—',
            'featured_daily' => $featuredDaily,
            'featured_daily_label' => $featuredDaily > 0
                ? '+'.number_format($featuredDaily, 2, ',', ' ')
                : '—',
        ];
    }

    private function featuredDailyEstimate(?Plan $plan): float
    {
        if ($plan === null) {
            return 0.0;
        }

        if ($plan->daily_estimate !== null) {
            return (float) $plan->daily_estimate;
        }

        $apr = (float) ($plan->annual_profit_percent ?? 0);

        if ($apr <= 0) {
            return 0.0;
        }

        $amount = $plan->calculatorMinAmount();

        return round($amount * ($apr / 100) / 365, 2);
    }

    private function compactAmount(float $value, int $decimals = 1): string
    {
        if ($value >= 1_000_000) {
            return number_format($value / 1_000_000, $decimals, ',', '').' млн';
        }

        if ($value >= 1_000) {
            return number_format($value / 1_000, $decimals, ',', '').' тыс';
        }

        return number_format($value, 0, '.', ' ');
    }

    private function compactUnits(int $value, int $decimals = 1): string
    {
        if ($value >= 1_000_000) {
            return number_format($value / 1_000_000, $decimals, ',', '').' млн ед.';
        }

        if ($value >= 1_000) {
            return number_format($value, 0, '.', ' ').' ед.';
        }

        return number_format($value, 0, '.', ' ').' ед.';
    }

    private function signedAmount(float $value): string
    {
        $prefix = $value >= 0 ? '+' : '−';

        return $prefix.number_format(abs($value), 2, ',', ' ');
    }
}
