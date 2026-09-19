@if($walletModalOpen)
@php
  $isBtc = $walletModalCurrency === 'BTC';
  $modalHint = $walletModalMode === 'disconnect'
    ? __('coin.profile.disconnect_wallet_hint')
    : ($isBtc
      ? __('coin.profile.btc_payout_address_hint', ['network' => $this->payoutNetworkLabel])
      : __('coin.profile.payout_address_hint', ['network' => $this->payoutNetworkLabel]));
  $placeholder = $isBtc
    ? __('coin.profile.btc_payout_address_placeholder')
    : __('coin.profile.payout_address_placeholder');
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px); overflow-y: auto;"
  wire:click="closeWalletModal"
  wire:keydown.escape.window="closeWalletModal"
>
  <div
    style="width: min(100%, 520px); max-height: min(90dvh, 760px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden; overflow-y: auto;"
    wire:click.stop
  >
    <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
      <div style="min-width: 0;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ mb_strtoupper(__('coin.profile.connected_wallet')) }} · {{ $walletModalCurrency }}</div>
        <div style="margin-top: 8px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">
          {{ $walletModalMode === 'disconnect' ? __('coin.profile.disconnect_wallet_title') : __('coin.profile.payout_address_modal_title') }}
        </div>
        <div style="margin-top: 6px; font-size: 12.5px; color: rgba(214,238,248,0.72);">
          {{ $modalHint }}
        </div>
      </div>
      <button type="button" wire:click="closeWalletModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1; flex: none;">×</button>
    </div>

    <div style="padding: 22px; display: flex; flex-direction: column; gap: 14px;">
      @if($walletModalMode === 'save')
        <div>
          <label style="display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.65);">{{ mb_strtoupper(__('coin.wallet.payout_address')) }} · {{ $this->payoutNetworkLabel }}</label>
          <input type="text" wire:model="payoutAddressInput" autocomplete="off" spellcheck="false" placeholder="{{ $placeholder }}" style="width: 100%; box-sizing: border-box; margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: 'JetBrains Mono', monospace; font-size: 13px;" />
          @error('payoutAddressInput')<p style="margin-top: 8px; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
        </div>

        <div>
          <label style="display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.65);">{{ mb_strtoupper(__('coin.profile.payout_address_confirm')) }}</label>
          <input type="text" wire:model="payoutAddressConfirm" autocomplete="off" spellcheck="false" placeholder="{{ $placeholder }}" style="width: 100%; box-sizing: border-box; margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: 'JetBrains Mono', monospace; font-size: 13px;" />
          @error('payoutAddressConfirm')<p style="margin-top: 8px; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
        </div>
      @endif

      <div>
        <label style="display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.65);">{{ mb_strtoupper(__('coin.profile.sessions_password')) }}</label>
        <input type="password" wire:model="payoutAddressPassword" autocomplete="current-password" placeholder="{{ __('coin.profile.sessions_password_placeholder') }}" style="width: 100%; box-sizing: border-box; margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-size: 14px;" />
        @error('payoutAddressPassword')<p style="margin-top: 8px; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
      </div>

      <div style="display: flex; gap: 10px; margin-top: 4px;">
        <button type="button" wire:click="closeWalletModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: transparent; color: rgba(214,238,248,0.82); font-family: inherit; font-size: 13px; cursor: pointer;">{{ __('coin.cancel') }}</button>
        @if($walletModalMode === 'disconnect')
          <button type="button" wire:click="disconnectPayoutAddress" wire:loading.attr="disabled" wire:target="disconnectPayoutAddress" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(255,120,120,0.35); background: rgba(255,120,120,0.12); color: #ffd0d0; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
            <span wire:loading.remove wire:target="disconnectPayoutAddress">{{ __('coin.profile.disconnect') }}</span>
            <span wire:loading wire:target="disconnectPayoutAddress">{{ __('coin.profile.saving_password') }}</span>
          </button>
        @else
          <button type="button" wire:click="savePayoutAddress" wire:loading.attr="disabled" wire:target="savePayoutAddress" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">
            <span wire:loading.remove wire:target="savePayoutAddress">{{ __('coin.profile.save_wallet') }}</span>
            <span wire:loading wire:target="savePayoutAddress">{{ __('coin.profile.saving_password') }}</span>
          </button>
        @endif
      </div>
    </div>
  </div>
</div>
@endif
