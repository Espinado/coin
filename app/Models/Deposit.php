<?php

namespace App\Models;

use App\Support\PaymentStatusReason;
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
        'status_reason',
        'method',
        'external_reference',
        'payment_address',
        'gateway_uniq_id',
        'gateway_network',
        'txid',
        'received_amount',
        'expires_at',
        'confirmed_by',
        'confirmed_at',
    ];

    public static function gatewayUniqId(int $id): string
    {
        return 'deposit:'.$id;
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'credited_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'received_amount' => 'decimal:8',
            'expires_at' => 'datetime',
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

    public function userRejectionMessage(): ?string
    {
        if ($this->status !== self::STATUS_REJECTED) {
            return null;
        }

        return PaymentStatusReason::depositMessage($this);
    }
}
