<?php

namespace App\Support;

use App\Models\Plan;
use Illuminate\Support\Collection;

final class LandingPlans
{
    /**
     * @param  Collection<int, Plan>  $plans
     * @return array{defaultPlanId: int|null, currency: string, plans: list<array<string, mixed>>}
     */
    public static function calculatorPayload(Collection $plans): array
    {
        $currency = (string) config('coin.wallet.base_currency', 'USDT');

        $defaultPlan = $plans->firstWhere('is_featured', true) ?? $plans->first();

        return [
            'defaultPlanId' => $defaultPlan?->id,
            'currency' => $currency,
            'plans' => $plans->map(function (Plan $plan) use ($currency): array {
                return [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->displayName(),
                    'minAmount' => $plan->calculatorMinAmount(),
                    'maxAmount' => $plan->calculatorMaxAmount(),
                    'step' => $plan->calculatorStep(),
                    'apr' => $plan->annual_profit_percent !== null
                        ? (float) $plan->annual_profit_percent
                        : 0,
                ];
            })->values()->all(),
        ];
    }
}
