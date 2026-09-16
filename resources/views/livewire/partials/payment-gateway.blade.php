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
  $canDismiss = ! in_array($paymentModalStep, ['processing', 'redirect'], true);
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; overflow-y: auto;"
  @if($canDismiss && ! in_array($paymentModalStep, ['bank'], true)) wire:click="closePaymentModal" wire:keydown.escape.window="closePaymentModal" @endif
>
  @if($paymentModalStep === 'gateway')
  <div style="min-height: 100dvh; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px);">
    <div style="width: min(100%, 440px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
      <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ __('coin.bank_gateway.checkout') }}</div>
          <div style="font-size: 14px; font-weight: 600; color: #f0fbff;">{{ __('coin.bank_gateway.title') }}</div>
        </div>
        <button type="button" wire:click="closePaymentModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>
      </div>
      <div style="padding: 22px;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ __('coin.bank_gateway.merchant') }}</div>
        <div style="margin-top: 8px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ \App\Support\PlatformBrand::name() }}</div>
        <div style="margin-top: 18px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.bank_gateway.purpose') }}</span><span>{{ __('coin.bank_gateway.top_up_purpose') }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.bank_gateway.method') }}</span><span>{{ __('coin.bank_gateway.internet_bank') }}</span></div>
          <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 16px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          @if($creditPreview)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.credit_to_balance') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 14px; color: #f0fbff;">{{ $creditPreview }}</span></div>
          @endif
        </div>
        <button type="button" wire:click="proceedToTopUpBank" wire:loading.attr="disabled" wire:target="proceedToTopUpBank" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="proceedToTopUpBank">{{ __('coin.bank_gateway.continue_to_bank') }}</span>
          <span wire:loading wire:target="proceedToTopUpBank">{{ __('coin.bank_gateway.connecting') }}</span>
        </button>
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">{{ __('coin.bank_gateway.redirect_note') }}</p>
      </div>
    </div>
  </div>

  @elseif($paymentModalStep === 'redirect')
  <div style="min-height: 100dvh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #f4f7fb;">
    <div style="width: min(100%, 380px); text-align: center;">
      <div style="width: 64px; height: 64px; margin: 0 auto; border-radius: 16px; background: linear-gradient(145deg, #1f4f8a, #2563a8); display: grid; place-items: center; box-shadow: 0 12px 32px rgba(31, 79, 138, 0.25);">
        <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: #fff;">IB</span>
      </div>
      <div style="margin-top: 28px; width: 48px; height: 48px; margin-inline: auto; border-radius: 50%; border: 3px solid rgba(31, 79, 138, 0.15); border-top-color: #2563a8; animation: coinBankSpin 0.9s linear infinite;"></div>
      <div style="margin-top: 22px; font-size: 18px; font-weight: 600; color: #1a2f4a;">{{ __('coin.bank_gateway.redirecting') }}</div>
      <div style="margin-top: 8px; font-size: 14px; line-height: 1.55; color: #5a718a;">{{ __('coin.bank_gateway.redirecting_hint') }}</div>
    </div>
  </div>

  @elseif($paymentModalStep === 'bank')
  <div style="min-height: 100dvh; background: #eef3f9; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;" wire:click.stop>
    <div style="background: linear-gradient(90deg, #1f4f8a, #2563a8); color: #fff; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.15); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700;">IB</div>
        <div>
          <div style="font-size: 15px; font-weight: 600;">{{ __('coin.bank_gateway.bank_name') }}</div>
          <div style="font-size: 12px; opacity: 0.85;">{{ __('coin.bank_gateway.bank_subtitle') }}</div>
        </div>
      </div>
      <div style="font-size: 12px; opacity: 0.9;">{{ __('coin.bank_gateway.secure_session') }}</div>
    </div>

    <div style="max-width: 520px; margin: 32px auto; padding: 0 20px;">
      <div style="background: #fff; border-radius: 12px; border: 1px solid #d8e2ef; box-shadow: 0 8px 28px rgba(26, 47, 74, 0.08); overflow: hidden;">
        <div style="padding: 20px 22px; border-bottom: 1px solid #e8eef5;">
          <div style="font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; color: #6b829c;">{{ __('coin.bank_gateway.payment_confirmation') }}</div>
          <div style="margin-top: 6px; font-size: 22px; font-weight: 700; color: #1a2f4a;">{{ $amount }} {{ $currency }}</div>
        </div>
        <div style="padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; font-size: 14px; color: #334862;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: #6b829c;">{{ __('coin.bank_gateway.from_account') }}</span><span style="font-family: 'JetBrains Mono', monospace;">LV** **** **** 4821</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: #6b829c;">{{ __('coin.bank_gateway.recipient') }}</span><span style="text-align: right;">{{ \App\Support\PlatformBrand::legalName() }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: #6b829c;">{{ __('coin.bank_gateway.reference') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">COIN-TOP-{{ str_pad((string) ($user->id ?? 0), 5, '0', STR_PAD_LEFT) }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: #6b829c;">{{ __('coin.bank_gateway.description') }}</span><span style="text-align: right;">{{ __('coin.bank_gateway.top_up_purpose') }}</span></div>
        </div>
        <div style="padding: 18px 22px 22px; display: flex; gap: 10px; background: #f8fafc; border-top: 1px solid #e8eef5;">
          <button type="button" wire:click.stop="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #c8d6e6; background: #fff; color: #334862; font-family: inherit; font-size: 14px; cursor: pointer;">{{ __('coin.bank_gateway.cancel') }}</button>
          <button type="button" wire:click.stop="confirmTopUpBankPayment" wire:loading.attr="disabled" wire:target="confirmTopUpBankPayment" style="flex: 1.4; padding: 12px; border-radius: 8px; border: 0; background: linear-gradient(90deg, #1f4f8a, #2563a8); color: #fff; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer; position: relative; z-index: 1;">
            <span wire:loading.remove wire:target="confirmTopUpBankPayment">{{ __('coin.bank_gateway.confirm_payment') }}</span>
            <span wire:loading wire:target="confirmTopUpBankPayment">{{ __('coin.bank_gateway.processing_short') }}</span>
          </button>
        </div>
      </div>
      <p style="margin-top: 16px; font-size: 12px; line-height: 1.5; text-align: center; color: #6b829c;">{{ __('coin.bank_gateway.demo_bank_note') }}</p>
    </div>
  </div>

  @elseif($paymentModalStep === 'processing')
  <div style="min-height: 100dvh; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.88); backdrop-filter: blur(8px);">
    <div style="width: min(100%, 380px); text-align: center;" wire:click.stop>
      <div style="width: 56px; height: 56px; margin: 0 auto; border-radius: 50%; border: 3px solid rgba(150,235,250,0.14); border-top-color: oklch(0.88 0.12 192); animation: coinBankSpin 0.9s linear infinite;"></div>
      <div style="margin-top: 22px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ __('coin.bank_gateway.processing_title') }}</div>
      <div style="margin-top: 8px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.72);">{{ __('coin.bank_gateway.processing_hint') }}</div>
      <div style="margin-top: 18px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: rgba(214,238,248,0.55);">{{ __('coin.payment_modal.do_not_close') }}</div>
    </div>
  </div>

  @elseif($paymentModalStep === 'success')
  <div style="min-height: 100dvh; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px);">
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
  <div style="min-height: 100dvh; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px);">
    <div style="width: min(100%, 420px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;" wire:click.stop>
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
  @keyframes coinBankSpin {
    to { transform: rotate(360deg); }
  }
</style>
@endif
