<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
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
            'accrued_amount' => 'decimal:2',
        ];
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
        return number_format((float) $this->accrued_amount, 2, '.', ',');
    }

    public function formattedTflops(): string
    {
        return number_format($this->tflops, 0, '.', ',');
    }

    public function formattedPrincipal(): string
    {
        $amount = $this->principal_amount ?? $this->plan?->price_amount ?? $this->tflops;
        $currency = $this->currency ?? $this->plan?->currency ?? 'USDT';

        return number_format((float) $amount, 2, '.', ',').' '.$currency;
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
        return __('coin.investment').' · '.($this->plan?->name ?? '—');
    }
}
