<div x-data x-effect="document.documentElement.classList.toggle('coin-nav-open', @js($menuOpen)); document.documentElement.classList.toggle('coin-modal-open', @js(filled($paymentModal)))">
<div class="coin-dashboard" style="display: flex; min-height: 100vh; width: 1440px; margin: 0 auto; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif;">

  <div class="coin-nav-overlay" wire:click="closeMenu"></div>
  <aside class="coin-sidebar" style="width: 248px; flex: none; border-right: 1px solid rgba(150,235,250,0.1); background: rgba(4,16,28,0.6); padding: 22px 16px; display: flex; flex-direction: column; gap: 3px;">
    <div class="coin-sidebar-nav" style="display: flex; flex-direction: column; gap: 3px;">
    <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 11px; padding: 4px 10px 24px; color: inherit;">
      <div style="width: 28px; height: 28px; border-radius: 9px; background: linear-gradient(145deg, oklch(0.86 0.12 192), oklch(0.6 0.13 210)); display: grid; place-items: center; box-shadow: 0 8px 22px -8px oklch(0.78 0.13 192 / 0.8);">
        <div style="width: 10px; height: 10px; border-radius: 3px; background: #061423;"></div>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 600; letter-spacing: -0.015em;">Coin</div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.6);">{{ mb_strtoupper(__('coin.nav.portal')) }}</div>
      </div>
    </a>

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 0 12px 10px;">{{ mb_strtoupper(__('coin.nav.main')) }}</div>

    <button wire:click="setSection(0)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: #e6f4fa; text-align: left; cursor: pointer;">
      @if($section === 0)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: oklch(0.86 0.12 192);"></span>
      <span style="position: relative;">{{ __('coin.nav.overview') }}</span>
    </button>
    <button wire:click="setSection(1)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 1)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">{{ __('coin.nav.investment_plans') }}</span>
    </button>
    <button wire:click="setSection(2)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 2)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative; flex: 1;">{{ __('coin.nav.my_investments') }}</span>
      <span style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; padding: 2px 7px; border-radius: 6px; background: rgba(150,235,250,0.1); color: rgba(214,238,248,0.8);">{{ $this->activeContractCount }}</span>
    </button>
    <button wire:click="setSection(3)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 3)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">{{ __('coin.nav.statistics') }}</span>
    </button>
    <button wire:click="setSection(4)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 4)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">{{ __('coin.nav.wallet') }}</span>
    </button>
    <button wire:click="setSection(5)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 5)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">{{ __('coin.nav.referrals') }}</span>
    </button>

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 22px 12px 10px;">{{ mb_strtoupper(__('coin.nav.account')) }}</div>
    <button wire:click="setSection(6)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 6)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">{{ __('coin.nav.settings') }}</span>
    </button>
    <button type="button" wire:click="openSupport" wire:key="support-nav-{{ $this->unreadSupportCount }}" data-unread-support="{{ $this->unreadSupportCount }}" class="coin-nav-support {{ $this->unreadSupportCount > 0 ? 'coin-nav-support--unread' : '' }}" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: {{ $this->unreadSupportCount > 0 ? '#f0fbff' : ($section === 7 ? '#e6f4fa' : 'rgba(230,244,250,0.72)') }}; text-align: left; cursor: pointer; width: 100%; z-index: 2;">
      @if($section === 7)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@elseif($this->unreadSupportCount > 0)<span data-user-support-nav-bg style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.72 0.16 35 / 0.12); border: 1px solid oklch(0.82 0.18 35 / 0.35); pointer-events: none;"></span>@endif
      <span class="coin-nav-support-dot" style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: {{ $this->unreadSupportCount > 0 ? 'oklch(0.85 0.18 35)' : ($section === 7 ? 'oklch(0.86 0.12 192)' : 'rgba(150,235,250,0.3)') }}; {{ $this->unreadSupportCount > 0 ? 'box-shadow: 0 0 10px oklch(0.85 0.18 35 / 0.8);' : '' }}"></span>
      <span class="coin-nav-support-label" style="position: relative; flex: 1; font-weight: {{ $this->unreadSupportCount > 0 ? '600' : '400' }};">{{ __('coin.nav.live_support') }}</span>
      @if($this->unreadSupportCount > 0)
        <span class="coin-support-badge" data-user-support-nav-badge style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; min-width: 20px; text-align: center; padding: 3px 7px; border-radius: 999px; background: linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25)); color: #1a0a04; box-shadow: 0 0 16px oklch(0.82 0.2 35 / 0.55);">{{ $this->unreadSupportCount }}</span>
      @endif
    </button>
    </div>

    <div class="coin-sidebar-footer" style="margin-top: auto; flex-shrink: 0; display: flex; flex-direction: column; gap: 8px; padding-top: 12px;">
      @include('livewire.partials.sidebar-active-investments')

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: rgba(230,244,250,0.78); font-family: inherit; font-size: 13px; cursor: pointer;">{{ __('coin.nav.logout') }}</button>
      </form>
    </div>
  </aside>

  <main style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
    <header class="coin-dash-header" style="display: flex; align-items: center; gap: 20px; padding: 20px 32px; border-bottom: 1px solid rgba(150,235,250,0.1); background: rgba(4,16,28,0.4);">
      <button type="button" class="coin-burger" wire:click="toggleMenu" aria-label="{{ __('coin.nav.open_menu') }}"><span></span><span></span><span></span></button>
      <div style="min-width: 0; flex: 1;">
        <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ $this->title }}</div>
        <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $this->subtitle }}</div>
      </div>
      <div class="coin-dash-meta">
        <div class="coin-hide-mobile" style="display: flex; align-items: center; gap: 9px; padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.8);">
          <span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); box-shadow: 0 0 9px oklch(0.85 0.15 160); animation: dbPulse 2.4s infinite;"></span>
          {{ mb_strtoupper(__('coin.nav.account_active')) }}
        </div>
        <div class="coin-hide-mobile" style="padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.8);">{{ $wallet?->currency ?? 'USDT' }}</div>
        <div style="display: flex; align-items: center; gap: 10px; padding: 6px 12px 6px 6px; border-radius: 999px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04);">
          <span style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(145deg, oklch(0.7 0.13 198), oklch(0.5 0.15 285)); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #04121f;">{{ $user->avatarInitial() }}</span>
          <span class="coin-hide-mobile" style="font-size: 13px;">{{ $user->accountLabel() }}</span>
        </div>
      </div>
    </header>

    @if($actionMessage && ! filled($paymentModal))
      @php
        $feedbackStyle = match ($actionMessageTone ?? 'info') {
            'success' => 'border: 1px solid oklch(0.7 0.14 160 / 0.5); background: oklch(0.58 0.14 160 / 0.22); color: oklch(0.93 0.1 160);',
            'error' => 'border: 1px solid oklch(0.62 0.18 25 / 0.5); background: oklch(0.52 0.16 25 / 0.22); color: oklch(0.94 0.08 25);',
            default => 'border: 1px solid oklch(0.86 0.11 195 / 0.35); background: oklch(0.6 0.13 200 / 0.18); color: #eafcff;',
        };
      @endphp
      <div style="margin: 0 32px 0; padding: 12px 16px; border-radius: 10px; font-size: 13px; {{ $feedbackStyle }}">{{ $actionMessage }}</div>
    @endif

    @if($section === 0)
      <section data-screen-label="{{ __('coin.nav.overview') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.total_balance')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $this->walletCurrency }} · {{ $wallet?->usd_estimate_label }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.locked_in_investments')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ $wallet?->formattedLocked() ?? '0.00' }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.invest.active_count_line', ['count' => $this->activeContractCount]) }} · {{ $wallet?->currency ?? 'USDT' }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.active')) }}</div>
            <div style="margin-top: 14px; font-size: 24px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">{{ $primaryPlan?->name ?? '—' }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.invest.days_apr', ['days' => $primaryContract?->termDays() ?? 0, 'apr' => $primaryPlan?->formattedAnnualProfit() ?? '—']) }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">{{ mb_strtoupper(__('coin.invest.daily_profit')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: oklch(0.9 0.12 192);">{{ $user->formattedDailyReward() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.75);">{{ $wallet?->currency ?? 'USDT' }} · {{ __('coin.invest.accrues_daily') }}</div>
          </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; padding: 16px 18px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); align-items: center;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7); margin-right: 6px;">{{ mb_strtoupper(__('coin.actions.quick')) }}</span>
          <button wire:click="setSection(1)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">{{ __('coin.actions.invest') }}</button>
          <button wire:click="setSection(4)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.actions.top_up') }}</button>
          <button wire:click="setSection(4)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.actions.payout') }}</button>
          <button wire:click="setSection(2)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.nav.my_investments') }}</button>
          <button wire:click="setSection(5)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(180,180,255,0.26); background: rgba(150,140,255,0.1); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.actions.invite') }}</button>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 15px; font-weight: 600;">{{ __('coin.overview.accruals') }}</div>
                <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.overview.accruals_sub') }}</div>
              </div>
              <div style="display: flex; gap: 7px;">
                <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.7);">30D</span>
                <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.4); background: oklch(0.6 0.13 200 / 0.22); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: #f0fbff;">14D</span>
                <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.7);">24H</span>
              </div>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 8px; height: 176px; margin-top: 24px;">
              <div style="flex: 1; height: 34%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
              <div style="flex: 1; height: 46%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
              <div style="flex: 1; height: 39%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
              <div style="flex: 1; height: 58%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
              <div style="flex: 1; height: 52%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
              <div style="flex: 1; height: 67%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
              <div style="flex: 1; height: 61%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
              <div style="flex: 1; height: 74%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
              <div style="flex: 1; height: 69%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
              <div style="flex: 1; height: 83%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
              <div style="flex: 1; height: 78%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
              <div style="flex: 1; height: 91%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.18));"></div>
              <div style="flex: 1; height: 86%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.18));"></div>
              <div style="flex: 1; height: 100%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, #eafcff, oklch(0.88 0.12 192 / 0.22)); box-shadow: 0 0 26px oklch(0.88 0.12 192 / 0.45);"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);"><span>AUG 26</span><span>SEP 1</span><span>SEP 8</span></div>
          </div>

          @php $allocation = $this->planAllocation; @endphp
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.overview.portfolio_allocation') }}</div>
            <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.overview.portfolio_allocation_sub') }}</div>
            @if($allocation['items'])
            <div style="display: flex; align-items: center; gap: 22px; margin-top: 24px;">
              <div style="position: relative; width: 122px; height: 122px; flex: none; border-radius: 50%; background: conic-gradient({{ $allocation['gradient'] }});">
                <div style="position: absolute; inset: 16px; border-radius: 50%; background: #081b2c; display: grid; place-items: center;">
                  <div style="text-align: center;">
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 17px; color: #f0fbff;">{{ $allocation['utilized'] }}%</div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.1em; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.locked')) }}</div>
                  </div>
                </div>
              </div>
              <div style="display: flex; flex-direction: column; gap: 11px; font-size: 12.5px; min-width: 0;">
                @foreach($allocation['items'] as $item)
                <div style="display: flex; align-items: center; gap: 8px;"><span style="width: 8px; height: 8px; border-radius: 2px; background: {{ $item['color'] }};"></span><span style="color: rgba(214,238,248,0.78);">{{ $item['name'] }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $item['percent'] }}%</span></div>
                @endforeach
              </div>
            </div>
            @else
            <div style="margin-top: 24px; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.overview.no_allocation') }}</div>
            @endif
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.invest.active_investments') }}</span>
              <button wire:click="setSection(2)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">{{ mb_strtoupper(__('coin.actions.all_investments')) }}</button>
            </div>
            <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 14px;">
              @foreach($activeContracts as $contract)
              <div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 13px;"><span>{{ $contract->plan?->displayName() }} · {{ $contract->formattedPrincipal() }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $contract->computedProgressPercent() }}%</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->computedProgressPercent() }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
              </div>
              @endforeach
              <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: rgba(214,238,248,0.7); padding-top: 4px;"><span>{{ __('coin.overview.next_accrual') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">00:05 UTC</span></div>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.nav.wallet') }}</span>
              <button wire:click="setSection(4)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">{{ mb_strtoupper(__('coin.actions.open')) }}</button>
            </div>
            <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 13px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.available') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedAvailable() }} {{ $this->walletCurrency }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.pending_settlement') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedPending() }} {{ $this->walletCurrency }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.payout_address') }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.8);">{{ $wallet?->payout_address }}</span></div>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 20px;">
              <button wire:click="setSection(4)" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: oklch(0.6 0.13 200 / 0.22); color: #eafcff; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.actions.top_up') }}</button>
              <button wire:click="setSection(4)" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.actions.payout') }}</button>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(170deg, rgba(120,110,220,0.13), rgba(150,235,250,0.02));">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.nav.referrals') }}</span>
              <button wire:click="setSection(5)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">{{ mb_strtoupper(__('coin.actions.open')) }}</button>
            </div>
            <div style="margin-top: 18px; display: flex; align-items: baseline; gap: 10px;">
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 28px; color: #f0fbff;">{{ $referral?->invited_count ?? 0 }}</span>
              <span style="font-size: 12.5px; color: rgba(214,238,248,0.72);">{{ __('coin.overview.invited') }}</span>
            </div>
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.overview.referral_rewards') }}</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">{{ $referral?->formattedTotalRewards() }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.overview.commission_share') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $referral?->commissionLabel() }}</span></div>
            </div>
            <button wire:click="setSection(5)" style="width: 100%; margin-top: 20px; padding: 10px; border-radius: 10px; border: 1px solid rgba(180,180,255,0.3); background: rgba(150,140,255,0.12); color: #eafcff; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">{{ __('coin.overview.invite_friends') }}</button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.overview.recent_activity') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.overview.last_5_entries')) }}</span>
            </div>
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>{{ mb_strtoupper(__('coin.table.time')) }}</span><span>{{ mb_strtoupper(__('coin.table.type')) }}</span><span>{{ mb_strtoupper(__('coin.table.source')) }}</span><span style="text-align: right;">{{ mb_strtoupper(__('coin.table.amount')) }}</span></div>
            @foreach($transactions->take(5) as $transaction)
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;"><span style="color: rgba(214,238,248,0.78);">{{ $transaction->formattedOccurredAt() }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->displayType() }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->displaySource() }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span></div>
            @endforeach
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.overview.plan_breakdown') }}</div>
            <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.overview.plan_breakdown_sub') }}</div>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>Frankfurt · FRA-02</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.86 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>{{ mb_strtoupper(__('coin.overview.online')) }}</span></div>
                <div style="margin-top: 11px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level1BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
                <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.overview.locked_usdt', ['amount' => '768'])) }}</div>
              </div>
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>Ashburn · IAD-01</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.86 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>{{ mb_strtoupper(__('coin.overview.online')) }}</span></div>
                <div style="margin-top: 11px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level2BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.72 0.11 215);"></div></div>
                <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.overview.locked_usdt', ['amount' => '432'])) }}</div>
              </div>
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(180,180,255,0.16); background: rgba(150,140,255,0.07);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>São Paulo · GRU-01</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.88 0.15 90);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>{{ mb_strtoupper(__('coin.overview.expanding')) }}</span></div>
                <div style="margin-top: 11px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.overview.capacity_soon')) }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif

    @if($section === 1)
      <section data-screen-label="{{ __('coin.nav.investment_plans') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          @foreach($plans as $plan)
            @include('livewire.partials.plan-card', ['plan' => $plan, 'primaryPlan' => $primaryPlan, 'selectedPlanId' => $selectedPlanId])
          @endforeach
        </div>
        <div id="coin-plan-calculator" style="padding: 26px 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 340px); gap: 40px; align-items: center; scroll-margin-top: 96px;">
          <div>
            <div style="display: flex; align-items: baseline; gap: 12px;">
              <span style="font-size: 17px; font-weight: 600;">{{ __('coin.invest.calculator') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ $this->calculatorTermLabel }}</span>
            </div>
            <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 13.5px; color: rgba(214,238,248,0.74);">{{ __('coin.invest.investment_amount') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">{{ $this->powerLabel }} <span style="font-size: 12px; color: rgba(214,238,248,0.7);">USDT</span></span>
            </div>
            @php
              $calcMin = $this->calculatorMin;
              $calcMax = $this->calculatorMax;
              $calcMid = (int) round(($calcMin + $calcMax) / 2);
              $calcUpperMid = (int) round($calcMin + (($calcMax - $calcMin) * 0.66));
            @endphp
            <input type="range" min="{{ $calcMin }}" max="{{ $calcMax }}" step="{{ $this->calculatorStep }}" wire:model.live="power" wire:key="calc-slider-{{ $selectedPlanId }}" style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;" />
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);">
              <span>{{ number_format($calcMin, 0, '.', ' ') }}</span>
              <span>{{ number_format($calcMid, 0, '.', ' ') }}</span>
              <span>{{ number_format($calcUpperMid, 0, '.', ' ') }}</span>
              <span>{{ number_format($calcMax, 0, '.', ' ') }}</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 26px;">
              <div style="padding: 16px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.invest.per_day')) }}</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ $this->daily }}</div>
              </div>
              <div style="padding: 16px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.invest.per_month')) }}</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ $this->monthly }}</div>
              </div>
              <div style="padding: 16px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.3); background: oklch(0.6 0.13 200 / 0.18);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.75);">{{ mb_strtoupper(__('coin.invest.per_year')) }}</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.9 0.12 192);">{{ $this->yearly }}</div>
              </div>
            </div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.14); background: linear-gradient(170deg, rgba(20,55,80,0.7), rgba(6,20,35,0.85));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.selected_plan')) }}</div>
            <div style="margin-top: 12px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em;">{{ $this->planName }}</div>
            <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.invest.min_investment') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planCompute }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.invest.term') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planTerm }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.invest.infrastructure') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planInfra }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.invest.estimated_price') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planPrice }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.invest.currency') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->currency ?? 'USDT' }}</span></div>
            </div>
            <button type="button" wire:click="openInvestmentPaymentModal" wire:loading.attr="disabled" wire:target="openInvestmentPaymentModal" style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">
              <span wire:loading.remove wire:target="openInvestmentPaymentModal">{{ __('coin.actions.invest') }}</span>
              <span wire:loading wire:target="openInvestmentPaymentModal">{{ __('coin.payment_modal.confirming') }}</span>
            </button>
            @error('purchase')<p style="margin: 12px 0 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
            <p style="margin: 16px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.invest.estimates_note') }}</p>
          </div>
        </div>
      </section>
    @endif

    @if($section === 2)
      <section data-screen-label="{{ __('coin.nav.my_investments') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.active_investments')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->activeContractCount }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.total_locked')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->totalAllocatedTflops }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.lifetime_profit')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: oklch(0.9 0.12 192);">{{ $this->lifetimeProfit }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.invest.lifetime_profit_hint') }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.next_expiry')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->nextExpiryLabel }}</div>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
          @foreach($activeContracts as $contract)
            @include('livewire.partials.contract-active-card', ['contract' => $contract, 'primaryContract' => $primaryContract])
          @endforeach

          @if($completedContracts->isNotEmpty())
          <div style="margin-top: 6px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.invest.archive_title')) }}</div>
          @endif
          @foreach($completedContracts as $contract)
            @include('livewire.partials.contract-completed-card', ['contract' => $contract])
          @endforeach
        </div>
      </section>
    @endif

    @if($section === 3)
      <section data-screen-label="{{ __('coin.nav.statistics') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <button wire:click="setPeriod(0)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 0)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">{{ __('coin.stats.per_day') }}</span>
          </button>
          <button wire:click="setPeriod(1)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 1)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">{{ __('coin.stats.per_week') }}</span>
          </button>
          <button wire:click="setPeriod(2)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 2)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">{{ __('coin.stats.per_month') }}</span>
          </button>
          <div style="flex: 1;"></div>
          <button style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">{{ __('coin.stats.export_csv') }}</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.stats.profit')) }} {{ $this->periodLabel }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->periodTotal }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ $this->walletCurrency }} · {{ __('coin.stats.accrued') }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.stats.avg_daily')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->formattedDailyReward() }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.accrues_daily_hint') }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.stats.availability')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->availability_label }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.availability_hint') }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.stats.load')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->load_label }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.load_hint') }}</div>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="display: flex; align-items: baseline; justify-content: space-between;">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.profit_trend') }}</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ $this->walletCurrency }} · {{ $this->periodLabel }}</span>
          </div>
          <div style="display: flex; align-items: flex-end; gap: 6px; height: 210px; margin-top: 26px;">
            <div style="flex: 1; height: 28%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.75), oklch(0.72 0.11 210 / 0.1));"></div>
            <div style="flex: 1; height: 36%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.75), oklch(0.72 0.11 210 / 0.1));"></div>
            <div style="flex: 1; height: 31%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.75), oklch(0.72 0.11 210 / 0.1));"></div>
            <div style="flex: 1; height: 44%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.75), oklch(0.72 0.11 210 / 0.1));"></div>
            <div style="flex: 1; height: 39%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.74 0.11 206 / 0.8), oklch(0.74 0.11 206 / 0.1));"></div>
            <div style="flex: 1; height: 52%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.74 0.11 206 / 0.8), oklch(0.74 0.11 206 / 0.1));"></div>
            <div style="flex: 1; height: 47%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.74 0.11 206 / 0.8), oklch(0.74 0.11 206 / 0.1));"></div>
            <div style="flex: 1; height: 61%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.78 0.12 200 / 0.85), oklch(0.78 0.12 200 / 0.12));"></div>
            <div style="flex: 1; height: 56%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.78 0.12 200 / 0.85), oklch(0.78 0.12 200 / 0.12));"></div>
            <div style="flex: 1; height: 68%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.8 0.12 198 / 0.9), oklch(0.8 0.12 198 / 0.12));"></div>
            <div style="flex: 1; height: 63%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.8 0.12 198 / 0.9), oklch(0.8 0.12 198 / 0.12));"></div>
            <div style="flex: 1; height: 74%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.14));"></div>
            <div style="flex: 1; height: 71%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.14));"></div>
            <div style="flex: 1; height: 82%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.16));"></div>
            <div style="flex: 1; height: 78%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.16));"></div>
            <div style="flex: 1; height: 88%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.18));"></div>
            <div style="flex: 1; height: 84%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.18));"></div>
            <div style="flex: 1; height: 94%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.9 0.12 192), oklch(0.9 0.12 192 / 0.2));"></div>
            <div style="flex: 1; height: 89%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, oklch(0.9 0.12 192), oklch(0.9 0.12 192 / 0.2));"></div>
            <div style="flex: 1; height: 100%; border-radius: 4px 4px 2px 2px; background: linear-gradient(180deg, #eafcff, oklch(0.88 0.12 192 / 0.22)); box-shadow: 0 0 26px oklch(0.88 0.12 192 / 0.45);"></div>
          </div>
          <div style="display: flex; justify-content: space-between; margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);"><span>W1</span><span>W5</span><span>W10</span><span>W15</span><span>W20</span></div>
        </div>

        @php $statsAllocation = $this->planAllocation; @endphp
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.by_plan') }}</span>
            @if($statsAllocation['items'])
            <div style="display: flex; height: 10px; border-radius: 6px; overflow: hidden; margin-top: 20px;">
              @foreach($statsAllocation['items'] as $item)
              <div style="width: {{ max($item['percent'], 1) }}%; background: {{ $item['color'] }};"></div>
              @endforeach
            </div>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              @foreach($statsAllocation['items'] as $item)
              <div style="display: flex; justify-content: space-between;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(214,238,248,0.78);"><span style="width: 8px; height: 8px; border-radius: 2px; background: {{ $item['color'] }};"></span>{{ $item['name'] }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $item['percent'] }}%</span></div>
              @endforeach
            </div>
            @else
            <div style="margin-top: 20px; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.stats.no_chart') }}</div>
            @endif
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.investment_breakdown') }}</span>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 16px;">
              @forelse($activeContracts as $contract)
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">{{ $contract->plan?->displayName() }} · {{ $contract->code }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $contract->formattedPrincipal() }}</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->computedProgressPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
              </div>
              @empty
              <div style="font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.stats.no_active') }}</div>
              @endforelse
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.profit_history') }}</span>
            <div style="margin-top: 18px; display: flex; flex-direction: column;">
              @forelse($profitTransactions->take(5) as $profitTx)
              <div style="display: flex; justify-content: space-between; padding: 11px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">{{ $profitTx->formattedOccurredAt() }} · {{ $profitTx->displayType() }}</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">{{ $profitTx->amount_label }}</span></div>
              @empty
              <div style="padding: 11px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.stats.no_profit_yet') }}</div>
              @endforelse
            </div>
            <a href="{{ route('dashboard.profit-history') }}" wire:navigate style="display: block; width: 100%; margin-top: 18px; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; text-align: center; text-decoration: none; cursor: pointer;">{{ __('coin.stats.full_history') }}</a>
          </div>
        </div>
      </section>
    @endif

    @if($section === 4)
      <section data-screen-label="{{ __('coin.nav.wallet') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">{{ mb_strtoupper(__('coin.wallet.total_balance')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.75);">{{ $wallet?->currency ?? 'USDT' }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.available')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedAvailable() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.available_hint') }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.locked')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedLocked() ?? '0.00' }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.locked_principal_hint') }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.pending_payout')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedPending() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $wallet?->pending_note }}</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.1fr); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.top_up_title') }}</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.top_up_sub') }}</div>
            <div style="margin-top: 20px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.wallet.deposit_currency')) }}</div>
            <div style="margin-top: 9px; display: flex; gap: 8px; flex-wrap: wrap;">
              @foreach($this->depositCurrencies as $currencyOption)
              <button type="button" wire:click="$set('depositCurrency', '{{ $currencyOption }}')" style="padding: 8px 12px; border-radius: 8px; border: 1px solid {{ $depositCurrency === $currencyOption ? 'oklch(0.86 0.11 195 / 0.55)' : 'rgba(150,235,250,0.16)' }}; background: {{ $depositCurrency === $currencyOption ? 'oklch(0.6 0.13 200 / 0.22)' : 'transparent' }}; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.88); cursor: pointer;">{{ $currencyOption }}</button>
              @endforeach
            </div>
            <div style="margin-top: 16px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.amount')) }}</div>
            <div style="margin-top: 9px; padding: 13px 15px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.16); background: rgba(4,16,28,0.6); display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 15px;">
              <input type="text" inputmode="decimal" wire:model="depositAmount" wire:key="deposit-amount-{{ $depositAmount }}" placeholder="0.00" autocomplete="off" style="flex:1;border:0;background:transparent;color:#f0fbff;outline:none;font-family:inherit;font-size:15px;" />
              <span style="color: rgba(214,238,248,0.78);">{{ $depositCurrency }}</span>
            </div>
            @error('depositAmount')<p style="margin-top:8px;font-size:12px;color:#ff8f8f;">{{ $message }}</p>@enderror
            <div style="display: flex; gap: 7px; margin-top: 12px;">
              <button type="button" wire:click="setDepositPreset(100)" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); background: transparent; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78); cursor: pointer;">100</button>
              <button type="button" wire:click="setDepositPreset(500)" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); background: transparent; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78); cursor: pointer;">500</button>
              <button type="button" wire:click="setDepositPreset(1000)" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); background: transparent; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78); cursor: pointer;">1 000</button>
            </div>
            <button type="button" wire:click="openTopUpPaymentModal" wire:loading.attr="disabled" wire:target="openTopUpPaymentModal" style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">
              <span wire:loading.remove wire:target="openTopUpPaymentModal">{{ __('coin.wallet.add_funds') }}</span>
              <span wire:loading wire:target="openTopUpPaymentModal">{{ __('coin.payment_modal.confirming') }}</span>
            </button>
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.payout_title') }}</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.payout_sub') }}</div>
            <div style="margin-top: 20px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.amount')) }}</div>
            <div style="margin-top: 9px; padding: 13px 15px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.16); background: rgba(4,16,28,0.6); display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 15px; gap: 12px;">
              <input type="text" inputmode="decimal" wire:model="withdrawAmount" wire:key="withdraw-amount-{{ $withdrawAmount }}" placeholder="0.00" autocomplete="off" style="flex:1;border:0;background:transparent;color:#f0fbff;outline:none;font-family:inherit;font-size:15px;" />
              <button type="button" wire:click="setWithdrawMax" wire:loading.attr="disabled" wire:target="setWithdrawMax" style="flex:none;border:0;background:transparent;color:oklch(0.88 0.11 195);font-family:inherit;font-size:13px;cursor:pointer;padding:0 4px;">{{ __('coin.wallet.max') }}</button>
            </div>
            @error('withdrawAmount')<p style="margin-top:8px;font-size:12px;color:#ff8f8f;">{{ $message }}</p>@enderror
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 11px; font-size: 12.5px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.network_fee') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->networkFeeLabel }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.processing_time') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~2 h</span></div>
            </div>
            <button type="button" wire:click="openPayoutPaymentModal" style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">{{ __('coin.wallet.request_payout') }}</button>
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.payout_details') }}</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.payout_details_sub') }}</div>
            <div style="margin-top: 20px; padding: 14px 16px; border-radius: 12px; border: 1px dashed rgba(150,235,250,0.22); background: rgba(150,235,250,0.03);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.wallet.primary_wallet')) }}</div>
              <div style="margin-top: 9px; font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #eafcff; word-break: break-all;">{{ $wallet?->payout_address }}</div>
              <div style="margin-top: 9px; display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>{{ mb_strtoupper(__('coin.wallet.confirmed')) }}</div>
            </div>
            <div style="margin-top: 14px; display: flex; flex-direction: column; gap: 11px; font-size: 12.5px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.network') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->network_label }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.min_payout') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->min_withdrawal_label }} {{ $this->walletCurrency }}</span></div>
            </div>
            <button wire:click="setSection(6)" style="width: 100%; margin-top: 20px; padding: 11px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">{{ __('coin.wallet.manage_addresses') }}</button>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.transactions') }}</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ __('coin.stats.total_entries', ['count' => $walletTransactions->total()]) }}</span>
          </div>
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>{{ mb_strtoupper(__('coin.table.date')) }}</span><span>{{ mb_strtoupper(__('coin.table.type')) }}</span><span>{{ mb_strtoupper(__('coin.table.destination')) }}</span><span>{{ mb_strtoupper(__('coin.table.status')) }}</span><span style="text-align: right;">{{ mb_strtoupper(__('coin.table.amount')) }}</span></div>
          @forelse($walletTransactions as $transaction)
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last || $walletTransactions->hasPages()) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;"><span style="color: rgba(214,238,248,0.78);">{{ $transaction->formattedOccurredAt() }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->displayType() }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.72);">{{ $transaction->displaySource() }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: {{ $transaction->statusColor() }};">{{ $transaction->displayStatus() }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span></div>
          @empty
          <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.admin.no_transactions') }}</div>
          @endforelse
          @if($walletTransactions->hasPages())
            @include('livewire.partials.coin-pagination', ['paginator' => $walletTransactions])
          @endif
        </div>
      </section>
    @endif

    @if($section === 5)
      <section data-screen-label="{{ __('coin.nav.referrals') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(180,180,255,0.18); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.12));">
          <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ __('coin.referrals.hero_title') }}</div>
          <p style="margin: 10px 0 0; max-width: 620px; font-size: 14px; line-height: 1.6; color: rgba(214,238,248,0.75);">{{ __('coin.referrals.hero_sub') }}</p>
          <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px; flex-wrap: wrap;">
            <div id="referral-share-url" style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.3); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13.5px; color: #eafcff;">{{ $referral?->shareUrl() }}</div>
            <button type="button" wire:click="copyReferralLink" style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.referrals.copy_link') }}</button>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <input type="email" wire:model="referralInviteEmail" placeholder="{{ __('coin.referrals.invite_email_placeholder') }}" style="min-width: 220px; padding: 13px 16px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(4,16,28,0.55); color: #eafcff; font-family: inherit; font-size: 13.5px; outline: none;" />
              <button type="button" wire:click="sendReferralInvite" wire:loading.attr="disabled" wire:target="sendReferralInvite" style="padding: 13px 20px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">
                <span wire:loading.remove wire:target="sendReferralInvite">{{ __('coin.referrals.invite_email') }}</span>
                <span wire:loading wire:target="sendReferralInvite">{{ __('coin.referrals.invite_sending') }}</span>
              </button>
            </div>
            @error('referralInviteEmail')<p style="width: 100%; margin: 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.invited')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->invited_count ?? 0 }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.active_investments')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->referralInvestmentsCount() ?? 0 }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.referrals.referral_investments_hint') }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.referral_rewards')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: oklch(0.9 0.12 192);">{{ $referral?->formattedRewardsBalance() }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.commission_share')) }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->commissionLabel() }}</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.referrals.accrual_history') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ mb_strtoupper(__('coin.table.last_5')) }}</span>
            </div>
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>{{ mb_strtoupper(__('coin.table.user')) }}</span><span>{{ mb_strtoupper(__('coin.table.plan')) }}</span><span>{{ mb_strtoupper(__('coin.table.purchase')) }}</span><span style="text-align: right;">{{ mb_strtoupper(__('coin.table.commission')) }}</span></div>
            @forelse($referralCommissions as $commission)
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px;"><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $commission->referralLabel() }}</span><span style="color: rgba(214,238,248,0.78);">{{ $commission->planName() }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ number_format((float) $commission->purchase_amount, 0, '.', ',') }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: oklch(0.88 0.12 192);">{{ $commission->formattedCommission() }}</span></div>
            @empty
            @foreach($referralAccruals as $accrual)
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px;"><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $accrual->user_label }}</span><span style="color: rgba(214,238,248,0.78);">{{ $accrual->plan_name }}</span><span style="color: rgba(214,238,248,0.78);">—</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: oklch(0.88 0.12 192);">{{ $accrual->amount_label }}</span></div>
            @endforeach
            @if($referralCommissions->isEmpty() && $referralAccruals->isEmpty())
            <div style="padding: 13px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.referrals.no_commissions') }}</div>
            @endif
            @endforelse
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.referrals.network') }}</span>
            <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 18px;">
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">{{ __('coin.referrals.direct_referrals') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ __('coin.referrals.users_count', ['count' => $referral?->level1_users]) }}</span></div>
                <div style="margin-top: 9px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 100%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
                <div style="margin-top: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.referrals.percent_on_plan', ['percent' => $referral?->level1_percent])) }}</div>
              </div>
            </div>
            <p style="margin: 22px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.referrals.one_level_note') }}</p>
          </div>
        </div>
      </section>
    @endif

    @if($section === 6)
      @include('livewire.partials.settings-section')
    @endif

    @if($section === 7)
      @include('livewire.partials.support-section')
    @endif
  </main>
