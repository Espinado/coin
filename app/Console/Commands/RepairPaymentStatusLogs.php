<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Models\Withdrawal;
use App\Services\Payment\PaymentStatusLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairPaymentStatusLogs extends Command
{
    protected $signature = 'payment-logs:repair
                            {--dry-run : Show planned changes without writing}
                            {--skip-delete : Only refresh texts and fields, keep all rows}';

    protected $description = 'Clean and re-decode existing payment journal rows';

    public function handle(PaymentStatusLogService $logs): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $skipDelete = (bool) $this->option('skip-delete');

        $ignoredPolls = PaymentStatusLog::query()
            ->where('event_type', 'payout_poll')
            ->where('result', 'ignored');

        $ignoredPollCount = (clone $ignoredPolls)->count();

        $pollProcessedDupes = PaymentStatusLog::query()
            ->where('source', PaymentStatusLog::SOURCE_POLL)
            ->where('result', 'processed')
            ->whereIn('event_type', ['payout_poll', 'status_change'])
            ->whereNotNull('withdrawal_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_status_logs as app_logs')
                    ->whereColumn('app_logs.withdrawal_id', 'payment_status_logs.withdrawal_id')
                    ->where('app_logs.source', PaymentStatusLog::SOURCE_APP)
                    ->where('app_logs.event_type', 'status_change');
            });

        $pollProcessedDupeCount = (clone $pollProcessedDupes)->count();

        $staleFailedPolls = PaymentStatusLog::query()
            ->where('event_type', 'payout_poll')
            ->where('result', 'failed')
            ->where(function ($query) {
                $query->whereNotNull('withdrawal_id')
                    ->whereExists(function ($exists) {
                        $exists->select(DB::raw(1))
                            ->from('withdrawals')
                            ->whereColumn('withdrawals.id', 'payment_status_logs.withdrawal_id')
                            ->where('withdrawals.status', '!=', Withdrawal::STATUS_PROCESSING);
                    })
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('deposit_id')
                            ->whereExists(function ($exists) {
                                $exists->select(DB::raw(1))
                                    ->from('deposits')
                                    ->whereColumn('deposits.id', 'payment_status_logs.deposit_id')
                                    ->whereIn('deposits.status', [
                                        Deposit::STATUS_CONFIRMED,
                                        Deposit::STATUS_REJECTED,
                                    ]);
                            });
                    });
            });

        $staleFailedPollCount = (clone $staleFailedPolls)->count();

        $refreshCount = PaymentStatusLog::query()->count();

        $this->info("Ignored payout polls to remove: {$ignoredPollCount}");
        $this->info("Duplicate poll processed rows to remove: {$pollProcessedDupeCount}");
        $this->info("Stale failed poll rows on closed requests to remove: {$staleFailedPollCount}");
        $this->info("Rows to refresh (decode / fix fields): {$refreshCount}");

        if ($dryRun) {
            $this->warn('Dry run — no changes applied.');

            return self::SUCCESS;
        }

        if (! $skipDelete) {
            DB::transaction(function () use ($ignoredPolls, $pollProcessedDupes, $staleFailedPolls): void {
                $ignoredPolls->delete();
                $pollProcessedDupes->delete();
                $staleFailedPolls->delete();
            });

            $this->info('Removed noisy / duplicate / stale poll rows.');
        }

        $updated = 0;

        PaymentStatusLog::query()
            ->orderBy('id')
            ->each(function (PaymentStatusLog $log) use ($logs, &$updated): void {
                if ($logs->repairExistingRow($log)) {
                    $updated++;
                }
            });

        $this->info("Refreshed {$updated} journal rows.");

        return self::SUCCESS;
    }
}
