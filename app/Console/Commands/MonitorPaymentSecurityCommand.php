<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitorPaymentSecurityCommand extends Command
{
    protected $signature = 'coin:monitor-payment-security';

    protected $description = 'Log payment security signals: invalid webhook signatures and stale CCAPI requests';

    public function handle(): int
    {
        $this->checkInvalidWebhookSignatures();
        $this->checkStaleProcessingWithdrawals();
        $this->checkStalePendingDeposits();

        return self::SUCCESS;
    }

    private function checkInvalidWebhookSignatures(): void
    {
        $lookbackMinutes = max(1, (int) config('coin.payments.ccapi.security_monitor.lookback_minutes', 60));
        $threshold = max(1, (int) config('coin.payments.ccapi.security_monitor.invalid_signature_threshold', 5));

        $count = PaymentWebhookLog::query()
            ->where('signature_valid', false)
            ->where('created_at', '>=', now()->subMinutes($lookbackMinutes))
            ->count();

        if ($count < $threshold) {
            return;
        }

        Log::warning('payment.security.invalid_webhook_signatures', [
            'count' => $count,
            'lookback_minutes' => $lookbackMinutes,
            'threshold' => $threshold,
        ]);

        $this->components->warn(sprintf(
            'Detected %d invalid CCAPI webhook signature(s) in the last %d minute(s).',
            $count,
            $lookbackMinutes,
        ));
    }

    private function checkStaleProcessingWithdrawals(): void
    {
        $count = Withdrawal::staleProcessingCount();

        if ($count === 0) {
            return;
        }

        $cacheKey = 'payment.security.stale_processing_withdrawals';

        if (! Cache::add($cacheKey, $count, now()->addHour())) {
            return;
        }

        Log::warning('payment.security.stale_processing_withdrawals', [
            'count' => $count,
            'stale_hours' => (int) config('coin.payments.ccapi.withdrawal_poll_stale_hours', 24),
        ]);

        $this->components->warn(sprintf(
            '%d withdrawal(s) remain in processing beyond the stale threshold.',
            $count,
        ));
    }

    private function checkStalePendingDeposits(): void
    {
        $staleMinutes = max(1, (int) config('coin.payments.ccapi.deposit_stale_alert_minutes', 30));
        $threshold = now()->subMinutes($staleMinutes);

        $count = Deposit::query()
            ->where('status', Deposit::STATUS_PENDING)
            ->where('method', 'ccapi')
            ->where(function ($query) use ($threshold) {
                $query->where('updated_at', '<=', $threshold)
                    ->orWhere(function ($inner) use ($threshold) {
                        $inner->whereNull('updated_at')
                            ->where('created_at', '<=', $threshold);
                    });
            })
            ->count();

        if ($count === 0) {
            return;
        }

        $cacheKey = 'payment.security.stale_pending_deposits';

        if (! Cache::add($cacheKey, $count, now()->addHour())) {
            return;
        }

        Log::warning('payment.security.stale_pending_deposits', [
            'count' => $count,
            'stale_minutes' => $staleMinutes,
        ]);

        $this->components->warn(sprintf(
            '%d pending CCAPI deposit(s) await IPN longer than %d minute(s).',
            $count,
            $staleMinutes,
        ));
    }
}
