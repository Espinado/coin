<?php

namespace App\Console\Commands;

use App\Services\ProfitAccrualService;
use Illuminate\Console\Command;

class RunDailyProfitAccrual extends Command
{
    protected $signature = 'coin:accrue-daily-profits';

    protected $description = 'Accrue daily investment profit for all active contracts (scheduled at 09:00 by default)';

    public function handle(ProfitAccrualService $accrual): int
    {
        $this->info(sprintf(
            'Starting daily accrual (schedule: %s %s)...',
            config('coin.profit_accrual.schedule_time'),
            config('coin.profit_accrual.schedule_timezone'),
        ));

        $result = $accrual->accrueDaily();

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
