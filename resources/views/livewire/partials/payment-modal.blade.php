@if($paymentModal && $paymentModal !== 'topup')
@php
  $isInvestment = $paymentModal === 'investment';
  $isPlanChange = $paymentModal === 'plan_change';
  $isPayout = $paymentModal === 'payout';
  $currency = $isPayout ? $withdrawCurrency : ($wallet?->currency ?? config('coin.wallet.base_currency', 'USDT'));
  $isMockDriver = ! app(\App\Services\PlatformSettingsService::class)->usesLivePaymentGateway();
  $changingContract = $isPlanChange ? $this->changingContract : null;
  $amount = $isPlanChange
    ? number_format($this->planChangeTopUp, 2, '.', ',')
    : ($isInvestment
      ? number_format((float) $power, 0, '.', ',')
      : ($withdrawCurrency === 'BTC'
        ? rtrim(rtrim(number_format((float) $withdrawAmount, 8, '.', ','), '0'), '.')
        : number_format((float) $withdrawAmount, 2, '.', ',')));
  $planName = $this->planName;
  $payoutAddress = $isPayout ? $this->withdrawPayoutAddress : $wallet?->payout_address;
  $payoutNetworkLabel = $isPayout ? $this->withdrawPayoutNetworkLabel : $wallet?->network_label;
  $successTitle = $isPlanChange
    ? ($paymentModalStep === 'pending_approval'
      ? __('coin.payment_modal.plan_change_pending_title')
      : __('coin.payment_modal.plan_changed_title'))
    : ($isInvestment
      ? __('coin.payment_modal.plan_activated_title')
      : __('coin.payment_modal.success'));
  $successBody = $isPlanChange
    ? ($paymentModalStep === 'pending_approval'
      ? __('coin.payment_modal.plan_change_pending')
      : __('coin.payment_modal.plan_changed'))
    : ($isInvestment
      ? __('coin.payment_modal.plan_activated')
      : ($isMockDriver ? __('coin.crypto_gateway.payout_paid_success') : __('coin.payment_modal.payout_success')));
  $reviewLabel = $isPlanChange
    ? __('coin.payment_modal.plan_change')
    : ($isInvestment
      ? __('coin.payment_modal.plan_purchase')
      : __('coin.payment_modal.payout_request'));
  $reviewHint = $isPlanChange
    ? __('coin.payment_modal.plan_change_hint')
    : ($isInvestment
      ? __('coin.payment_modal.from_balance')
      : __('coin.payment_modal.to_wallet'));
  $isContractFlow = $isInvestment || $isPlanChange;
@endphp
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px); overflow-y: auto;"
  @if(! in_array($paymentModalStep, ['processing'], true)) wire:click="closePaymentModal" wire:keydown.escape.window="closePaymentModal" @endif
