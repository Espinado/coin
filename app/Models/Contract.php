<?php

namespace App\Models;

use App\Support\LocaleFormat;
use App\Support\PlanLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /** Demo/seed purge only — production contracts are archived, not deleted. */
    public static bool $allowDeletion = false;

    protected $fillable = [
        'user_id',
        'plan_id',
        'code',
        'status',
        'principal_amount',
        'currency',
        'annual_profit_percent',
        'started_at',
        'ends_at',
        'tflops',
        'duration_days',
        'days_elapsed',
        'last_accrued_on',
        'accrued_amount',
        'progress_percent',
        'started_label',
        'ends_label',
        'location_label',
        'completed_summary',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'annual_profit_percent' => 'decimal:2',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_accrued_on' => 'date',
            'accrued_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function () {
            if (! static::$allowDeletion) {
                throw new \RuntimeException('Contracts cannot be deleted. Archive them with status "completed" instead.');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeArchived($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function formattedAccrued(): string
    {
        $currency = $this->currency ?? $this->plan?->currency ?? 'USDT';

        return number_format((float) $this->accrued_amount, 2, '.', ',').' '.$currency;
    }

    public function activeDays(): int
    {
        $term = $this->termDays();

        if ($term <= 0) {
            return (int) $this->days_elapsed;
        }

        if ($this->isCompleted()) {
            return $term;
        }

        $accrualDays = (int) $this->days_elapsed;

        if (! $this->started_at) {
            return min($term, $accrualDays);
        }

        $calendarDays = (int) $this->started_at->copy()->startOfDay()->diffInDays(now()->startOfDay());

        return min($term, max($accrualDays, $calendarDays));
    }

    public function computedProgressPercent(): int
    {
        $term = $this->termDays();

        if ($term <= 0) {
            return min(100, (int) $this->progress_percent);
        }

        return min(100, (int) round($this->activeDays() / $term * 100));
    }

    public function formattedTflops(): string
    {
        return number_format($this->tflops, 0, '.', ',');
    }

    public function formattedPrincipal(): string
    {
        $amount = (float) ($this->principal_amount ?? 0);
        $currency = $this->currency ?? $this->plan?->currency ?? 'USDT';

        return number_format($amount, 2, '.', ',').' '.$currency;
    }

    public function termDays(): int
    {
        if ($this->duration_days > 0) {
            return (int) $this->duration_days;
        }

        if ($this->started_at && $this->ends_at) {
            return (int) $this->started_at->diffInDays($this->ends_at);
        }

        return (int) ($this->plan?->duration_days ?? 0);
    }

    public function formattedDuration(): string
    {
        $days = $this->termDays();

        if ($days <= 0) {
            return __('coin.invest.by_agreement');
        }

        return __('coin.invest.duration_days', ['count' => $days]);
    }

    public function formattedEndsAt(): string
    {
        if ($this->ends_at) {
            return LocaleFormat::date($this->ends_at);
        }

        if (filled($this->ends_label) && $this->ends_label !== 'By agreement') {
            return $this->ends_label;
        }

        return __('coin.invest.by_agreement');
    }

    public function displayLocationLabel(): string
    {
        return PlanLabels::infra($this->location_label);
    }

    public function dailyProfitAmount(): float
    {
        $principal = (float) ($this->principal_amount ?? $this->plan?->price_amount ?? 0);
        $apr = (float) ($this->annual_profit_percent ?? $this->plan?->annual_profit_percent ?? 0);

        if ($principal <= 0 || $apr <= 0) {
            return 0.0;
        }

        return round($principal * ($apr / 100) / 365, 2);
    }

    public function formattedDailyProfit(): string
    {
        $daily = $this->dailyProfitAmount();

        if ($daily <= 0) {
            return '—';
        }

        $currency = $this->currency ?? $this->plan?->currency ?? 'USDT';

        return number_format($daily, 2, '.', ',').' '.$currency;
    }

    public function formattedAnnualProfit(): ?string
    {
        $apr = $this->annual_profit_percent ?? $this->plan?->annual_profit_percent;

        if ($apr === null) {
            return null;
        }

        return number_format((float) $apr, 1, '.', '').'%';
    }

    public function statusLabel(): string
    {
        $key = 'coin.contract_status.'.$this->status;

        return __($key) !== $key ? __($key) : strtoupper($this->status);
    }

    public function title(): string
    {
        return __('coin.investment').' · '.($this->plan?->displayName() ?? '—');
    }
}
