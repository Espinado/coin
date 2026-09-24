<?php

namespace App\Console\Commands;

use App\Services\Payment\DepositPollService;
use Illuminate\Console\Command;

class PollStuckDepositsCommand extends Command
{
    protected $signature = 'coin:poll-stuck-deposits';

    protected $description = 'Reject expired pending CCAPI deposits and log stale ones awaiting IPN';

    public function handle(DepositPollService $pollService): int
    {
        $stats = $pollService->pollStuckDeposits();

        if ($stats['checked'] === 0) {
            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Checked %d pending deposit(s): %d expired, %d stale alert(s), %d errors.',
            $stats['checked'],
            $stats['expired'],
            $stats['stale'],
            $stats['errors'],
        ));

        return self::SUCCESS;
    }
}
