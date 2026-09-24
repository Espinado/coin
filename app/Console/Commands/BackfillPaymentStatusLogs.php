<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\Payment\PaymentStatusLogService;
use Illuminate\Console\Command;

class BackfillPaymentStatusLogs extends Command
{
    protected $signature = 'payment-logs:backfill {--with-entities : Also create missing created events for deposits and withdrawals}';

    protected $description = 'Import historical payment webhook logs into payment_status_logs';

    public function handle(PaymentStatusLogService $logs): int
    {
        $imported = 0;
        $skipped = 0;

        PaymentWebhookLog::query()
            ->whereNotNull('processing_result')
            ->orderBy('id')
            ->each(function (PaymentWebhookLog $log) use ($logs, &$imported, &$skipped): void {
                $record = $logs->recordFromWebhookLog($log);

                if ($record === null) {
                    $skipped++;

                    return;
                }

                $imported++;
            });

        $entities = 0;

        if ($this->option('with-entities')) {
            Deposit::query()->orderBy('id')->each(function (Deposit $deposit) use ($logs, &$entities): void {
                $hasCreated = PaymentStatusLog::query()
                    ->where('deposit_id', $deposit->id)
                    ->where('event_type', 'created')
                    ->exists();

                if ($hasCreated) {
                    return;
                }

                $record = $logs->depositCreated($deposit);
                $record->forceFill(['created_at' => $deposit->created_at])->save();
                $entities++;
            });

            Withdrawal::query()->orderBy('id')->each(function (Withdrawal $withdrawal) use ($logs, &$entities): void {
                $hasCreated = PaymentStatusLog::query()
                    ->where('withdrawal_id', $withdrawal->id)
                    ->where('event_type', 'created')
                    ->exists();

                if ($hasCreated) {
                    return;
                }

                $record = $logs->withdrawalCreated($withdrawal);
                $record->forceFill(['created_at' => $withdrawal->created_at])->save();
                $entities++;
            });
        }

        $this->info("Imported {$imported} webhook log entries, skipped {$skipped} duplicates.");

        if ($this->option('with-entities')) {
            $this->info("Added {$entities} entity created events.");
        }

        return self::SUCCESS;
    }
}
