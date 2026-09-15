<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Epoch;
use RuntimeException;

class EpochService
{
    /** @deprecated Replaced by ProfitAccrualService — manual/epoch accrual is disabled. */
    public function runSettlement(?Admin $admin = null): Epoch
    {
        throw new RuntimeException('Epoch settlement is disabled. Daily profit is accrued automatically via coin:accrue-daily-profits.');
    }

    public function currentEpochNumber(): int
    {
        return (int) Epoch::query()->max('number');
    }

    public function nextEpochNumber(): int
    {
        return $this->currentEpochNumber() + 1;
    }
}
