@php
  $searchProperty = $searchProperty ?? 'walletSearch';
  $placeholder = $placeholder ?? __('coin.wallet.search_transactions');
@endphp

<div style="display: flex; align-items: center; gap: 12px; margin-top: 18px; flex-wrap: wrap;">
  <input
    type="search"
    wire:model.live.debounce.300ms="{{ $searchProperty }}"
    placeholder="{{ $placeholder }}"
    style="flex: 1; min-width: 220px; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(4,16,28,0.85); color: #e6f4fa; font-family: inherit; font-size: 13px;"
  >
  @if($searchProperty === 'walletSearch' && filled($walletSearch ?? null))
  <button type="button" wire:click="clearWalletSearch" style="padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 12.5px; cursor: pointer;">{{ __('coin.wallet.clear_search') }}</button>
  @endif
</div>
