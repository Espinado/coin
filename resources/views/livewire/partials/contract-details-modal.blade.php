@if($contract = $this->contractDetails)
@php
  $plan = $contract->plan;
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px); overflow-y: auto;"
  wire:click="closeContractDetails"
  wire:keydown.escape.window="closeContractDetails"
>
  <div
    style="width: min(100%, 520px); max-height: min(90dvh, 820px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden; overflow-y: auto;"
    wire:click.stop
  >
    <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
      <div style="min-width: 0;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ mb_strtoupper(__('coin.contract.details_title')) }}</div>
        <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">{{ $plan?->displayName() ?? '—' }}</span>
          <span style="padding: 3px 9px; border-radius: 6px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: oklch(0.88 0.14 160);">{{ $contract->statusLabel() }}</span>
        </div>
        <div style="margin-top: 6px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.7);">{{ $contract->code }} · {{ $contract->displayLocationLabel() }}</div>
      </div>
      <button type="button" wire:click="closeContractDetails" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1; flex: none;">×</button>
    </div>

    <div style="padding: 22px; display: flex; flex-direction: column; gap: 20px;">
      @if($plan)
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.contract.plan_section')) }}</div>
        <div style="margin-top: 14px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.tier') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->displayTierLabel() }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.min_investment') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.term') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->formattedDuration() }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.annual_return') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->formattedAnnualProfit() ?? ($plan->formattedDailyEstimate() ?? __('coin.invest.estimated')) }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.infrastructure') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->displayInfra() }}</span></div>
          @if($plan->formattedDailyEstimate())
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.86);">{{ __('coin.invest.daily_estimate') }}</span><span class="coin-modal-value" style="text-align: right;">{{ $plan->formattedDailyEstimate() }}</span></div>
          @endif
        </div>
      </div>
      @endif

      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.contract.investment_section')) }}</div>
        <div style="margin-top: 14px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; font-size: 13px;">
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.principal')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->formattedPrincipal() }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.apr')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->formattedAnnualProfit() ?? '—' }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.daily_profit')) }}</div><div class="coin-modal-value" style="margin-top: 8px; color: oklch(0.9 0.12 192);">{{ $contract->formattedDailyProfit() }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.profit_accrued')) }}</div><div class="coin-modal-value" style="margin-top: 8px; color: oklch(0.9 0.12 192);">{{ $contract->formattedAccrued() }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.started_at')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->started_at ? \App\Support\LocaleFormat::date($contract->started_at) : ($contract->started_label ?? '—') }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.maturity')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->formattedEndsAt() }}</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.progress')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->computedProgressPercent() }}%</div></div>
          <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: rgba(214,238,248,0.82);">{{ mb_strtoupper(__('coin.contract.contract_code')) }}</div><div class="coin-modal-value" style="margin-top: 8px;">{{ $contract->code }}</div></div>
        </div>
        <div style="margin-top: 16px;">
          <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: rgba(214,238,248,0.86);"><span>{{ __('coin.contract.progress_label') }}</span><span class="coin-modal-value" style="flex: none; text-align: right;">{{ $contract->activeDays() }} / {{ $contract->termDays() }} {{ __('coin.contract.days') }}</span></div>
          <div style="margin-top: 10px; height: 6px; border-radius: 4px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->computedProgressPercent() }}%; height: 100%; border-radius: 4px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
        </div>
      </div>

      <button type="button" wire:click="closeContractDetails" style="width: 100%; padding: 13px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer;">{{ __('coin.close') }}</button>
    </div>
  </div>
</div>
@endif
