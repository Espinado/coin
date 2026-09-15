<div style="padding: 24px; border-radius: 16px; border: 1px dashed rgba(150,235,250,0.2); background: rgba(150,235,250,0.02);">
  <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 16px; min-width: 0;">
      <div style="width: 46px; height: 46px; flex: none; border-radius: 13px; background: linear-gradient(150deg, rgba(150,235,250,0.12), rgba(4,16,28,0.8)); border: 1px solid rgba(150,235,250,0.18); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 4px; background: rgba(214,238,248,0.45);"></span></div>
      <div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span style="font-size: 16.5px; font-weight: 600;">{{ $contract->title() }}</span>
          <span style="padding: 3px 9px; border-radius: 6px; background: rgba(150,235,250,0.08); border: 1px solid rgba(150,235,250,0.22); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: rgba(214,238,248,0.78);">{{ $contract->statusLabel() }}</span>
        </div>
        <div style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.7);">{{ $contract->code }} · {{ $contract->displayLocationLabel() }}</div>
        @if(filled($contract->completed_summary))
        <div style="margin-top: 8px; font-size: 12.5px; color: rgba(214,238,248,0.68);">{{ $contract->completed_summary }}</div>
        @endif
      </div>
    </div>
    <button wire:click="setSection(1)" style="padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.invest.renew') }}</button>
  </div>
  <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; margin-top: 24px;">
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.principal')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedPrincipal() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.profit_accrued')) }}</div><div style="margin-top: 9px; font-size: 14px; color: oklch(0.9 0.12 192);">{{ $contract->formattedAccrued() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.maturity')) }}</div><div style="margin-top: 9px; font-size: 14px;">{{ $contract->formattedEndsAt() }}</div></div>
    <div><div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.contract.progress')) }}</div><div style="margin-top: 9px; font-size: 14px;">100%</div></div>
  </div>
</div>
