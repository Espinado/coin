<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'available',
        'pending',
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
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedBalance(): string
    {
        return number_format((float) $this->balance, 2, '.', ',');
    }

    public function formattedAvailable(): string
    {
        return number_format((float) $this->available, 2, '.', ',');
    }

    public function formattedPending(): string
    {
        return number_format((float) $this->pending, 2, '.', ',');
    }
}
