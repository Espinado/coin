<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use App\Services\PlatformSettingsService;
use App\Services\ProfitAccrualService;
use App\Services\SystemLogService;
use Illuminate\Console\Command;
use Throwable;

class RunDailyProfitAccrual extends Command
{
    protected $signature = 'coin:accrue-daily-profits';

    protected $description = 'Accrue daily investment profit for all active contracts (scheduled at 09:00 by default)';

    public function handle(
        ProfitAccrualService $accrual,
        PlatformSettingsService $settings,
        SystemLogService $systemLogs,
    ): int {
        $this->info(sprintf(
            'Starting daily accrual (schedule: %s %s)...',
            $settings->profitAccrualTime(),
            $settings->profitAccrualTimezone(),
        ));

        try {
            $result = $accrual->accrueDaily();
        } catch (Throwable $exception) {
            $systemLogs->critical(
                SystemLog::SOURCE_CRON,
                'Daily profit accrual failed: '.$exception->getMessage(),
                [
                    'schedule_time' => $settings->profitAccrualTime(),
                    'timezone' => $settings->profitAccrualTimezone(),
                ],
                'coin:accrue-daily-profits',
                $exception,
            );

            \Illuminate\Support\Facades\Log::channel('profit_accrual')->error('Daily profit accrual failed', [
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            $this->error('Daily accrual failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Daily accrual completed: %d contracts, %d users, %s total profit, %d matured. Log: storage/logs/profit-accrual.log',
            $result['contracts_processed'],
            $result['users_credited'],
            number_format($result['total_profit'], 2, '.', ''),
            $result['contracts_matured'],
        ));

        return self::SUCCESS;
    }
}
