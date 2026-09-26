@php
  $searchProperty = $searchProperty ?? 'walletSearch';
  $placeholder = $placeholder ?? __('coin.wallet.search_transactions');
@endphp

<div class="coin-list-toolbar">
  <input
    type="search"
    class="coin-list-toolbar__search"
    wire:model.live.debounce.300ms="{{ $searchProperty }}"
    placeholder="{{ $placeholder }}"
  >
  @if($searchProperty === 'walletSearch' && filled($walletSearch ?? null))
  <button type="button" class="coin-list-toolbar__btn" wire:click="clearWalletSearch">{{ __('coin.wallet.clear_search') }}</button>
  @endif
</div>
