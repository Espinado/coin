<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfitAccrualService
{
    public function __construct(
        private WalletService $wallets,
        private PlanPurchaseService $purchases,
    ) {}

    /** @return array{contracts_processed: int, total_profit: float, contracts_matured: int} */
    public function accrueDaily(?Admin $admin = null): array
    {
        return DB::transaction(function () {
            $contracts = Contract::query()
                ->with(['user.wallet', 'plan'])
                ->where('status', 'active')
                ->get();

            $totalProfit = 0.0;
            $processed = 0;
            $matured = 0;

            foreach ($contracts as $contract) {
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
        if ($contract->status !== 'active') {
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

        if ($contract->duration_days > 0) {
            $contract->increment('days_elapsed');
            $contract->update([
                'progress_percent' => min(100, (int) round(($contract->days_elapsed / $contract->duration_days) * 100)),
            ]);
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

        return $profit;
    }

    public function completeIfMature(Contract $contract): bool
    {
        if ($contract->status !== 'active') {
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

        $this->releasePrincipal($contract);

        $contract->update([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_summary' => 'Matured · '.number_format((float) $contract->accrued_amount, 2, '.', ',').' profit accrued',
        ]);

        $this->refreshUserDailyProfitExpectation($contract->user);

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
            ->where('status', 'active')
            ->get()
            ->sum(fn (Contract $contract) => $this->purchases->dailyProfitFor($contract));

        $user->update([
            'expected_daily_reward' => round($daily, 2),
        ]);
    }
}
