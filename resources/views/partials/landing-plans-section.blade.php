@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Plan> $plans */
    $defaultPlan = $plans->firstWhere('is_featured', true) ?? $plans->first();
    $currency = (string) config('coin.wallet.base_currency', 'USDT');
@endphp

<style>
    #plans {
        color: #e6f4fa;
    }

    #plans .landing-plan-card {
        color: #f0fbff;
    }

    #plans .landing-plan-card__title {
        color: #f0fbff;
    }

    #plans .landing-plan-card__price {
        color: #f0fbff;
    }

    #plans .landing-plan-card__value {
        color: #f0fbff;
    }

    #plans .landing-plans-calculator {
        color: #e6f4fa;
    }

    #plans .landing-plans-calculator__title {
        color: #f0fbff;
    }

    #plans .landing-plans-calculator__stat {
        color: #f0fbff;
    }

    #plans [data-landing-slider] {
        accent-color: oklch(0.8 0.13 192);
    }

    @media (max-width: 900px) {
        .landing-plans-calculator { grid-template-columns: 1fr !important; gap: 28px !important; }
        #plans { padding-left: 24px !important; padding-right: 24px !important; }
    }
</style>

<section id="plans" data-screen-label="Plans" style="position: relative; z-index: 5; padding: 48px 72px; border-top: 1px solid rgba(150,235,250,0.08); color: #e6f4fa;">
    <div style="text-align: center; max-width: 620px; margin: 0 auto;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">PLANS</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Choose your investment plan</h2>
        <p style="margin: 18px 0 0; font-size: 16px; line-height: 1.6; color: rgba(230,244,250,0.72);">Fixed APR plans with transparent daily profit. Estimates are indicative; terms apply at purchase.</p>
    </div>

    <div id="landing-plans" style="margin-top: 48px;">
        <script type="application/json" id="landing-plans-data">@json($landingPlansPayload)</script>

        @if($plans->isEmpty())
            <p style="margin: 0; text-align: center; color: rgba(230,244,250,0.72);">{{ __('coin.overview.no_plans') }}</p>
        @else
            <div class="landing-plans-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                @foreach($plans as $plan)
                    @php
                        $isFeatured = (bool) $plan->is_featured;
                        $isDefaultSelected = $defaultPlan && (int) $plan->id === (int) $defaultPlan->id;
                        $cardBorder = $isFeatured || $isDefaultSelected
                            ? 'border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(150,235,250,0.03)); box-shadow: 0 30px 70px -44px oklch(0.7 0.14 195 / 0.9);'
                            : 'border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);';
                        $buttonStyle = $isDefaultSelected
                            ? 'border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-weight: 600;'
                            : 'border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-weight: 500;';
                    @endphp
                    <div
                        data-landing-card="{{ $plan->id }}"
                        data-landing-featured="{{ $isFeatured ? '1' : '0' }}"
                        class="landing-plan-card{{ $isDefaultSelected ? ' landing-plan-card--selected' : '' }}"
                        style="position: relative; padding: 30px; border-radius: 20px; {{ $cardBorder }} display: flex; flex-direction: column;"
                    >
                        @if($isFeatured)
                            <div style="position: absolute; top: -10px; left: 30px; padding: 4px 11px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: #04121f;">{{ mb_strtoupper($plan->displayTierLabel()) }}</div>
                        @endif
                        <div class="landing-plan-card__title" style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">{{ $plan->displayName() }}</div>
                        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.74);">{{ $plan->displayTierLabel() }}</div>
                        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
                            <span class="landing-plan-card__price" style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">{{ $plan->formattedPriceLabel() }}</span>
                        </div>
                        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 24px 0;"></div>
                        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
                            <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.min_investment') }}</span><span class="landing-plan-card__value" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
                            <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.term') }}</span><span class="landing-plan-card__value" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $plan->formattedDuration() }}</span></div>
                            <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">APR</span><span class="landing-plan-card__value" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $plan->formattedAnnualProfit() ?? '—' }}</span></div>
                            <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.daily_estimate') }}</span><span class="landing-plan-card__value" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $plan->formattedDailyEstimate() ?? '—' }}</span></div>
                            <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.infrastructure') }}</span><span class="landing-plan-card__value" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $plan->displayInfra() }}</span></div>
                        </div>
                        <button
                            type="button"
                            data-landing-select="{{ $plan->id }}"
                            style="margin-top: 28px; padding: 12px; border-radius: 11px; {{ $buttonStyle }} font-family: inherit; font-size: 14px; cursor: pointer;"
                        >{{ __('coin.actions.select') }}</button>
                    </div>
                @endforeach
            </div>

            @php
                $initialPlan = $defaultPlan ?? $plans->first();
                $initialAmount = $initialPlan->calculatorMinAmount();
                $initialApr = (float) ($initialPlan->annual_profit_percent ?? 0);
                $initialDaily = $initialApr > 0
                    ? round($initialAmount * ($initialApr / 100) / 365, 2)
                    : 0;
            @endphp

            <div class="landing-plans-calculator" style="margin-top: 20px; padding: 32px 36px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.08); display: grid; grid-template-columns: 1fr 320px; gap: 48px; align-items: center; color: #e6f4fa;">
                <div>
                    <div class="landing-plans-calculator__title" style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">Profit estimate</div>
                    <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
                        <span style="font-size: 13.5px; color: rgba(230,244,250,0.74);">{{ __('coin.invest.investment_amount') }}</span>
                        <span data-landing-amount style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ number_format($initialAmount, 0, '.', ',') }} <span style="font-size: 12px; color: rgba(230,244,250,0.7);">{{ $currency }}</span></span>
                    </div>
                    <input
                        type="range"
                        data-landing-slider
                        min="{{ $initialPlan->calculatorMinAmount() }}"
                        max="{{ $initialPlan->calculatorMaxAmount() }}"
                        step="{{ $initialPlan->calculatorStep() }}"
                        value="{{ $initialAmount }}"
                        style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;"
                    />
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.65);">
                        <span data-landing-slider-min>{{ number_format($initialPlan->calculatorMinAmount(), 0, '.', ' ') }}</span>
                        <span data-landing-slider-mid>{{ number_format((int) round(($initialPlan->calculatorMinAmount() + $initialPlan->calculatorMaxAmount()) / 2), 0, '.', ' ') }}</span>
                        <span data-landing-slider-max>{{ number_format($initialPlan->calculatorMaxAmount(), 0, '.', ' ') }}</span>
                    </div>
                    <p style="margin: 20px 0 0; font-size: 12px; line-height: 1.55; color: rgba(230,244,250,0.65);">This calculation is indicative only. Actual profit follows plan APR; returns are not guaranteed.</p>
                </div>
                <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(20,55,80,0.75), rgba(6,20,35,0.9));">
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">EST. PER DAY</div>
                    <div data-landing-daily style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 32px; color: oklch(0.9 0.12 192);">{{ number_format($initialDaily, 2, '.', ',') }}</div>
                    <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
                    <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px; margin-bottom: 12px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.per_month') }}</span><span data-landing-monthly class="landing-plans-calculator__stat" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ number_format($initialDaily * 30, 1, '.', ',') }}</span></div>
                    <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">{{ __('coin.invest.matching_plan') }}</span><span data-landing-plan-name class="landing-plans-calculator__stat" style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: #f0fbff;">{{ $initialPlan->displayName() }}</span></div>
                </div>
            </div>
        @endif
    </div>
</section>
