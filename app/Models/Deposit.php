<?php

namespace App\Models;

use App\Support\CryptoAmountFormat;
use App\Support\LocaleFormat;
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
        'input_amount',
        'input_currency',
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

    public static function idFromPublicReference(?string $value): ?int
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^top[-_]?(\d+)$/i', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'input_amount' => 'decimal:8',
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
        return CryptoAmountFormat::amountWithSymbol($this->amount, (string) $this->currency);
    }

    public function hasInputConversion(): bool
    {
        if ($this->input_amount === null || $this->input_currency === null) {
            return false;
        }

        return strtoupper((string) $this->input_currency) !== strtoupper((string) $this->currency);
    }

    public function formattedInputAmount(): ?string
    {
        if ($this->input_amount === null || $this->input_currency === null) {
            return null;
        }

        return CryptoAmountFormat::amountWithSymbol($this->input_amount, (string) $this->input_currency);
    }

    public function formattedCreditedAmount(): ?string
    {
        if ($this->credited_amount === null) {
            return null;
        }

        return number_format((float) $this->credited_amount, 2, '.', ',').' '.($this->credited_currency ?? 'USDT');
    }

    public function formattedChainReceivedAmount(): ?string
    {
        if ($this->received_amount === null) {
            return null;
        }

        return CryptoAmountFormat::formatPlain($this->received_amount, (string) $this->currency)
            .' '.strtoupper((string) $this->currency);
    }

    public function userRejectionMessage(): ?string
    {
        if ($this->status !== self::STATUS_REJECTED) {
            return null;
        }

        return PaymentStatusReason::depositShortMessage($this);
    }

    public function adminRejectionMessage(): ?string
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
                    'time' => $this->expires_at
                        ->timezone(LocaleFormat::displayTimezone())
                        ->format('d.m.Y H:i'),
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
