<div class="coin-dash-panel">
  <div class="coin-dash-panel__title">{{ __('coin.overview.plan_breakdown') }}</div>
  <div class="coin-dash-panel__sub">{{ __('coin.overview.plan_breakdown_sub') }}</div>
  <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
    @forelse($plans as $plan)
      @php
        $isEnterprise = $plan->isEnterprise();
        $cardBorder = $isEnterprise
            ? 'border: 1px solid rgba(180,180,255,0.16); background: rgba(150,140,255,0.07);'
            : 'border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);';
        $statusColor = $isEnterprise ? 'oklch(0.88 0.15 90)' : 'oklch(0.86 0.14 160)';
        $statusLabel = $isEnterprise ? __('coin.overview.expanding') : __('coin.overview.available');
      @endphp
      <div wire:key="overview-plan-{{ $plan->id }}" style="padding: 14px 16px; border-radius: 12px; {{ $cardBorder }}">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px;">
          <span style="font-weight: 500;">{{ $plan->displayName() }}</span>
          <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: {{ $statusColor }}; flex-shrink: 0;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor }};"></span>
            {{ mb_strtoupper($statusLabel) }}
          </span>
        </div>
        @if(! $isEnterprise)
        <div style="margin-top: 11px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);">
          <div style="width: {{ min(100, max(0, (int) ($plan->capacity_percent ?? 0))) }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div>
        </div>
        @endif
        <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; font-size: 11px;">
          <div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.1em; color: rgba(214,238,248,0.58);">{{ mb_strtoupper(__('coin.invest.min_investment')) }}</div>
            <div style="margin-top: 4px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.82);">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</div>
          </div>
          <div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.1em; color: rgba(214,238,248,0.58);">{{ mb_strtoupper(__('coin.invest.term')) }}</div>
            <div style="margin-top: 4px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.82);">{{ $plan->formattedDuration() }}</div>
          </div>
          <div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.1em; color: rgba(214,238,248,0.58);">{{ mb_strtoupper(__('coin.invest.annual_return')) }}</div>
            <div style="margin-top: 4px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.82);">{{ $plan->formattedAnnualProfit() ?? ($plan->formattedDailyEstimate() ?? __('coin.invest.by_agreement')) }}</div>
          </div>
        </div>
      </div>
    @empty
      <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-size: 13px; color: rgba(214,238,248,0.68);">
        {{ __('coin.overview.no_plans') }}
      </div>
    @endforelse
  </div>
</div>
