<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'credited_amount',
        'credited_currency',
        'exchange_rate',
        'status',
        'method',
        'external_reference',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'credited_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }

    public function formattedAmount(): string
    {
        return number_format((float) $this->amount, 2, '.', ',').' '.$this->currency;
    }

    public function formattedCreditedAmount(): ?string
    {
        if ($this->credited_amount === null) {
            return null;
        }

        return number_format((float) $this->credited_amount, 2, '.', ',').' '.($this->credited_currency ?? 'USDT');
    }
}
