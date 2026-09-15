<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfitAccrualService
{
    public function __construct(
        private WalletService $wallets,
        private PlanPurchaseService $purchases,
        private UserNotificationService $notifications,
    ) {}

    /** @return array{contracts_processed: int, total_profit: float, contracts_matured: int} */
    public function accrueDaily(): array
    {
        return DB::transaction(function () {
            $contractIds = Contract::query()
                ->active()
                ->orderBy('id')
                ->pluck('id');

            $totalProfit = 0.0;
            $processed = 0;
            $matured = 0;

            foreach ($contractIds as $contractId) {
                $contract = Contract::query()
                    ->with(['user.wallet', 'plan'])
                    ->whereKey($contractId)
                    ->lockForUpdate()
                    ->first();

                if (! $contract) {
                    continue;
                }

                $this->notifications->maybeSendContractExpiryReminder($contract);

                $profit = $this->accrueContract($contract);

                if ($profit > 0) {
                    $totalProfit += $profit;
                    $processed++;
                }

                if ($this->completeIfMature($contract)) {
                    $matured++;
                }
            }

            return [
                'contracts_processed' => $processed,
                'total_profit' => round($totalProfit, 2),
                'contracts_matured' => $matured,
            ];
        });
    }

    public function accrueContract(Contract $contract): float
    {
        if (! $contract->isActive()) {
            return 0.0;
        }

        $today = now()->toDateString();

        if ($contract->last_accrued_on?->toDateString() === $today) {
            return 0.0;
        }

        $profit = $this->purchases->dailyProfitFor($contract);

        if ($profit <= 0) {
            return 0.0;
        }

        $user = $contract->user;
        $wallet = $this->wallets->ensureWallet($user);
        $currency = $contract->currency ?: $this->wallets->currencyFor($wallet);

        $wallet->increment('available', $profit);
        $wallet->increment('balance', $profit);

        $contract->increment('accrued_amount', $profit);

        $contract->refresh();

        if ($contract->termDays() > 0) {
            $contract->increment('days_elapsed');
            $contract->refresh();
            $contract->update([
                'progress_percent' => $contract->computedProgressPercent(),
                'last_accrued_on' => $today,
            ]);
        } else {
            $contract->update(['last_accrued_on' => $today]);
        }

        $this->wallets->record(
            $user,
            'Daily profit',
            $contract->plan?->name ?? $contract->code,
            $profit,
            $currency,
            'positive',
            'COMPLETED',
            $contract,
        );

        $this->refreshUserDailyProfitExpectation($user);
        $this->notifications->notifyDailyProfit($user, $contract->fresh(['plan']), $profit, $currency);

        return $profit;
    }

    public function completeIfMature(Contract $contract): bool
    {
        if (! $contract->isActive()) {
            return false;
        }

        $mature = false;

        if ($contract->ends_at && now()->greaterThanOrEqualTo($contract->ends_at)) {
            $mature = true;
        } elseif ($contract->duration_days > 0 && $contract->days_elapsed >= $contract->duration_days) {
            $mature = true;
        }

        if (! $mature) {
            return false;
        }

        $principal = (float) ($contract->principal_amount ?? 0);
        $currency = $contract->currency ?: $this->wallets->currencyFor($this->wallets->ensureWallet($contract->user));

        $this->releasePrincipal($contract);

        $contract->update([
            'status' => Contract::STATUS_COMPLETED,
            'ends_at' => $contract->ends_at ?? now(),
            'progress_percent' => 100,
            'days_elapsed' => max((int) $contract->days_elapsed, $contract->termDays()),
            'completed_summary' => __('coin.invest.matured_summary', [
                'amount' => number_format((float) $contract->accrued_amount, 2, '.', ','),
            ]),
        ]);

        $this->refreshUserDailyProfitExpectation($contract->user);
        $this->notifications->notifyContractMatured($contract->user, $contract->fresh(['plan']), $principal, $currency);

        return true;
    }

    private function releasePrincipal(Contract $contract): void
    {
        $principal = (float) ($contract->principal_amount ?? 0);

        if ($principal <= 0) {
            return;
        }

        $user = $contract->user;
        $wallet = $this->wallets->ensureWallet($user);
        $currency = $contract->currency ?: $this->wallets->currencyFor($wallet);

        $wallet->decrement('locked_balance', min($principal, (float) $wallet->locked_balance));
        $wallet->increment('available', $principal);

        $this->wallets->record(
            $user,
            'Principal release',
            $contract->code,
            $principal,
            $currency,
            'positive',
            'COMPLETED',
            $contract,
        );
    }

    private function refreshUserDailyProfitExpectation(User $user): void
    {
        $daily = $user->contracts()
            ->active()
            ->get()
            ->sum(fn (Contract $contract) => $this->purchases->dailyProfitFor($contract));

        $user->update([
            'expected_daily_reward' => round($daily, 2),
        ]);
    }
}
