<?php

namespace App\Models;

use App\Support\MoneyFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChangeRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'contract_id',
        'from_plan_id',
        'to_plan_id',
        'reference',
        'top_up_amount',
        'principal_after',
        'status',
        'processed_by',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'top_up_amount' => 'decimal:2',
            'principal_after' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => __('coin.plan_change_status.pending'),
            self::STATUS_APPROVED => __('coin.plan_change_status.approved'),
            self::STATUS_REJECTED => __('coin.plan_change_status.rejected'),
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

    public function fromPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'from_plan_id');
    }

    public function toPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'to_plan_id');
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
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function formattedTopUp(): string
    {
        $currency = $this->contract?->currency ?? config('coin.wallet.base_currency', 'USDT');

        return MoneyFormat::amount($this->top_up_amount, $currency);
    }

    public function formattedPrincipalAfter(): string
    {
        $currency = $this->contract?->currency ?? config('coin.wallet.base_currency', 'USDT');

        return MoneyFormat::amount($this->principal_after, $currency);
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
