<?php

namespace App\Console\Commands;

use App\Services\ProfitAccrualService;
use Illuminate\Console\Command;

class RunDailyProfitAccrual extends Command
{
    protected $signature = 'coin:accrue-daily-profits';

    protected $description = 'Accrue daily investment profit for all active deposits';

    public function handle(ProfitAccrualService $accrual): int
    {
        $result = $accrual->accrueDaily();

        $this->info(sprintf(
            'Daily accrual completed: %d contracts, %s total profit, %d matured.',
            $result['contracts_processed'],
            number_format($result['total_profit'], 2, '.', ''),
            $result['contracts_matured'],
        ));

        return self::SUCCESS;
    }
}
