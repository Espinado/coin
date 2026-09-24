<?php

namespace App\Jobs;

use App\Models\Deposit;
use App\Models\PaymentStatusLog;
use App\Services\DepositService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpirePendingDepositJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $depositId,
    ) {}

    public function uniqueId(): string
    {
        return 'expire-deposit:'.$this->depositId;
    }

    public function handle(DepositService $deposits): void
    {
        $deposit = Deposit::query()->find($this->depositId);

        if ($deposit === null) {
            return;
        }

        if ($deposit->expires_at !== null && $deposit->expires_at->isFuture()) {
            $this->release($deposit->expires_at->diffInSeconds(now()));

            return;
        }

        $deposits->expireIfDue($deposit, PaymentStatusLog::SOURCE_POLL);
    }
}
