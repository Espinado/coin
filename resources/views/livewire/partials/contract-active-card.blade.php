@php
    $isPrimary = $primaryContract && $contract->id === $primaryContract->id;
@endphp

<div style="padding: 24px; border-radius: 16px; border: 1px solid {{ $isPrimary ? 'oklch(0.86 0.11 195 / 0.26)' : 'rgba(150,235,250,0.12)' }}; background: {{ $isPrimary ? 'linear-gradient(170deg, oklch(0.6 0.13 200 / 0.16), rgba(150,235,250,0.03))' : 'rgba(150,235,250,0.035)' }};">
  <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 16px; min-width: 0;">
      <div style="width: 46px; height: 46px; flex: none; border-radius: 13px; background: {{ $isPrimary ? 'linear-gradient(150deg, oklch(0.72 0.13 198), oklch(0.44 0.12 215))' : 'linear-gradient(150deg, #1a4055, #0b2030)' }}; border: 1px solid {{ $isPrimary ? 'rgba(190,250,255,0.35)' : 'rgba(150,235,250,0.22)' }}; display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 4px; background: {{ $isPrimary ? '#eafcff' : 'oklch(0.7 0.1 200)' }};"></span></div>
      <div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="font-size: 16.5px; font-weight: 600;">{{ $contract->title() }}</span>
          <span style="padding: 3px 9px; border-radius: 6px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: oklch(0.88 0.14 160);">{{ $contract->statusLabel() }}</span>
          @if(! empty($pendingPlanChange))
          <span style="padding: 3px 9px; border-radius: 6px; background: rgba(255,180,84,0.16); border: 1px solid rgba(255,180,84,0.35); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.08em; color: #ffb454;">{{ __('coin.invest.plan_change_pending_badge', ['plan' => $pendingPlanChange->toPlan?->displayName()]) }}</span>
          @endif
        </div>
        <div style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.7);">{{ $contract->code }} · {{ $contract->displayLocationLabel() }}</div>
      </div>
    </div>
    <div style="display: flex; gap: 8px;">
      <button type="button" wire:click="openContractDetails({{ $contract->id }})" style="padding: 10px 16px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 46px -22px oklch(0.8 0.13 195 / 0.85);">{{ __('coin.contract.details') }}</button>
      @if(empty($pendingPlanChange))
      <button type="button" wire:click="openChangePlan({{ $contract->id }})" style="padding: 10px 16px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 46px -22px oklch(0.8 0.13 195 / 0.85);">{{ __('coin.invest.change_plan') }}</button>
      @endif
    </div>
  </div>
  <div style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 20px; margin-top: 24px;">
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.principal')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedPrincipal() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.apr')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedAnnualProfit() ?? '—' }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.daily_profit')) }}</div><div style="margin-top: 9px; font-size: 14px; color: oklch(0.9 0.12 192);">{{ $contract->formattedDailyProfit() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.profit_accrued')) }}</div><div style="margin-top: 9px; font-size: 14px; color: oklch(0.9 0.12 192);">{{ $contract->formattedAccrued() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.maturity')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedEndsAt() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.progress')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->computedProgressPercent() }}%</div></div>
  </div>
  <div style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: rgba(214,238,248,0.74);"><span>{{ __('coin.contract.progress_label') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $contract->activeDays() }} / {{ $contract->termDays() }} {{ __('coin.contract.days') }}</span></div>
    <div style="margin-top: 10px; height: 6px; border-radius: 4px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->computedProgressPercent() }}%; height: 100%; border-radius: 4px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
  </div>
</div>
