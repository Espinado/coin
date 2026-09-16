<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'available',
        'pending',
        'locked_balance',
        'usd_estimate_label',
        'payout_address',
        'pending_note',
        'network_label',
        'min_withdrawal_label',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'available' => 'decimal:2',
            'pending' => 'decimal:2',
            'locked_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currencyCode(): string
    {
        return MoneyFormat::currency($this->currency);
    }

    public function formattedBalance(): string
    {
        return MoneyFormat::amount($this->balance, $this->currency);
    }

    public function formattedAvailable(): string
    {
        return MoneyFormat::amount($this->available, $this->currency);
    }

    public function formattedPending(): string
    {
        return MoneyFormat::amount($this->pending, $this->currency);
    }

    public function formattedLocked(): string
    {
        return MoneyFormat::amount($this->locked_balance ?? 0, $this->currency);
    }
}
