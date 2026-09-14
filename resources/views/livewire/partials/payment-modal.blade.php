@if($paymentModal)
@php
  $currency = $wallet?->currency ?? 'USDT';
  $isInvestment = $paymentModal === 'investment';
  $isPayout = $paymentModal === 'payout';
  $amount = $isInvestment
    ? number_format((float) $power, 0, '.', ',')
    : number_format((float) $withdrawAmount, 2, '.', ',');
  $planName = $this->planName;
  $payoutAddress = $wallet?->payout_address;
@endphp
<div
  style="position: fixed; inset: 0; z-index: 120; display: grid; place-items: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px);"
  @if($paymentModalStep !== 'processing') wire:click="closePaymentModal" wire:keydown.escape.window="closePaymentModal" @endif
>
  <div
    style="width: min(100%, 420px); border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden;"
    wire:click.stop
  >
    <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); display: grid; place-items: center;">
          <span style="width: 12px; height: 12px; border-radius: 3px; background: #04121f;"></span>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">COIN PAY</div>
          <div style="font-size: 14px; font-weight: 600; color: #f0fbff;">Secure payment</div>
        </div>
      </div>
      @if($paymentModalStep !== 'processing')
      <button type="button" wire:click="closePaymentModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1;">×</button>
      @endif
    </div>

    <div style="padding: 22px;">
      @if($paymentModalStep === 'review')
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
          {{ $isInvestment ? 'PLAN PURCHASE' : 'PAYOUT REQUEST' }}
        </div>
        <div style="margin-top: 10px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">
          {{ $amount }} {{ $currency }}
        </div>
        <div style="margin-top: 6px; font-size: 13px; color: rgba(214,238,248,0.72);">
          {{ $isInvestment ? 'Investment payment from your available balance' : 'Transfer to your external wallet' }}
        </div>

        <div style="margin-top: 20px; padding: 16px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
          @if($isInvestment)
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Plan</span><span style="font-weight: 500; text-align: right;">{{ $planName }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Term</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $this->planTerm }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Payment method</span><span style="text-align: right;">Available balance</span></div>
          @else
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Destination</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; text-align: right; word-break: break-all; max-width: 220px;">{{ $payoutAddress }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Network</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">{{ $wallet?->network_label }}</span></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Network fee</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right;">0.40 {{ $currency }}</span></div>
          @endif
          <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
          <div style="display: flex; justify-content: space-between; gap: 12px;"><span style="color: rgba(214,238,248,0.72);">Total</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">{{ $amount }} {{ $currency }}</span></div>
        </div>

        @if($isInvestment)
        <button type="button" wire:click="confirmInvestmentPayment" wire:loading.attr="disabled" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmInvestmentPayment">Confirm payment</span>
          <span wire:loading wire:target="confirmInvestmentPayment">Starting…</span>
        </button>
        @else
        <button type="button" wire:click="confirmPayoutPayment" wire:loading.attr="disabled" style="width: 100%; margin-top: 22px; padding: 13px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">
          <span wire:loading.remove wire:target="confirmPayoutPayment">Confirm payment</span>
          <span wire:loading wire:target="confirmPayoutPayment">Starting…</span>
        </button>
        @endif
        <p style="margin: 14px 0 0; font-size: 11px; line-height: 1.5; text-align: center; color: rgba(214,238,248,0.55);">Demo payment gateway · funds move inside your Coin account</p>

      @elseif($paymentModalStep === 'processing')
        <div style="padding: 28px 0 18px; text-align: center;">
          <div style="width: 56px; height: 56px; margin: 0 auto; border-radius: 50%; border: 3px solid rgba(150,235,250,0.14); border-top-color: oklch(0.88 0.12 192); animation: coinPaySpin 0.9s linear infinite;"></div>
          <div style="margin-top: 22px; font-size: 18px; font-weight: 600; color: #f0fbff;">Processing payment</div>
          <div style="margin-top: 8px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.72);">Verifying transaction and updating your balance…</div>
          <div style="margin-top: 18px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; color: rgba(214,238,248,0.55);">DO NOT CLOSE THIS WINDOW</div>
        </div>

      @elseif($paymentModalStep === 'success')
        <div style="padding: 18px 0 8px; text-align: center;">
          <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(120, 230, 180, 0.14); border: 1px solid rgba(120, 230, 180, 0.35); display: grid; place-items: center;">
            <span style="font-size: 28px; color: oklch(0.86 0.14 160);">✓</span>
          </div>
          <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">Payment successful</div>
          <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">
            @if($isInvestment)
              Your investment is active. Principal is locked until maturity.
            @else
              Your payout request has been submitted and queued for processing.
            @endif
          </div>
          @if($paymentModalReference)
          <div style="margin-top: 18px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 12px; color: rgba(214,238,248,0.82);">
            Reference · {{ $paymentModalReference }}
          </div>
          @endif
        </div>

        <div style="display: flex; gap: 10px; margin-top: 22px;">
          @if($isInvestment)
          <button type="button" wire:click="finishInvestmentPayment" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">View investments</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">Close</button>
          @else
          <button type="button" wire:click="closePaymentModal" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">Done</button>
          @endif
        </div>

      @elseif($paymentModalStep === 'error')
        <div style="padding: 18px 0 8px; text-align: center;">
          <div style="width: 62px; height: 62px; margin: 0 auto; border-radius: 50%; background: rgba(255, 143, 143, 0.12); border: 1px solid rgba(255, 143, 143, 0.28); display: grid; place-items: center;">
            <span style="font-size: 26px; color: #ff8f8f;">!</span>
          </div>
          <div style="margin-top: 20px; font-size: 20px; font-weight: 600; color: #f0fbff;">Payment failed</div>
          <div style="margin-top: 8px; font-size: 13.5px; line-height: 1.55; color: rgba(214,238,248,0.75);">{{ $paymentModalError ?? 'Something went wrong. Please try again.' }}</div>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 22px;">
          <button type="button" wire:click="$set('paymentModalStep', 'review')" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">Try again</button>
          <button type="button" wire:click="closePaymentModal" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; cursor: pointer;">Close</button>
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
