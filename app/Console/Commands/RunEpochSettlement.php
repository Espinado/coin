<?php

namespace App\Console\Commands;

use App\Services\EpochService;
use Illuminate\Console\Command;

class RunEpochSettlement extends Command
{
    protected $signature = 'coin:run-epoch';

    protected $description = 'Run Coin epoch reward settlement for all active contracts';

    public function handle(EpochService $epochs): int
    {
        $epoch = $epochs->runSettlement();

        $this->info(sprintf(
            'Epoch %d completed: %d contracts, %s total rewards.',
            $epoch->number,
            $epoch->contracts_settled,
            $epoch->formattedTotalRewards(),
        ));

        return self::SUCCESS;
    }
}