</div>

@include('livewire.partials.payment-gateway')
@include('livewire.partials.payment-modal')

</div>

@script
<script>
  $wire.on('support-thread-scroll', () => {
    requestAnimationFrame(() => {
      window.scrollSupportThreadToBottom?.('smooth');
    });
  });

  $wire.on('support-append-message', (payload) => {
    const message = payload?.message ?? payload;
    window.appendSupportMessage?.(message);
  });

  $wire.on('support-message-sent', (payload) => {
    window.showSupportToast?.(payload?.message ?? @js(__('coin.support.message_sent')));
  });

  $wire.on('support-message-received', (payload) => {
    const incoming = window.readLivewireEventPayload?.(payload, 'incomingMessage')
      ?? window.readLivewireEventPayload?.(payload);

    if (incoming?.id || incoming?.body) {
      window.showIncomingMessageToast?.(incoming, @js(__('coin.support.new_message')));
    } else {
      window.showSupportToast?.(@js(__('coin.support.new_message')), 'incoming');
    }
  });

  $wire.on('support-unread-updated', (payload) => {
    const count = window.readLivewireEventPayload?.(payload, 'count');

    if (count === undefined || count === null) {
      return;
    }

    window.updateUserSupportNavBadge?.(Number(count), { force: true });
  });

  $wire.watch('selectedTicketId', () => {
    if ($wire.section === 7) {
      requestAnimationFrame(() => window.scrollSupportThreadToBottom?.('auto'));
    }
  });
</script>
@endscript