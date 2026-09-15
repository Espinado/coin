<div class="coin-sidebar-investments">
  <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">
      {{ mb_strtoupper($activeContracts->count() > 1 ? __('coin.invest.active_plural') : __('coin.invest.active')) }}
    </div>
    @if($activeContracts->count() > 0)
    <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; padding: 2px 7px; border-radius: 6px; background: rgba(150,235,250,0.1); color: rgba(214,238,248,0.8);">{{ $activeContracts->count() }}</span>
    @endif
  </div>

  @if($activeContracts->isEmpty())
  <div style="margin-top: 10px; font-size: 12.5px; line-height: 1.45; color: rgba(214,238,248,0.68);">{{ __('coin.invest.no_active') }}</div>
  @else
  <div class="coin-sidebar-investments-list" style="margin-top: 12px; display: flex; flex-direction: column; gap: 10px; max-height: min(240px, 32vh); overflow-y: auto; padding-right: 2px;">
    @foreach($activeContracts as $contract)
    <div wire:key="sidebar-contract-{{ $contract->id }}" style="padding: 12px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.14); background: rgba(4,16,28,0.35);">
      <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
        <div style="min-width: 0;">
          <div style="font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contract->plan?->displayName() ?? '—' }}</div>
          <div style="margin-top: 3px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.72); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $contract->formattedPrincipal() }} · {{ $contract->termDays() }} {{ __('coin.invest.days_suffix') }}</div>
        </div>
        <button type="button" wire:click="setSection(2)" style="flex: none; align-self: flex-start; border: 0; background: transparent; padding: 2px 0 0; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.08em; color: oklch(0.88 0.11 195); cursor: pointer; white-space: nowrap;">{{ mb_strtoupper(__('coin.actions.open')) }}</button>
      </div>
      <div style="margin-top: 10px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.14);">
        <div style="width: {{ $contract->computedProgressPercent() }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div>
      </div>
      <div style="margin-top: 6px; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ __('coin.invest.elapsed', ['elapsed' => $contract->activeDays(), 'total' => $contract->termDays()]) }}</div>
    </div>
    @endforeach
  </div>
  @endif

  <button type="button" wire:click="setSection(1)" style="width: 100%; margin-top: 14px; padding: 10px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">{{ __('coin.actions.new_investment') }}</button>
</div>
