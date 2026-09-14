<?php

namespace App\Models;

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
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PAID => 'Paid',
            self::STATUS_REJECTED => 'Rejected',
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
        return number_format((float) $this->amount, 2, '.', ',');
    }
}
