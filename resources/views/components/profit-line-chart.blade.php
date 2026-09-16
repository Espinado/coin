@props(['chart', 'height' => 210, 'areaId' => 'profitChartArea', 'lineId' => 'profitChartLine'])

@if($chart['hasData'])
<div style="position: relative; height: {{ $height }}px;">
  <div style="position: absolute; inset: 0 0 0 0; display: grid; grid-template-columns: 52px minmax(0, 1fr); gap: 8px; pointer-events: none;">
    <div style="display: flex; flex-direction: column; justify-content: space-between; padding: 4px 0 22px; font-family: 'JetBrains Mono', monospace; font-size: 9px; line-height: 1.2; color: rgba(214,238,248,0.55); text-align: right;">
      <span>{{ $chart['yMaxLabel'] }}</span>
      <span>{{ $chart['yMidLabel'] }}</span>
      <span>0</span>
    </div>
    <div style="position: relative; min-width: 0;">
      <svg viewBox="0 0 1000 210" preserveAspectRatio="none" style="width: 100%; height: 100%; overflow: visible; display: block;">
        <defs>
          <linearGradient id="{{ $areaId }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="oklch(0.88 0.12 192)" stop-opacity="0.38"></stop>
            <stop offset="100%" stop-color="oklch(0.72 0.11 210)" stop-opacity="0.03"></stop>
          </linearGradient>
          <linearGradient id="{{ $lineId }}" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="oklch(0.72 0.11 210)"></stop>
            <stop offset="100%" stop-color="#eafcff"></stop>
          </linearGradient>
        </defs>
        <path d="{{ $chart['areaPath'] }}" fill="url(#{{ $areaId }})"></path>
        <path d="{{ $chart['linePath'] }}" fill="none" stroke="url(#{{ $lineId }})" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" style="filter: drop-shadow(0 0 10px oklch(0.88 0.12 192 / 0.55));"></path>
        @foreach($chart['points'] as $point)
        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="{{ $point['highlight'] ? 5 : 3 }}" fill="{{ $point['highlight'] ? '#eafcff' : 'oklch(0.84 0.12 195)' }}" opacity="{{ $point['highlight'] ? 1 : 0.55 }}" vector-effect="non-scaling-stroke" style="@if($point['highlight']) filter: drop-shadow(0 0 12px oklch(0.88 0.12 192 / 0.85)); @endif">
          <title>{{ $point['tooltip'] }}</title>
        </circle>
        @endforeach
      </svg>
      @foreach($chart['points'] as $point)
        @if($point['showLabel'])
        <div style="position: absolute; left: {{ $point['xPct'] }}%; top: {{ $point['yPct'] }}%; transform: translate(-50%, calc(-100% - 8px)); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; line-height: 1; color: #eafcff; white-space: nowrap; text-shadow: 0 1px 6px rgba(0,0,0,0.65); pointer-events: none;">{{ $point['valueLabel'] }}</div>
        @endif
      @endforeach
    </div>
  </div>
</div>
<div style="display: flex; justify-content: space-between; margin-top: 14px; margin-left: 60px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);">
  @foreach($chart['axis'] as $tick)
  <span>{{ is_array($tick) ? $tick['label'] : $tick }}</span>
  @endforeach
</div>
@endif
