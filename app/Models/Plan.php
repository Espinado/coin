<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tier_label',
        'price_label',
        'min_deposit',
        'price_amount',
        'annual_profit_percent',
        'currency',
        'tflops',
        'duration_days',
        'infra',
        'reward_multiplier',
        'daily_estimate',
        'max_tflops',
        'sort_order',
        'is_featured',
        'is_active',
        'capacity_percent',
    ];

    protected function casts(): array
    {
        return [
            'min_deposit' => 'decimal:2',
            'price_amount' => 'decimal:2',
            'annual_profit_percent' => 'decimal:2',
            'reward_multiplier' => 'decimal:2',
            'daily_estimate' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function formattedTflops(): string
    {
        return number_format($this->tflops, 0, '.', ',');
    }

    public function formattedDailyEstimate(): ?string
    {
        if ($this->daily_estimate === null) {
            return null;
        }

        return '~'.number_format((float) $this->daily_estimate, 1, '.', '').' / day';
    }

    public function formattedDuration(): string
    {
        if ($this->duration_days === null) {
            return 'By agreement';
        }

        return $this->duration_days.' days';
    }

    public function isCurrentFor(?Plan $activePlan): bool
    {
        return $activePlan !== null && $this->id === $activePlan->id;
    }

    public function isEnterprise(): bool
    {
        return $this->slug === 'enterprise';
    }

    public function actionLabel(?Plan $activePlan = null): string
    {
        if ($this->isCurrentFor($activePlan)) {
            return 'Manage deposit';
        }

        if ($this->isEnterprise()) {
            return 'Contact sales';
        }

        if ($activePlan !== null && $this->sort_order > $activePlan->sort_order) {
            return 'Upgrade';
        }

        return 'Invest';
    }

    public function calculatorTermLabel(): string
    {
        if ($this->duration_days === null) {
            return 'CUSTOM TERM';
        }

        if ($this->duration_days >= 365) {
            return '12-MONTH CONTRACT';
        }

        if ($this->duration_days >= 180) {
            return '6-MONTH CONTRACT';
        }

        return $this->duration_days.'-DAY CONTRACT';
    }

    public function formattedComputeLabel(): string
    {
        if ($this->min_deposit !== null) {
            return number_format((float) $this->min_deposit, 0, '.', ',').' '.($this->currency ?? 'USDT');
        }

        if ($this->isEnterprise()) {
            return 'Custom';
        }

        return $this->formattedTflops().' TFLOPS';
    }

    public function formattedAnnualProfit(): ?string
    {
        if ($this->annual_profit_percent === null) {
            return null;
        }

        return number_format((float) $this->annual_profit_percent, 1, '.', '').'%';
    }

    public function formattedMinDeposit(): ?string
    {
        if ($this->min_deposit === null) {
            return null;
        }

        return number_format((float) $this->min_deposit, 0, '.', ',').' '.($this->currency ?? 'USDT');
    }
}
