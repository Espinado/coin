<?php

namespace App\Models;

use App\Support\MoneyFormat;
use App\Support\PaymentStatusReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    public const STATUS_PENDING = 'pending';

    /** @deprecated Legacy intermediate status — displayed as pending. */
    public const STATUS_APPROVED = 'approved';

    /** Internal gateway-in-flight status — displayed as pending. */
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'reference',
        'amount',
        'base_amount',
        'currency',
        'exchange_rate',
        'usdt_per_btc',
        'withdrawal_type',
        'payout_address',
        'network_label',
        'gateway_request_id',
        'txid',
        'gateway_state',
        'sent_at',
        'gateway_poll_checked_at',
        'gateway_poll_summary',
        'status',
        'status_reason',
        'processed_by',
        'admin_note',
        'processed_at',
    ];

    public static function gatewayUniqId(string $reference): string
    {
        return 'withdrawal:'.$reference;
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'base_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'usdt_per_btc' => 'decimal:8',
            'sent_at' => 'datetime',
            'gateway_poll_checked_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function adminStatuses(): array
    {
        return [
            self::STATUS_PENDING => __('coin.withdrawal_status.pending'),
            self::STATUS_PAID => __('coin.withdrawal_status.paid'),
            self::STATUS_REJECTED => __('coin.withdrawal_status.rejected'),
        ];
    }

    /** @return list<string> */
    public static function openStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_PROCESSING,
        ];
    }

    /** @return array<int, string> */
    public static function committedStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_PROCESSING,
            self::STATUS_PAID,
        ];
    }

    /** @return array<string, list<string>> */
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_PENDING => [self::STATUS_REJECTED],
            self::STATUS_APPROVED => [self::STATUS_REJECTED],
            self::STATUS_PROCESSING => [],
            self::STATUS_PAID => [],
            self::STATUS_REJECTED => [],
        ];
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::allowedTransitions()[$from] ?? [], true);
    }

    /** @return list<string> */
    public static function closedStatuses(): array
    {
        return [
            self::STATUS_PAID,
            self::STATUS_REJECTED,
        ];
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::closedStatuses(), true);
    }

    public function adminStatus(): string
    {
        if (in_array($this->status, self::openStatuses(), true)) {
            return self::STATUS_PENDING;
        }

        return $this->status;
    }

    /** @return array<string, string> */
    public function adminSelectableStatuses(): array
    {
        $current = $this->adminStatus();
        $next = self::allowedTransitions()[$this->status] ?? [];
        $keys = array_values(array_unique(array_merge([$current], $next)));

        return array_intersect_key(self::adminStatuses(), array_flip($keys));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function statusLabel(): string
    {
        return self::adminStatuses()[$this->adminStatus()] ?? ucfirst((string) $this->status);
    }

    public function formattedAmount(): string
    {
        return MoneyFormat::amount($this->amount, $this->currency);
    }

    /** USDT amount reserved from the user's balance. */
    public function ledgerAmount(): float
    {
        return (float) ($this->base_amount ?? $this->amount);
    }

    public static function pendingCountForAdmin(): int
    {
        return self::query()->whereIn('status', self::openStatuses())->count();
    }

    public function userRejectionMessage(): ?string
    {
        if ($this->status !== self::STATUS_REJECTED) {
            return null;
        }

        return PaymentStatusReason::withdrawalMessage($this);
    }
}
