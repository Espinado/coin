<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyUnlockRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'contract_id',
        'reference',
        'principal_amount',
        'fee_percent',
        'fee_min',
        'fee_amount',
        'credit_amount',
        'currency',
        'status',
        'processed_by',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'fee_percent' => 'decimal:2',
            'fee_min' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => __('coin.early_unlock_status.pending'),
            self::STATUS_APPROVED => __('coin.early_unlock_status.approved'),
            self::STATUS_REJECTED => __('coin.early_unlock_status.rejected'),
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function processedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function formattedPrincipal(): string
    {
        return MoneyFormat::amount($this->principal_amount, $this->currency);
    }

    public function formattedFee(): string
    {
        return MoneyFormat::amount($this->fee_amount, $this->currency);
    }

    public function formattedCredit(): string
    {
        return MoneyFormat::amount($this->credit_amount, $this->currency);
    }

    public static function pendingCountForAdmin(): int
    {
        return self::query()->where('status', self::STATUS_PENDING)->count();
    }

    public static function pendingForContract(int $contractId): ?self
    {
        return self::query()
            ->where('contract_id', $contractId)
            ->where('status', self::STATUS_PENDING)
            ->first();
    }
}
