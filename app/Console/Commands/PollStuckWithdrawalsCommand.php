<?php

namespace App\Console\Commands;

use App\Services\Payment\WithdrawalPollService;
use Illuminate\Console\Command;

class PollStuckWithdrawalsCommand extends Command
{
    protected $signature = 'coin:poll-stuck-withdrawals';

    protected $description = 'Poll CCAPI payout status for withdrawals stuck in processing without IPN';

    public function handle(WithdrawalPollService $pollService): int
    {
        $stats = $pollService->pollStuckWithdrawals();

        if ($stats['polled'] === 0) {
            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Polled %d withdrawal(s): %d completed, %d pending, %d gateway failed, %d errors, %d stale alert(s).',
            $stats['polled'],
            $stats['completed'],
            $stats['pending'],
            $stats['failed'],
            $stats['errors'],
            $stats['stale'],
        ));

        return self::SUCCESS;
    }
}
