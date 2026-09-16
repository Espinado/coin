@php
    $isChangeMode = ! empty($changingContract);
    $isCurrent = $isChangeMode && $plan->isCurrentFor($primaryPlan);
    $isSelected = (int) ($selectedPlanId ?? 0) === $plan->id;
    $isEnterprise = $plan->isEnterprise();
    $isCluster = $plan->slug === 'cluster';
    $actionLabel = $isEnterprise ? __('coin.invest.contact_sales') : __('coin.actions.select');
    $selectTarget = 'selectPlan('.$plan->id.')';
    $selectedStyle = $isSelected && ! $isCurrent
        ? 'border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.18), rgba(150,235,250,0.04)); box-shadow: 0 24px 60px -40px oklch(0.7 0.14 195 / 0.7);'
        : '';
    $currentCardStyle = 'border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.24), rgba(150,235,250,0.03)); box-shadow: 0 24px 60px -40px oklch(0.7 0.14 195 / 0.9);';
    $defaultStyle = $isEnterprise
        ? 'border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(170deg, rgba(120,110,220,0.13), rgba(150,235,250,0.02));'
        : 'border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);';
    $cardStyle = $selectedStyle ?: ($isCurrent ? $currentCardStyle : $defaultStyle);
@endphp

<div class="coin-plan-card" style="padding: 24px; border-radius: 18px; {{ $cardStyle }}">
  @if($isCurrent)
  <div class="coin-plan-card__badge">{{ mb_strtoupper(__('coin.invest.current_badge')) }}</div>
  @elseif($isSelected)
  <div class="coin-plan-card__badge">{{ mb_strtoupper(__('coin.invest.selected')) }}</div>
  @endif

  <div class="coin-plan-card__icon-row">
    @if($isEnterprise)
    <div class="coin-plan-card__icon coin-plan-card__icon--enterprise">
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.5);"></span></div>
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: oklch(0.78 0.15 292);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.4);"></span></div>
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: rgba(190,190,255,0.4);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.28);"></span></div>
    </div>
    @elseif($isCluster)
    <div class="coin-plan-card__icon coin-plan-card__icon--cluster">
      <div class="coin-plan-card__cluster-node coin-plan-card__cluster-node--left"></div>
      <div class="coin-plan-card__cluster-node coin-plan-card__cluster-node--center"></div>
      <div class="coin-plan-card__cluster-node coin-plan-card__cluster-node--right"></div>
    </div>
    @elseif($isCurrent)
    <div class="coin-plan-card__icon coin-plan-card__icon--current"><span></span></div>
    @else
    <div class="coin-plan-card__icon"><span></span></div>
    @endif
    <span class="coin-plan-card__tier">{{ $plan->displayTierLabel() }}</span>
  </div>

  <div class="coin-plan-card__title">{{ $plan->displayName() }}</div>

  <div class="coin-plan-card__headline">
    <span class="coin-plan-card__price">{{ $plan->formattedPriceLabel() }}</span>
    @if(! $isEnterprise)
    <span class="coin-plan-card__duration">{{ $plan->formattedDuration() }}</span>
    @endif
  </div>

  <div class="coin-plan-card__divider"></div>

  <div class="coin-plan-card__specs">
    <div class="coin-plan-card__spec-row"><span>{{ __('coin.invest.min_investment') }}</span><span>{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
    <div class="coin-plan-card__spec-row"><span>{{ __('coin.invest.term') }}</span><span>{{ $plan->formattedDuration() }}</span></div>
    <div class="coin-plan-card__spec-row"><span>{{ __('coin.invest.annual_return') }}</span><span>{{ $plan->formattedAnnualProfit() ?? ($plan->formattedDailyEstimate() ?? __('coin.invest.estimated')) }}</span></div>
    <div class="coin-plan-card__spec-row"><span>{{ __('coin.invest.infrastructure') }}</span><span>{{ $plan->displayInfra() }}</span></div>
  </div>

  <div class="coin-plan-card__footer">
    <div class="coin-plan-card__capacity"><div style="width: {{ $plan->capacity_percent ?? ($isEnterprise ? 100 : 0) }}%; height: 100%; border-radius: 3px; background: {{ $isEnterprise ? 'linear-gradient(90deg, oklch(0.78 0.15 292), oklch(0.88 0.12 192))' : ($isCluster ? 'oklch(0.8 0.12 198)' : 'oklch(0.7 0.11 205)') }};"></div></div>
    <button type="button" wire:click="selectPlan({{ $plan->id }})" wire:loading.attr="disabled" wire:target="{{ $selectTarget }}" class="coin-plan-card__cta coin-plan-card__cta--{{ $isSelected ? 'selected' : 'default' }}{{ $isEnterprise ? ' coin-plan-card__cta--enterprise' : '' }}">
      <span wire:loading.remove wire:target="{{ $selectTarget }}">{{ $actionLabel }}</span>
      <span wire:loading wire:target="{{ $selectTarget }}">{{ __('coin.actions.selecting') }}</span>
    </button>
  </div>
</div>
