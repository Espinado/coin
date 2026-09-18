<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfitAccrualService
{
    /** @var array<int, array<string, mixed>> */
    private array $userAccrualSnapshots = [];

    public function __construct(
        private WalletService $wallets,
        private PlanPurchaseService $purchases,
        private UserNotificationService $notifications,
    ) {}

    /** Settle active contracts whose term has ended — release locked principal to available balance. */
    public function settleMatureContractsForUser(User $user): int
    {
        $matured = DB::transaction(function () use ($user) {
            $contractIds = Contract::query()
                ->where('user_id', $user->id)
                ->active()
                ->orderBy('id')
                ->pluck('id');

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

                $this->accrueContract($contract);

                if ($this->completeIfMature($contract->fresh(['user.wallet', 'plan']))) {
                    $matured++;
                }
            }

            return $matured;
        });

        $this->sendPendingProfitNotifications($user->id);

        return $matured;
    }

    /** @return array{contracts_processed: int, total_profit: float, contracts_matured: int, users_credited: int} */
    public function accrueDaily(): array
    {
        $this->userAccrualSnapshots = [];

        Log::channel('profit_accrual')->info('Daily profit accrual run started', [
            'accrual_date' => now()->toDateString(),
            'schedule_time' => config('coin.profit_accrual.schedule_time'),
            'timezone' => config('coin.profit_accrual.schedule_timezone'),
            'started_at' => now()->toIso8601String(),
        ]);

        $result = DB::transaction(function () {
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
                'users_credited' => count($this->userAccrualSnapshots),
            ];
        });

        $this->flushAccrualLogs($result);

        return $result;
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
        $balanceBefore = (float) $wallet->balance;
        $availableBefore = (float) $wallet->available;
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

        $this->trackUserAccrual($user, $profit, $balanceBefore, $availableBefore, $wallet->fresh(), $contract, $currency);
        $this->refreshUserDailyProfitExpectation($user);

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
                'amount' => \App\Support\MoneyFormat::amount($contract->accrued_amount, $currency),
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

    private function trackUserAccrual(
        User $user,
        float $profit,
        float $balanceBefore,
        float $availableBefore,
        Wallet $walletAfter,
        Contract $contract,
        string $currency,
    ): void {
        $userId = $user->id;

        if (! isset($this->userAccrualSnapshots[$userId])) {
            $this->userAccrualSnapshots[$userId] = [
                'user_id' => $userId,
                'email' => $user->email,
                'account' => $user->account_slug,
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'available_before' => $availableBefore,
                'profit_total' => 0.0,
                'contracts' => [],
            ];
        }

        $this->userAccrualSnapshots[$userId]['profit_total'] += $profit;
        $this->userAccrualSnapshots[$userId]['balance_after'] = (float) $walletAfter->balance;
        $this->userAccrualSnapshots[$userId]['available_after'] = (float) $walletAfter->available;
        $this->userAccrualSnapshots[$userId]['contracts'][] = [
            'contract_id' => $contract->id,
            'code' => $contract->code,
            'plan' => $contract->plan?->displayName() ?? $contract->plan?->name,
            'profit' => round($profit, 2),
        ];
    }

    /** @param  array{contracts_processed: int, total_profit: float, contracts_matured: int, users_credited: int}  $runSummary */
    private function flushAccrualLogs(array $runSummary): void
    {
        $logger = Log::channel('profit_accrual');

        foreach ($this->userAccrualSnapshots as $snapshot) {
            $balanceAfter = (float) $snapshot['balance_after'];
            $balanceBefore = (float) $snapshot['balance_before'];
            $availableAfter = (float) $snapshot['available_after'];
            $availableBefore = (float) $snapshot['available_before'];

            $logger->info('User profit accrued', [
                'user_id' => $snapshot['user_id'],
                'email' => $snapshot['email'],
                'account' => $snapshot['account'],
                'profit_accrued' => round((float) $snapshot['profit_total'], 2),
                'balance_before' => round($balanceBefore, 2),
                'balance_after' => round($balanceAfter, 2),
                'balance_delta' => round($balanceAfter - $balanceBefore, 2),
                'available_before' => round($availableBefore, 2),
                'available_after' => round($availableAfter, 2),
                'available_delta' => round($availableAfter - $availableBefore, 2),
                'contracts_count' => count($snapshot['contracts']),
                'contracts' => $snapshot['contracts'],
            ]);
        }

        $this->sendPendingProfitNotifications();

        $logger->info('Daily profit accrual run completed', [
            'accrual_date' => now()->toDateString(),
            'schedule_time' => config('coin.profit_accrual.schedule_time'),
            'timezone' => config('coin.profit_accrual.schedule_timezone'),
            'finished_at' => now()->toIso8601String(),
            ...$runSummary,
        ]);

        $this->userAccrualSnapshots = [];
    }

    private function sendPendingProfitNotifications(?int $onlyUserId = null): void
    {
        foreach ($this->userAccrualSnapshots as $userId => $snapshot) {
            if ($onlyUserId !== null && $userId !== $onlyUserId) {
                continue;
            }

            $profitTotal = (float) ($snapshot['profit_total'] ?? 0);

            if ($profitTotal <= 0) {
                continue;
            }

            $user = User::query()->find($userId);

            if (! $user instanceof User) {
                continue;
            }

            $this->notifications->notifyDailyProfitBatch(
                $user,
                $snapshot['contracts'],
                $profitTotal,
                (string) ($snapshot['currency'] ?? 'USDT'),
            );
        }
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
