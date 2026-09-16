<?php

namespace App\Models;

use App\Support\PlanLabels;
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

    public function displayTierLabel(): string
    {
        return PlanLabels::tier((string) $this->tier_label);
    }

    public function displayName(): string
    {
        return PlanLabels::name((string) $this->slug, (string) $this->name);
    }

    public function displayInfra(): string
    {
        return PlanLabels::infra($this->infra);
    }

    public function formattedDuration(): string
    {
        if ($this->duration_days === null) {
            return __('coin.invest.by_agreement');
        }

        return __('coin.invest.duration_days', ['count' => $this->duration_days]);
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
            return __('coin.invest.manage');
        }

        if ($this->isEnterprise()) {
            return __('coin.invest.contact_sales');
        }

        if ($activePlan !== null && $this->sort_order > $activePlan->sort_order) {
            return __('coin.invest.upgrade');
        }

        return __('coin.actions.select');
    }

    public function calculatorTermLabel(): string
    {
        if ($this->duration_days === null) {
            return __('coin.invest.custom_term');
        }

        if ($this->duration_days >= 365) {
            return __('coin.invest.contract_12_month');
        }

        if ($this->duration_days >= 180) {
            return __('coin.invest.contract_6_month');
        }

        return __('coin.invest.contract_n_days', ['days' => $this->duration_days]);
    }

    public function displayCurrency(): string
    {
        return (string) config('coin.wallet.base_currency', 'USDT');
    }

    public function formattedComputeLabel(): string
    {
        if ($this->min_deposit !== null) {
            return number_format((float) $this->min_deposit, 0, '.', ',').' '.$this->displayCurrency();
        }

        if ($this->isEnterprise()) {
            return __('coin.invest.by_agreement');
        }

        return number_format((float) $this->tflops, 0, '.', ',').' '.$this->displayCurrency();
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

        return number_format((float) $this->min_deposit, 0, '.', ',').' '.$this->displayCurrency();
    }

    public function calculatorMinAmount(): int
    {
        if ($this->isEnterprise()) {
            return 10_000;
        }

        return (int) max(1, $this->min_deposit ?? $this->tflops ?? 100);
    }

    public function calculatorMaxAmount(): int
    {
        if ($this->isEnterprise()) {
            return 50_000;
        }

        $min = $this->calculatorMinAmount();

        return (int) max($min, $this->max_tflops ?? ($min * 4));
    }

    public function calculatorStep(): int
    {
        $range = $this->calculatorMaxAmount() - $this->calculatorMinAmount();

        if ($range <= 500) {
            return 10;
        }

        if ($range <= 2_500) {
            return 50;
        }

        return 100;
    }
}
