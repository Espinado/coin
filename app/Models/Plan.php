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
            return 'Manage plan';
        }

        if ($this->isEnterprise()) {
            return 'Contact sales';
        }

        if ($activePlan !== null && $this->sort_order > $activePlan->sort_order) {
            return 'Upgrade';
        }

        return 'Activate';
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
        if ($this->isEnterprise()) {
            return number_format($this->tflops, 0, '.', ',').'+ TFLOPS';
        }

        return $this->formattedTflops().' TFLOPS';
    }
}
