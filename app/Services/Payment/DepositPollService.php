<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Services\DepositService;
use App\Services\PlatformSettingsService;
use App\Support\PaymentStatusReason;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DepositPollService
{
    public function __construct(
        private readonly PlatformSettingsService $settings,
        private readonly DepositService $deposits,
    ) {}

    /** @return array{checked: int, expired: int, stale: int, errors: int} */
    public function pollStuckDeposits(): array
    {
        $stats = [
            'checked' => 0,
            'expired' => 0,
            'stale' => 0,
            'errors' => 0,
        ];

        if (! $this->shouldPoll()) {
            return $stats;
        }

        Deposit::query()
            ->where('status', Deposit::STATUS_PENDING)
            ->where('method', 'ccapi')
            ->whereNotNull('payment_address')
            ->orderBy('id')
            ->each(function (Deposit $deposit) use (&$stats): void {
                $stats['checked']++;
                $this->processDeposit($deposit, $stats);
            });

        if ($stats['checked'] > 0) {
            Log::info('deposit.poll.batch', $stats);
        }

        return $stats;
    }

    /** @param array{checked: int, expired: int, stale: int, errors: int} $stats */
    private function processDeposit(Deposit $deposit, array &$stats): void
    {
        if ($deposit->expires_at !== null && $deposit->expires_at->isPast()) {
            try {
                $this->deposits->reject($deposit, null, PaymentStatusReason::DEPOSIT_EXPIRED);
                $stats['expired']++;
                Log::info('deposit.poll.expired', [
                    'deposit_id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                    'amount' => $deposit->amount,
                    'gateway_uniq_id' => $deposit->gateway_uniq_id,
                ]);
            } catch (\Throwable $exception) {
                $stats['errors']++;
                Log::warning('deposit.poll.expire_error', [
                    'deposit_id' => $deposit->id,
                    'message' => $exception->getMessage(),
                ]);
            }

            return;
        }

        $staleMinutes = max(1, (int) config('coin.payments.ccapi.deposit_stale_alert_minutes', 30));
        $referenceTime = $deposit->updated_at ?? $deposit->created_at;

        if ($referenceTime === null || $referenceTime->gte(now()->subMinutes($staleMinutes))) {
            return;
        }

        $cacheKey = 'deposit.poll.stale:'.$deposit->id;

        if (! Cache::add($cacheKey, true, now()->addHour())) {
            return;
        }

        $stats['stale']++;
        Log::warning('deposit.poll.stale_pending', [
            'deposit_id' => $deposit->id,
            'user_id' => $deposit->user_id,
            'amount' => $deposit->amount,
            'currency' => $deposit->currency,
            'gateway_uniq_id' => $deposit->gateway_uniq_id,
            'expires_at' => $deposit->expires_at?->toIso8601String(),
            'pending_since' => $referenceTime->toIso8601String(),
        ]);
    }

    private function shouldPoll(): bool
    {
        if (! config('coin.payments.ccapi.poll_stuck_deposits', true)) {
            return false;
        }

        return $this->settings->usesLivePaymentGateway()
            && (string) config('coin.payments.driver', 'mock') === 'ccapi';
    }
}
