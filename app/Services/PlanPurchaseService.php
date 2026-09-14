<?php

namespace App\Services;

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
            $currency = $plan->currency ?: $this->wallets->currencyFor($wallet);
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
                'status' => 'active',
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
                'started_label' => $startedAt->format('M j, Y'),
                'ends_label' => $endsAt?->format('M j, Y') ?? 'By agreement',
                'location_label' => $plan->infra,
            ]);

            $this->wallets->record(
                $user,
                PlatformTerms::TX_INVESTMENT,
                $plan->name,
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
            ->where('status', 'active')
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
}
