<?php

namespace App\Models;

use App\Support\MoneyFormat;
use App\Support\PlanLabels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plan extends Model
{
    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    public const OFFER_STATUS_PENDING = 'pending';

    public const OFFER_STATUS_ACCEPTED = 'accepted';

    public const OFFER_STATUS_EXPIRED = 'expired';

    public const OFFER_STATUS_REVOKED = 'revoked';

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
        'visibility',
        'offer_status',
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

    public function offer(): HasOne
    {
        return $this->hasOne(PlanOffer::class);
    }

    public function scopePublicCatalog(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $inner) {
                $inner->where('visibility', self::VISIBILITY_PUBLIC)
                    ->orWhereNull('visibility');
            });
    }

    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    public function isPublicCatalog(): bool
    {
        return ! $this->isPrivate();
    }

    public function formattedTflops(): string
    {
        return number_format($this->tflops, 0, '.', ',');
    }

    public function formattedDailyEstimate(): ?string
    {
        $daily = $this->estimatedDailyProfit();

        if ($daily === null) {
            return null;
        }

        return '~'.MoneyFormat::amount($daily, $this->displayCurrency(), 1).' / day';
    }

    /**
     * Daily profit estimate at minimum purchase from plan APR.
     * Falls back to stored daily_estimate only when APR/principal are missing.
     */
    public function estimatedDailyProfit(?float $amount = null): ?float
    {
        $apr = (float) ($this->annual_profit_percent ?? 0);
        $principal = $amount ?? (float) ($this->min_deposit ?? $this->price_amount ?? 0);

        if ($apr > 0 && $principal > 0) {
            return round($principal * ($apr / 100) / 365, 2);
        }

        if ($this->daily_estimate !== null) {
            return (float) $this->daily_estimate;
        }

        return null;
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

    public function requiredDepositAmount(): float
    {
        return (float) ($this->min_deposit ?? $this->price_amount ?? 0);
    }

    public function changePlanActionLabel(?Plan $currentPlan): string
    {
        if ($this->isCurrentFor($currentPlan)) {
            return __('coin.invest.current_plan_short');
        }

        if ($this->isEnterprise() && $this->min_deposit === null) {
            return __('coin.invest.contact_sales');
        }

        return __('coin.invest.switch_to_plan');
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

    public function formattedPriceLabel(): string
    {
        $label = trim((string) ($this->price_label ?? ''));

        if ($label === '') {
            return '—';
        }

        if ($this->isEnterprise() || ! str_starts_with($label, '$')) {
            return $label;
        }

        $amount = $this->min_deposit ?? $this->price_amount;

        if ($amount !== null) {
            return MoneyFormat::amount($amount, $this->displayCurrency(), 0);
        }

        $numeric = preg_replace('/[^0-9.]/', '', substr($label, 1));

        if ($numeric !== '' && is_numeric($numeric)) {
            return MoneyFormat::amount((float) $numeric, $this->displayCurrency(), 0);
        }

        return $label;
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
        if ($this->isPrivate()) {
            return (int) max(1, round((float) ($this->min_deposit ?? $this->price_amount ?? 1)));
        }

        if ($this->isEnterprise()) {
            return 10_000;
        }

        return (int) max(1, $this->min_deposit ?? $this->tflops ?? 100);
    }

    public function calculatorMaxAmount(): int
    {
        if ($this->isPrivate()) {
            return $this->calculatorMinAmount();
        }

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
