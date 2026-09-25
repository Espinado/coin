<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Services\DepositService;
use Illuminate\Console\Command;

class DiscardOrphanDepositCommand extends Command
{
    protected $signature = 'coin:discard-orphan-deposit
                            {deposit : Deposit ID}
                            {--delete : Permanently delete instead of rejecting}';

    protected $description = 'Reject or delete a pending deposit without payment address (failed gateway setup)';

    public function handle(DepositService $deposits): int
    {
        $deposit = Deposit::query()->find($this->argument('deposit'));

        if ($deposit === null) {
            $this->components->error('Deposit not found.');

            return self::FAILURE;
        }

        try {
            if ($this->option('delete')) {
                $deposits->deleteOrphanPending($deposit);
                $this->components->info(sprintf('Deleted orphan deposit #%d.', $deposit->id));
            } else {
                $deposits->discardOrphanPending($deposit);
                $this->components->info(sprintf('Rejected orphan deposit #%d.', $deposit->id));
            }
        } catch (\RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
