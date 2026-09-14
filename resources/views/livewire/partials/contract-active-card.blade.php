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
        </div>
        <div style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.7);">{{ $contract->code }} · {{ $contract->location_label }}</div>
      </div>
    </div>
    <div style="display: flex; gap: 8px;">
      <button style="padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Details</button>
      <button wire:click="setSection(1)" style="padding: 10px 16px; border-radius: 10px; border: 1px solid {{ $isPrimary ? 'oklch(0.86 0.11 195 / 0.5)' : 'rgba(150,235,250,0.2)' }}; background: {{ $isPrimary ? 'linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205))' : 'rgba(150,235,250,0.06)' }}; color: {{ $isPrimary ? '#04121f' : '#e6f4fa' }}; font-family: inherit; font-size: 13px; font-weight: {{ $isPrimary ? '600' : '500' }}; cursor: pointer;">{{ $isPrimary ? 'Upgrade' : 'Upgrade' }}</button>
    </div>
  </div>
  <div style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 20px; margin-top: 24px;">
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PRINCIPAL</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedPrincipal() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">APR</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedAnnualProfit() ?? '—' }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">DAILY PROFIT</div><div style="margin-top: 9px; font-size: 14px; color: oklch(0.9 0.12 192);">{{ $contract->formattedDailyProfit() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PROFIT ACCRUED</div><div style="margin-top: 9px; font-size: 14px; color: oklch(0.9 0.12 192);">{{ $contract->formattedAccrued() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">MATURITY</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->ends_label }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PROGRESS</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->progress_percent }}%</div></div>
  </div>
  <div style="margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: rgba(214,238,248,0.74);"><span>Deposit progress</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $contract->days_elapsed }} / {{ $contract->duration_days }} days</span></div>
    <div style="margin-top: 10px; height: 6px; border-radius: 4px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->progress_percent }}%; height: 100%; border-radius: 4px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
  </div>
</div>
