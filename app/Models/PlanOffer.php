<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanOffer extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'plan_id',
        'user_id',
        'created_by',
        'amount',
        'duration_days',
        'annual_profit_percent',
        'currency',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'annual_profit_percent' => 'decimal:2',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpiredByTime(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function formattedAmount(): string
    {
        return MoneyFormat::amount($this->amount, $this->currency);
    }

    public function formattedApr(): string
    {
        return number_format((float) $this->annual_profit_percent, 1, '.', '').'%';
    }

    public function statusLabel(): string
    {
        $key = 'coin.plan_offer_status.'.$this->status;

        return __($key) !== $key ? __($key) : ucfirst((string) $this->status);
    }
}
