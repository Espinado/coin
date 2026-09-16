<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'reference',
        'amount',
        'currency',
        'withdrawal_type',
        'payout_address',
        'network_label',
        'status',
        'processed_by',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => __('coin.withdrawal_status.pending'),
            self::STATUS_APPROVED => __('coin.withdrawal_status.approved'),
            self::STATUS_PROCESSING => __('coin.withdrawal_status.processing'),
            self::STATUS_PAID => __('coin.withdrawal_status.paid'),
            self::STATUS_REJECTED => __('coin.withdrawal_status.rejected'),
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
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function formattedAmount(): string
    {
        return MoneyFormat::amount($this->amount, $this->currency);
    }

    public static function pendingCountForAdmin(): int
    {
        return self::query()->where('status', self::STATUS_PENDING)->count();
    }
}
