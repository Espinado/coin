<?php

namespace App\Console\Commands;

use App\Models\PaymentStatusLog;
use App\Services\DepositService;
use Illuminate\Console\Command;

class ExpirePendingDepositsCommand extends Command
{
    protected $signature = 'coin:expire-pending-deposits';

    protected $description = 'Reject CCAPI deposits whose payment window has expired';

    public function handle(DepositService $deposits): int
    {
        $expired = $deposits->expireAllDuePending(PaymentStatusLog::SOURCE_POLL);

        if ($expired > 0) {
            $this->components->info(sprintf('Rejected %d expired pending deposit(s).', $expired));
        }

        return self::SUCCESS;
    }
}
