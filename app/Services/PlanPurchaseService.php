<?php

namespace App\Services;

use App\Support\LocaleFormat;
use App\Support\PlatformTerms;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlanPurchaseService
{
    public function __construct(
        private WalletService $wallets,
        private ReferralCommissionService $referralCommissions,
    ) {}

    public function purchase(User $user, Plan $plan, ?float $amount = null): Contract
    {
        if (! $plan->is_active) {
            throw new RuntimeException('This plan is not available.');
        }

        if ($plan->isEnterprise() && $plan->min_deposit === null) {
            throw new RuntimeException('Contact sales for Enterprise plans.');
        }

        $amount = $amount ?? (float) ($plan->price_amount ?? $plan->min_deposit ?? 0);
        $minDeposit = (float) ($plan->min_deposit ?? 0);

        if ($amount <= 0) {
            throw new RuntimeException('Invalid investment amount.');
        }

        if ($minDeposit > 0 && $amount < $minDeposit) {
            throw new RuntimeException('Amount is below the minimum investment for this plan.');
        }

        $wallet = $this->wallets->ensureWallet($user);

        if ((float) $wallet->available < $amount) {
            throw new RuntimeException('Insufficient available balance.');
        }

        return DB::transaction(function () use ($user, $plan, $amount, $wallet) {
            $currency = (string) config('coin.wallet.base_currency', 'USDT');
            $apr = $plan->annual_profit_percent;
            $durationDays = $plan->duration_days;
            $startedAt = now();
            $endsAt = $durationDays ? $startedAt->copy()->addDays($durationDays) : null;

            $wallet->decrement('available', $amount);
            $wallet->increment('locked_balance', $amount);

            $contract = Contract::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'code' => $this->generateCode(),
                'status' => Contract::STATUS_ACTIVE,
                'principal_amount' => $amount,
                'currency' => $currency,
                'annual_profit_percent' => $apr,
                'started_at' => $startedAt,
                'ends_at' => $endsAt,
                'tflops' => (int) ($plan->tflops ?? 0),
                'duration_days' => $durationDays ?? 0,
                'days_elapsed' => 0,
                'accrued_amount' => 0,
                'progress_percent' => 0,
                'started_label' => LocaleFormat::date($startedAt),
                'ends_label' => $endsAt ? LocaleFormat::date($endsAt) : __('coin.invest.by_agreement'),
                'location_label' => $plan->infra,
            ]);

            $this->wallets->record(
                $user,
                PlatformTerms::TX_INVESTMENT,
                $plan->displayName(),
                -$amount,
                $currency,
                'neutral',
                'COMPLETED',
                $contract,
            );

            $this->referralCommissions->onContractPurchased($contract);
            $this->refreshUserDailyProfitExpectation($user);

            return $contract->fresh(['plan']);
        });
    }

    private function generateCode(): string
    {
        do {
            $code = 'CTR-'.Str::upper(Str::random(6));
        } while (Contract::query()->where('code', $code)->exists());

        return $code;
    }

    private function refreshUserDailyProfitExpectation(User $user): void
    {
        $daily = $user->contracts()
            ->active()
            ->get()
            ->sum(fn (Contract $contract) => $this->dailyProfitFor($contract));

        $user->update([
            'expected_daily_reward' => round($daily, 2),
        ]);
    }

    public function dailyProfitFor(Contract $contract): float
    {
        $principal = (float) ($contract->principal_amount ?? 0);
        $apr = (float) ($contract->annual_profit_percent ?? $contract->plan?->annual_profit_percent ?? 0);

        if ($principal <= 0 || $apr <= 0) {
            return 0.0;
        }

        return round($principal * ($apr / 100) / 365, 2);
    }

    public function topUpRequired(Contract $contract, Plan $newPlan): float
    {
        $required = $newPlan->requiredDepositAmount();
        $current = (float) ($contract->principal_amount ?? 0);

        return max(0, round($required - $current, 2));
    }

    public function changePlan(User $user, Contract $contract, Plan $newPlan, bool $topUpHeld = false): Contract
    {
        if (! $contract->isActive()) {
            throw new RuntimeException(__('coin.messages.plan_change_inactive'));
        }

        if ((int) $contract->user_id !== (int) $user->id) {
            throw new RuntimeException(__('coin.messages.plan_change_forbidden'));
        }

        if ((int) $contract->plan_id === (int) $newPlan->id) {
            throw new RuntimeException(__('coin.messages.plan_change_same_plan'));
        }

        if (! $newPlan->is_active) {
            throw new RuntimeException(__('coin.messages.plan_change_unavailable'));
        }

        if ($newPlan->isEnterprise() && $newPlan->min_deposit === null) {
            throw new RuntimeException(__('coin.invest.contact_sales'));
        }

        $topUp = $this->topUpRequired($contract, $newPlan);

        if ($topUp > 0.009 && ! $topUpHeld) {
            $wallet = $this->wallets->ensureWallet($user);

            if ((float) $wallet->available < $topUp) {
                throw new RuntimeException(__('coin.messages.plan_change_insufficient_balance', [
                    'amount' => number_format($topUp, 2, '.', ' ').' '.($contract->currency ?? config('coin.wallet.base_currency', 'USDT')),
                ]));
            }
        }

        return DB::transaction(function () use ($user, $contract, $newPlan, $topUp, $topUpHeld) {
            $currency = (string) ($contract->currency ?? config('coin.wallet.base_currency', 'USDT'));
            $currentPrincipal = (float) ($contract->principal_amount ?? 0);
            $newPrincipal = round($currentPrincipal + $topUp, 2);
            $startedAt = $contract->started_at ?? now();
            $durationDays = $newPlan->duration_days;
            $endsAt = $durationDays ? $startedAt->copy()->addDays($durationDays) : null;
            $termDays = (int) ($durationDays ?? 0);
            $activeDays = $contract->activeDays();
            $progressPercent = $termDays > 0
                ? min(100, (int) round($activeDays / $termDays * 100))
                : (int) $contract->progress_percent;

            if ($topUp > 0.009) {
                $wallet = $this->wallets->ensureWallet($user);

                if ($topUpHeld) {
                    if ((float) $wallet->pending < $topUp) {
                        throw new RuntimeException(__('coin.messages.plan_change_insufficient_balance', [
                            'amount' => number_format($topUp, 2, '.', ' ').' '.($contract->currency ?? config('coin.wallet.base_currency', 'USDT')),
                        ]));
                    }

                    $wallet->decrement('pending', $topUp);
                } else {
                    $wallet->decrement('available', $topUp);
                }

                $wallet->increment('locked_balance', $topUp);

                $this->wallets->record(
                    $user,
                    PlatformTerms::TX_PLAN_UPGRADE,
                    $newPlan->displayName(),
                    -$topUp,
                    $currency,
                    'neutral',
                    'COMPLETED',
                    $contract,
                );

                $this->referralCommissions->onContractUpgradeTopUp($contract, $topUp);
            }

            $contract->update([
                'plan_id' => $newPlan->id,
                'principal_amount' => $newPrincipal,
                'annual_profit_percent' => $newPlan->annual_profit_percent,
                'duration_days' => $termDays,
                'ends_at' => $endsAt,
                'ends_label' => $endsAt ? LocaleFormat::date($endsAt) : __('coin.invest.by_agreement'),
                'tflops' => (int) ($newPlan->tflops ?? 0),
                'location_label' => $newPlan->infra,
                'progress_percent' => $progressPercent,
            ]);

            $this->refreshUserDailyProfitExpectation($user);

            return $contract->fresh(['plan']);
        });
    }
}
