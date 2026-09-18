<?php

namespace App\Console\Commands;

use App\Services\ProfitAccrualService;
use Illuminate\Console\Command;

class ReverseDailyProfitAccrual extends Command
{
    protected $signature = 'coin:reverse-daily-accruals
                            {--date= : Accrual calendar date (Y-m-d) in Europe/Riga, default today}
                            {--user= : Limit reversal to one user id}
                            {--force : Run without confirmation}';

    protected $description = 'Reverse mistaken daily profit accruals for a calendar date';

    public function handle(ProfitAccrualService $accrual): int
    {
        $timezone = config('coin.profit_accrual.schedule_timezone', 'Europe/Riga');
        $date = $this->option('date')
            ?: now($timezone)->toDateString();

        $userId = $this->option('user');
        $userFilter = $userId !== null && $userId !== '' ? (int) $userId : null;

        if (! $this->option('force') && ! $this->confirm("Reverse daily profit accruals for {$date} ({$timezone})?")) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        $result = $accrual->reverseAccrualsForDate($date, $userFilter);

        $this->info(sprintf(
            'Reversed %s USDT across %d transaction(s), %d contract(s) updated.',
            number_format($result['total_reversed'], 2, '.', ''),
            $result['transactions_removed'],
            $result['contracts_updated'],
        ));

        return self::SUCCESS;
    }
}
