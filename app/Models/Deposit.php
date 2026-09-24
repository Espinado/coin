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

    public function publicReference(): string
    {
        return 'TOP-'.$this->id;
    }

    public function userStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('coin.wallet.deposit_status_pending'),
            self::STATUS_CONFIRMED => __('coin.wallet.deposit_status_confirmed'),
            self::STATUS_REJECTED => __('coin.wallet.deposit_status_rejected'),
            default => ucfirst((string) $this->status),
        };
    }

    public function userStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'oklch(0.88 0.15 90)',
            self::STATUS_CONFIRMED => 'oklch(0.88 0.14 160)',
            self::STATUS_REJECTED => '#ff8f8f',
            default => 'rgba(214,238,248,0.78)',
        };
    }

    public function userStatusDetail(): ?string
    {
        if ($this->status === self::STATUS_REJECTED) {
            return $this->userRejectionMessage();
        }

        if ($this->status === self::STATUS_PENDING) {
            if ($this->expires_at !== null && $this->expires_at->isFuture()) {
                return __('coin.wallet.deposit_expires_at', [
                    'time' => $this->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                ]);
            }

            return __('coin.messages.top_up_pending');
        }

        if ($this->status === self::STATUS_CONFIRMED && $this->formattedCreditedAmount() !== null) {
            return __('coin.wallet.deposit_credited_detail', [
                'amount' => $this->formattedCreditedAmount(),
            ]);
        }

        return null;
    }

    public function canReopenPaymentDetails(): bool
    {
        return $this->status === self::STATUS_PENDING
            && filled($this->payment_address);
    }
}