>
  <div
    style="width: min(100%, 420px); max-height: min(90dvh, 720px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden; overflow-y: auto;"
    wire:click.stop
  >
    <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); display: grid; place-items: center;">
          <span style="width: 12px; height: 12px; border-radius: 3px; background: #04121f;"></span>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ __('coin.payment_modal.brand') }}</div>
          <div style="font-size: 14px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.secure') }}</div>
        </div>
      </div>
      @if($paymentModalStep !== 'processing')
      <button type="button" wire:click="closePaymentModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>
      @endif
    </div>

    <div style="padding: 22px;">
      @if($paymentModalStep === 'insufficient_funds')
        <div style="padding: 18px 0 8px; text-align: center;">
          <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(255, 180, 84, 0.12); border: 1px solid rgba(255, 180, 84, 0.28); display: grid; place-items: center;">
            <span style="font-size: 26px; color: #ffb454;">!</span>
          </div>
          <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.insufficient_funds_title') }}</div>
          <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">
            {{ __('coin.payment_modal.insufficient_funds_body', ['amount' => number_format($this->planChangeTopUp, 2, '.', ',').' '.$currency]) }}
          </div>
        </div>
        <button type="button" wire:click="goToWalletTopUp" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.top_up_balance') }}</button>
        <button type="button" wire:click="closePaymentModal" style="width: 100%; margin-top: 10px; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>

      @elseif($paymentModalStep === 'review')
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
          {{ $reviewLabel }}
        </div>
        @if(! $isPlanChange || $this->planChangeTopUp > 0)
        <div style="margin-top: 10px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">
          {{ $amount }} {{ $currency }}
        </div>
        @endif
        <div style="margin-top: 6px; font-size: 13px; color: rgba(214,238,248,0.72);">
          {{ $reviewHint }}
        </div>

        <div style="margin-top: 20px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          @if($isPlanChange && $changingContract)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.change_plan_from') }}</span><span style="text-align: right;">{{ $changingContract->plan?->displayName() }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.change_plan_to') }}</span><span style="font-weight: 500; text-align: right;">{{ $planName }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.change_plan_top_up') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">@if($this->planChangeTopUp > 0){{ number_format($this->planChangeTopUp, 2, '.', ',') }} {{ $currency }}@else{{ __('coin.invest.change_plan_no_top_up') }}@endif</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.change_plan_principal_after') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ number_format($this->planChangePrincipalAfter, 2, '.', ',') }} {{ $currency }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.term') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->planTerm }}</span></div>
          @elseif($isInvestment)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.plan') }}</span><span style="font-weight: 500; text-align: right;">{{ $planName }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.invest.term') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->planTerm }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.payment_method') }}</span><span style="text-align: right;">{{ __('coin.payment_modal.available_balance') }}</span></div>
          @else
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.destination') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; text-align: right; word-break: break-all; max-width: 220px;">{{ $payoutAddress }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.network') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $payoutNetworkLabel }}</span></div>
          @if($isPayout && $withdrawCurrency === 'BTC' && $this->withdrawDebitPreview)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.withdraw_debit_preview_label') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->withdrawDebitPreview }}</span></div>
          @endif
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.network_fee') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->platformFeeLabel }}</span></div>
          @if($this->withdrawTotalDebitLabel)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.wallet.withdraw_total_debit') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->withdrawTotalDebitLabel }}</span></div>
          @endif
          @endif
          @if(! $isPlanChange || $this->planChangeTopUp > 0)
          <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ $isPayout ? __('coin.wallet.payout_amount') : __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          @endif
        </div>

        @if($isPlanChange)
        <button type="button" wire:click="confirmPlanChange" wire:loading.attr="disabled" wire:target="confirmPlanChange" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmPlanChange">{{ __('coin.payment_modal.confirm') }}</span>
          <span wire:loading wire:target="confirmPlanChange">{{ __('coin.payment_modal.confirming') }}</span>
        </button>
        @elseif($isInvestment)
        <button type="button" wire:click="confirmInvestmentPayment" wire:loading.attr="disabled" wire:target="confirmInvestmentPayment" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmInvestmentPayment">{{ __('coin.payment_modal.confirm') }}</span>
          <span wire:loading wire:target="confirmInvestmentPayment">{{ __('coin.payment_modal.confirming') }}</span>
        </button>
        @elseif($isPayout)
        <div style="margin-top: 20px;">
          <label style="display: block; font-size: 12.5px; color: rgba(214,238,248,0.78); margin-bottom: 8px;">{{ __('coin.payment_modal.payout_password') }}</label>
          <input type="password" wire:model="payoutPassword" autocomplete="current-password" placeholder="{{ __('coin.payment_modal.payout_password_placeholder') }}" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.55); color: #f0fbff; font-family: inherit; font-size: 14px;" />
          @error('payoutPassword')<p style="margin: 8px 0 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
        </div>
        <button type="button" wire:click="beginPayoutVerification" wire:loading.attr="disabled" wire:target="beginPayoutVerification" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="beginPayoutVerification">{{ __('coin.payment_modal.payout_continue') }}</span>
          <span wire:loading wire:target="beginPayoutVerification">{{ __('coin.payment_modal.confirming') }}</span>
        </button>
        @endif
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">{{ __('coin.payment_modal.demo_note') }}</p>

      @elseif($paymentModalStep === 'payout_verify' && $isPayout)
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ __('coin.payment_modal.payout_verify_step') }}</div>
        <div style="margin-top: 10px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.payout_verify_title') }}</div>
        <div style="margin-top: 8px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.75);">
          {{ __('coin.payment_modal.payout_verify_sub', ['email' => $user->email]) }}
        </div>
        <div style="margin-top: 18px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.destination') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; text-align: right; word-break: break-all; max-width: 220px;">{{ $payoutAddress }}</span></div>
        </div>
        <div style="margin-top: 20px;">
          <label style="display: block; font-size: 12.5px; color: rgba(214,238,248,0.78); margin-bottom: 8px;">{{ __('coin.auth.two_factor_code') }}</label>
          <input type="text" wire:model="payoutVerificationCode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000000" style="width: 100%; box-sizing: border-box; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.55); color: #f0fbff; font-family: 'JetBrains Mono', monospace; font-size: 18px; letter-spacing: 0.2em; text-align: center;" />
          @error('payoutVerificationCode')<p style="margin: 8px 0 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
        </div>
        <button type="button" wire:click="confirmPayoutPayment" wire:loading.attr="disabled" wire:target="confirmPayoutPayment" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmPayoutPayment">{{ __('coin.payment_modal.payout_confirm') }}</span>
          <span wire:loading wire:target="confirmPayoutPayment">{{ __('coin.payment_modal.confirming') }}</span>
        </button>
        <div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 14px;">
          <button type="button" wire:click="resendPayoutVerificationCode" wire:loading.attr="disabled" wire:target="resendPayoutVerificationCode" style="border: 0; background: transparent; color: rgba(214,238,248,0.78); font-family: inherit; font-size: 13px; cursor: pointer; text-decoration: underline; padding: 0;">{{ __('coin.auth.two_factor_resend') }}</button>
          <button type="button" wire:click="backToPayoutReview" style="border: 0; background: transparent; color: rgba(214,238,248,0.78); font-family: inherit; font-size: 13px; cursor: pointer; text-decoration: underline; padding: 0;">{{ __('coin.payment_modal.payout_verify_back') }}</button>
        </div>

      @elseif($paymentModalStep === 'payout_gateway' && $isPayout)
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ __('coin.crypto_gateway.payout_step') }}</div>
        <div style="margin-top: 10px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ __('coin.crypto_gateway.payout_gateway_title') }}</div>
        <div style="margin-top: 18px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.total') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.destination') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; text-align: right; word-break: break-all; max-width: 220px;">{{ $payoutAddress }}</span></div>
          @if($paymentModalReference)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">{{ __('coin.payment_modal.reference') }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">{{ $paymentModalReference }}</span></div>
          @endif
        </div>
        <button type="button" wire:click="confirmPayoutGatewaySimulation" wire:loading.attr="disabled" wire:target="confirmPayoutGatewaySimulation" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmPayoutGatewaySimulation">{{ __('coin.crypto_gateway.simulate_payout') }}</span>
          <span wire:loading wire:target="confirmPayoutGatewaySimulation">{{ __('coin.crypto_gateway.payout_confirming') }}</span>
        </button>
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">{{ __('coin.crypto_gateway.payout_mock_note') }}</p>

      @elseif($paymentModalStep === 'processing')
        <div style="padding: 28px 0 18px; text-align: center;">
          <div style="width: 56px; height: 56px; margin: 0 auto; border-radius: 50%; border: 3px solid rgba(150,235,250,0.14); border-top-color: oklch(0.88 0.12 192); animation: coinPaySpin 0.9s linear infinite;"></div>
          <div style="margin-top: 22px; font-size: 18px; font-weight: 600; color: #f0fbff;">{{ $isPlanChange ? __('coin.payment_modal.plan_change_processing') : ($isInvestment ? __('coin.payment_modal.plan_processing') : __('coin.payment_modal.processing')) }}</div>
          <div style="margin-top: 8px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.72);">{{ $isPlanChange ? __('coin.payment_modal.plan_change_processing_hint') : ($isInvestment ? __('coin.payment_modal.plan_processing_hint') : __('coin.payment_modal.processing_hint')) }}</div>
          <div style="margin-top: 18px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: rgba(214,238,248,0.55);">{{ __('coin.payment_modal.do_not_close') }}</div>
        </div>

      @elseif(in_array($paymentModalStep, ['success', 'pending_approval'], true))
        <div style="padding: 18px 0 8px; text-align: center;">
          <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: {{ $paymentModalStep === 'pending_approval' ? 'rgba(255, 180, 84, 0.14)' : 'rgba(120, 230, 180, 0.14)' }}; border: 1px solid {{ $paymentModalStep === 'pending_approval' ? 'rgba(255, 180, 84, 0.35)' : 'rgba(120, 230, 180, 0.35)' }}; display: grid; place-items: center;">
            <span style="font-size: 28px; color: {{ $paymentModalStep === 'pending_approval' ? '#ffb454' : 'oklch(0.86 0.14 160)' }};">{{ $paymentModalStep === 'pending_approval' ? '…' : '✓' }}</span>
          </div>
          <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ $successTitle }}</div>
          <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">
            {{ $successBody }}
          </div>
          @if($isContractFlow && $planName)
          <div style="margin-top: 16px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-size: 13px; color: rgba(214,238,248,0.82);">
            {{ __('coin.plan') }} · {{ $planName }}
          </div>
          @endif
          @if($paymentModalReference)
          <div style="margin-top: 18px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(214,238,248,0.82);">
            {{ __('coin.payment_modal.reference') }} · {{ $paymentModalReference }}
          </div>
          @endif
        </div>

        <div style="display: flex; gap: 10px; margin-top: 22px;">
          @if($isPlanChange)
          <button type="button" wire:click="finishPlanChange" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.view_investments') }}</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
          @elseif($isInvestment)
          <button type="button" wire:click="finishInvestmentPayment" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.view_investments') }}</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
          @else
          <button type="button" wire:click="closePaymentModal" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.done') }}</button>
          @endif
        </div>

      @elseif($paymentModalStep === 'error')
        <div style="padding: 18px 0 8px; text-align: center;">
          <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(255, 143, 143, 0.12); border: 1px solid rgba(255, 143, 143, 0.28); display: grid; place-items: center;">
            <span style="font-size: 26px; color: #ff8f8f;">!</span>
          </div>
          <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">{{ __('coin.payment_modal.failed') }}</div>
          <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">{{ $paymentModalError ?? __('coin.payment_modal.try_again') }}</div>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 22px;">
          <button type="button" wire:click="$set('paymentModalStep', 'review')" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.payment_modal.try_again') }}</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">{{ __('coin.close') }}</button>
        </div>
      @endif
    </div>
  </div>
</div>
<style>
  @keyframes coinPaySpin {
    to { transform: rotate(360deg); }
  }
</style>
@endif
