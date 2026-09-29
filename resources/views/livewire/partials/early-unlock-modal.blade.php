@if($earlyUnlockContractId)
@php
  $currency = $earlyUnlockContract?->currency ?? $symbol ?? 'USDT';
  $principal = (float) ($earlyUnlockQuote['principal'] ?? 0);
  $feeAmount = (float) ($earlyUnlockQuote['fee_amount'] ?? 0);
  $creditAmount = (float) ($earlyUnlockQuote['credit_amount'] ?? 0);
  $feePercent = (float) ($earlyUnlockQuote['fee_percent'] ?? 0);
  $feeMin = (float) ($earlyUnlockQuote['fee_min'] ?? 0);
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 80; background: rgba(2, 10, 18, 0.72); backdrop-filter: blur(10px); display: grid; place-items: center; padding: 20px;"
  wire:click="closeEarlyUnlockModal"
  wire:keydown.escape.window="closeEarlyUnlockModal"
>
  <div
    style="width: min(100%, 440px); border-radius: 20px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(12, 32, 48, 0.98), rgba(6, 16, 28, 0.98)); box-shadow: 0 40px 90px -40px rgba(0,0,0,0.85); padding: 24px;"
    wire:click.stop
  >
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(255,180,84,0.85);">{{ __('coin.invest.close_early_kicker') }}</div>
        <h3 style="margin: 8px 0 0; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ __('coin.invest.close_early_title') }}</h3>
      </div>
      @if($earlyUnlockModalStep !== 'processing')
      <button type="button" wire:click="closeEarlyUnlockModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>
      @endif
    </div>

    @if($earlyUnlockModalStep === 'review')
      <p style="margin: 14px 0 0; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.72);">{{ __('coin.invest.close_early_lead') }}</p>
      <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 10px; font-size: 13.5px;">
        <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.7);">{{ __('coin.contract.principal') }}</span><span style="font-family: 'JetBrains Mono', monospace;">{{ number_format($principal, 2, '.', ',') }} {{ $currency }}</span></div>
        <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.7);">{{ __('coin.invest.close_early_fee') }}</span><span style="font-family: 'JetBrains Mono', monospace; color: #ffb454;">−{{ number_format($feeAmount, 2, '.', ',') }} {{ $currency }}</span></div>
        <div style="font-size: 11.5px; color: rgba(214,238,248,0.55);">{{ __('coin.invest.close_early_fee_hint', ['percent' => number_format($feePercent, 1), 'min' => number_format($feeMin, 2).' '.$currency]) }}</div>
        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 4px 0;"></div>
        <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.78); font-weight: 600;">{{ __('coin.invest.close_early_credit') }}</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.9 0.12 192); font-weight: 600;">{{ number_format($creditAmount, 2, '.', ',') }} {{ $currency }}</span></div>
      </div>
      <div style="margin-top: 22px; display: flex; gap: 10px;">
        <button type="button" wire:click="closeEarlyUnlockModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.cancel') }}</button>
        <button type="button" wire:click="confirmEarlyUnlock" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(255,180,84,0.5); background: linear-gradient(140deg, rgba(255,180,84,0.95), rgba(230,140,40,0.95)); color: #1a1208; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.invest.close_early_confirm') }}</button>
      </div>
    @elseif($earlyUnlockModalStep === 'processing')
      <div style="margin-top: 28px; text-align: center; color: rgba(214,238,248,0.75); font-size: 14px;">{{ __('coin.invest.close_early_processing') }}</div>
    @elseif($earlyUnlockModalStep === 'pending_approval')
      <div style="margin-top: 24px; text-align: center;">
        <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(255, 180, 84, 0.14); border: 1px solid rgba(255, 180, 84, 0.35); display: grid; place-items: center;">
          <span style="font-size: 28px; color: #ffb454;">…</span>
        </div>
        <div style="margin-top: 16px; font-size: 16px; font-weight: 600; color: #f0fbff;">{{ __('coin.invest.close_early_pending_title') }}</div>
        <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.72);">{{ __('coin.invest.close_early_pending_body') }}</div>
        @if($earlyUnlockModalReference)
        <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(214,238,248,0.65);">{{ $earlyUnlockModalReference }}</div>
        @endif
        <button type="button" wire:click="closeEarlyUnlockModal" style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.done') }}</button>
      </div>
    @elseif($earlyUnlockModalStep === 'error')
      <div style="margin-top: 24px; text-align: center;">
        <div style="font-size: 16px; font-weight: 600; color: #ff8f8f;">{{ __('coin.invest.close_early_error_title') }}</div>
        <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">{{ $earlyUnlockModalError ?? __('coin.payment_modal.try_again') }}</div>
        <div style="margin-top: 22px; display: flex; gap: 10px;">
          <button type="button" wire:click="$set('earlyUnlockModalStep', 'review')" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.try_again') }}</button>
          <button type="button" wire:click="closeEarlyUnlockModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
        </div>
      </div>
    @endif
  </div>
</div>
@endif
