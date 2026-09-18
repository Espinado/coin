@if($paymentModal === 'topup')
@php
  $currency = $depositCurrency ?: 'USDT';
  $rawAmount = (float) ($pendingTopUpAmount ?? $depositAmount);
  $amount = number_format($rawAmount, 2, '.', ',');
  $walletCurrency = $wallet?->currency ?? config('coin.wallet.base_currency', 'USDT');
  $creditPreview = null;
  if ($rawAmount > 0 && strtoupper($currency) !== strtoupper($walletCurrency)) {
      try {
          $creditPreview = app(\App\Services\ExchangeRateService::class)->previewLabel($rawAmount, $currency);
      } catch (\Throwable) {
          $creditPreview = null;
      }
  }
  $isMockDriver = config('coin.payments.driver', 'mock') === 'mock';
  $canDismiss = ! in_array($paymentModalStep, ['processing'], true);
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; overflow-y: auto;"
  @if($canDismiss && $paymentModalStep !== 'payment') wire:click="closePaymentModal" wire:keydown.escape.window="closePaymentModal" @endif
>
  @if($paymentModalStep === 'gateway')
  <div class="coin-payment-overlay__stage">
    <div style="width: min(100%, 440px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
      <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ __('coin.crypto_gateway.checkout') }}</div>
          <div style="font-size: 14px; font-weight: 600; color: #f0fbff;">{{ __('coin.crypto_gateway.title') }}</div>
        </div>
        <button type="button" wire:click="closePaymentModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>
      </div>
      <div style="padding: 22px;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ __('coin.crypto_gateway.merchant') }}</div>
        <div style="margin-top: 8px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ \App\Support\PlatformBrand::name() }}</div>
        <div style="margin-top: 18px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.crypto_gateway.purpose') }}</span><span>{{ __('coin.crypto_gateway.top_up_purpose') }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.crypto_gateway.method') }}</span><span>{{ $isMockDriver ? __('coin.crypto_gateway.method_mock') : __('coin.crypto_gateway.method_live') }}</span></div>
          <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 16px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          @if($creditPreview)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.credit_to_balance') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 14px; color: #f0fbff;">{{ $creditPreview }}</span></div>
          @endif
        </div>
        <button type="button" wire:click="proceedToTopUpPayment" wire:loading.attr="disabled" wire:target="proceedToTopUpPayment" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="proceedToTopUpPayment">{{ __('coin.crypto_gateway.continue') }}</span>
          <span wire:loading wire:target="proceedToTopUpPayment">{{ __('coin.crypto_gateway.preparing') }}</span>
        </button>
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">{{ __('coin.crypto_gateway.checkout_note') }}</p>
      </div>
    </div>
  </div>

  @elseif($paymentModalStep === 'payment')
  <div class="coin-payment-overlay__stage">
    <div style="width: min(100%, 460px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
      <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ __('coin.crypto_gateway.payment_step') }}</div>
        <div style="margin-top: 6px; font-size: 16px; font-weight: 600; color: #f0fbff;">{{ __('coin.crypto_gateway.send_exact_amount') }}</div>
      </div>
      <div style="padding: 22px;">
        <div style="padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          <div>
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(214,238,248,0.55); margin-bottom: 8px;">{{ __('coin.crypto_gateway.payment_address') }}</div>
            <div style="padding: 12px 14px; border-radius: 10px; border: 1px dashed rgba(150,235,250,0.22); background: rgba(2, 8, 16, 0.35); font-family: 'JetBrains Mono', monospace; font-size: 12px; line-height: 1.5; word-break: break-all; color: #f0fbff;">{{ $pendingPaymentAddress ?? '—' }}</div>
          </div>
          @if($pendingDepositId)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.reference') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">TOP-{{ $pendingDepositId }}</span></div>
          @endif
        </div>

        @if($isMockDriver)
        <button type="button" wire:click="confirmTopUpPayment" wire:loading.attr="disabled" wire:target="confirmTopUpPayment" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmTopUpPayment">{{ __('coin.crypto_gateway.simulate_payment') }}</span>
          <span wire:loading wire:target="confirmTopUpPayment">{{ __('coin.crypto_gateway.confirming') }}</span>
        </button>
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">{{ __('coin.crypto_gateway.mock_note') }}</p>
        @else
        <p style="margin: 22px 0 0; font-size: 12px; line-height: 1.55; text-align: center; color: rgba(214,238,248,0.72);">{{ __('coin.crypto_gateway.waiting_blockchain') }}</p>
        <button type="button" wire:click="closePaymentModal" style="width: 100%; margin-top: 14px; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
        @endif
      </div>
    </div>
  </div>

  @elseif($paymentModalStep === 'processing')
  <div class="coin-payment-overlay__stage coin-payment-overlay__stage--opaque">
    <div style="width: min(100%, 380px); text-align: center;" wire:click.stop>
      <div style="width: 56px; height: 56px; margin: 0 auto; border-radius: 50%; border: 3px solid rgba(150,235,250,0.14); border-top-color: oklch(0.88 0.12 192); animation: coinPaySpin 0.9s linear infinite;"></div>
      <div style="margin-top: 22px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ __('coin.crypto_gateway.processing_title') }}</div>
      <div style="margin-top: 8px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.72);">{{ __('coin.crypto_gateway.processing_hint') }}</div>
      <div style="margin-top: 18px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: rgba(214,238,248,0.55);">{{ __('coin.payment_modal.do_not_close') }}</div>
    </div>
  </div>

  @elseif($paymentModalStep === 'success')
  <div class="coin-payment-overlay__stage">
    <div style="width: min(100%, 420px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
      <div style="padding: 28px 22px 22px; text-align: center;">
        <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(120, 230, 180, 0.14); border: 1px solid rgba(120, 230, 180, 0.35); display: grid; place-items: center;">
          <span style="font-size: 28px; color: oklch(0.86 0.14 160);">✓</span>
        </div>
        <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.top_up_success_title') }}</div>
        <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">{{ __('coin.payment_modal.top_up_success') }}</div>
        @if($paymentModalReference)
        <div style="margin-top: 18px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(214,238,248,0.82);">
          {{ __('coin.payment_modal.reference') }} · {{ $paymentModalReference }}
        </div>
        @endif
        <button type="button" wire:click="closePaymentModal" style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.done') }}</button>
      </div>
    </div>
  </div>

  @elseif($paymentModalStep === 'error')
  <div class="coin-payment-overlay__stage">
    <div style="width: min(100%, 420px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,238,248,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
      <div style="padding: 28px 22px 22px; text-align: center;">
        <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(255, 143, 143, 0.12); border: 1px solid rgba(255, 143, 143, 0.28); display: grid; place-items: center;">
          <span style="font-size: 26px; color: #ff8f8f;">!</span>
        </div>
        <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.failed') }}</div>
        <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">{{ $paymentModalError ?? __('coin.payment_modal.try_again') }}</div>
        <div style="display: flex; gap: 10px; margin-top: 22px;">
          <button type="button" wire:click="$set('paymentModalStep', 'gateway')" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.try_again') }}</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
<style>
  @keyframes coinPaySpin {
    to { transform: rotate(360deg); }
  }
</style>
@endif
