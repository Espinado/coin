<div x-data x-effect="document.documentElement.classList.toggle('coin-nav-open', @js($menuOpen)); document.documentElement.classList.toggle('coin-modal-open', @js(filled($paymentModal) || filled($contractDetailsId) || $sessionsModalOpen || $walletModalOpen))">
<div class="coin-shell">
  <aside class="coin-sidebar">
    <div class="coin-sidebar-nav">
    <a href="{{ route('home') }}" class="coin-sidebar-brand">
      <x-brand-logo variant="horizontal" fluid :max-height="52" class="coin-sidebar-brand__logo" />
      <div class="coin-sidebar-brand__tagline">{{ mb_strtoupper(__('coin.nav.portal')) }}</div>
    </a>

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 0 12px 10px;">{{ mb_strtoupper(__('coin.nav.main')) }}</div>

    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 0, 'label' => __('coin.nav.overview'), 'currentSection' => $section])
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 1, 'label' => __('coin.nav.investment_plans'), 'currentSection' => $section])
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 2, 'label' => __('coin.nav.my_investments'), 'currentSection' => $section, 'badge' => $this->activeContractCount])
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 3, 'label' => __('coin.nav.statistics'), 'currentSection' => $section])
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 4, 'label' => __('coin.nav.wallet'), 'currentSection' => $section])
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 5, 'label' => __('coin.nav.referrals'), 'currentSection' => $section])

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 22px 12px 10px;">{{ mb_strtoupper(__('coin.nav.account')) }}</div>
    @include('livewire.partials.dashboard-nav-item', ['sectionId' => 6, 'label' => __('coin.nav.settings'), 'currentSection' => $section])
    <button type="button" wire:click="setSection(8)" wire:key="notifications-nav-{{ $this->unreadNotificationsCount }}" data-unread-notifications="{{ $this->unreadNotificationsCount }}" class="coin-nav-item coin-nav-notifications {{ $section === 8 ? 'coin-nav-item--active' : '' }} {{ $this->unreadNotificationsCount > 0 ? 'coin-nav-notifications--unread' : '' }}">
      @if($section === 8)<span class="coin-nav-item__bg" aria-hidden="true"></span>@elseif($this->unreadNotificationsCount > 0)<span data-user-notifications-nav-bg class="coin-nav-notifications__unread-bg" aria-hidden="true"></span>@endif
      <span class="coin-nav-notifications-dot" style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: {{ $this->unreadNotificationsCount > 0 ? 'oklch(0.86 0.12 192)' : ($section === 8 ? 'oklch(0.86 0.12 192)' : 'rgba(150,235,250,0.3)') }}; {{ $this->unreadNotificationsCount > 0 ? 'box-shadow: 0 0 10px oklch(0.86 0.12 192 / 0.8);' : '' }}"></span>
      <span class="coin-nav-notifications-label" style="position: relative; flex: 1; font-weight: {{ $this->unreadNotificationsCount > 0 ? '600' : '400' }};">{{ __('coin.nav.notifications') }}</span>
      @if($this->unreadNotificationsCount > 0)
        <span class="coin-notifications-badge" data-user-notifications-nav-badge style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; min-width: 20px; text-align: center; padding: 3px 7px; border-radius: 999px; background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; box-shadow: 0 0 16px oklch(0.86 0.12 192 / 0.45);">{{ $this->unreadNotificationsCount }}</span>
      @endif
    </button>
    <button type="button" wire:click="openSupport" wire:key="support-nav-{{ $this->unreadSupportCount }}" data-unread-support="{{ $this->unreadSupportCount }}" class="coin-nav-item coin-nav-support {{ $section === 7 ? 'coin-nav-item--active' : '' }} {{ $this->unreadSupportCount > 0 ? 'coin-nav-support--unread' : '' }}">
      @if($section === 7)<span class="coin-nav-item__bg" aria-hidden="true"></span>@elseif($this->unreadSupportCount > 0)<span data-user-support-nav-bg class="coin-nav-support__unread-bg" aria-hidden="true"></span>@endif
      <span class="coin-nav-support-dot" style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: {{ $this->unreadSupportCount > 0 ? 'oklch(0.85 0.18 35)' : ($section === 7 ? 'oklch(0.86 0.12 192)' : 'rgba(150,235,250,0.3)') }}; {{ $this->unreadSupportCount > 0 ? 'box-shadow: 0 0 10px oklch(0.85 0.18 35 / 0.8);' : '' }}"></span>
      <span class="coin-nav-support-label" style="position: relative; flex: 1; font-weight: {{ $this->unreadSupportCount > 0 ? '600' : '400' }};">{{ __('coin.nav.live_support') }}</span>
      @if($this->unreadSupportCount > 0)
        <span class="coin-support-badge" data-user-support-nav-badge style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; min-width: 20px; text-align: center; padding: 3px 7px; border-radius: 999px; background: linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25)); color: #1a0a04; box-shadow: 0 0 16px oklch(0.82 0.2 35 / 0.55);">{{ $this->unreadSupportCount }}</span>
      @endif
    </button>

    <form method="POST" action="{{ route('logout') }}" class="coin-sidebar-logout">
      @csrf
      <button type="submit" class="coin-nav-item coin-nav-item--logout">
        <span class="coin-nav-dot"></span>
        <span>{{ __('coin.nav.logout') }}</span>
      </button>
    </form>
    </div>

    <div class="coin-sidebar-footer">
      @include('livewire.partials.sidebar-active-investments')
    </div>
  </aside>

  <div class="coin-dashboard">
  <div class="coin-nav-overlay" wire:click="closeMenu"></div>
  <main class="coin-main">
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

    @include('livewire.partials.action-feedback-toast')

    @if($section === 0)
      <section data-screen-label="{{ __('coin.nav.overview') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.total_balance')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.locked_in_investments')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ $wallet?->formattedLocked() ?? \App\Support\MoneyFormat::zero() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.invest.active_count_line', ['count' => $this->activeContractCount]) }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">{{ mb_strtoupper(__('coin.invest.daily_profit')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: oklch(0.9 0.12 192);">{{ $user->formattedDailyReward() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.75);">{{ __('coin.invest.accrues_daily') }}</div>
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
                @foreach([2 => '30D', 1 => '14D', 0 => '24H'] as $chartPeriod => $chartLabel)
                <button type="button" wire:click="setAccrualChartPeriod({{ $chartPeriod }})" style="position: relative; padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); background: transparent; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.7); cursor: pointer;">
                  @if($accrualChartPeriod === $chartPeriod)<span style="position: absolute; inset: -1px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.4); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
                  <span style="position: relative; color: {{ $accrualChartPeriod === $chartPeriod ? '#f0fbff' : 'rgba(214,238,248,0.7)' }};">{{ $chartLabel }}</span>
                </button>
                @endforeach
              </div>
            </div>
            @php $accrualsChart = $this->accrualsChart; @endphp
            @if($accrualsChart['hasData'])
            <div style="margin-top: 24px;">
              <x-profit-line-chart :chart="$accrualsChart" :height="176" area-id="overviewAccrualsArea" line-id="overviewAccrualsLine" />
            </div>
            @else
            <div style="margin-top: 24px; padding: 40px 16px; text-align: center; font-size: 13px; color: rgba(214,238,248,0.66);">{{ __('coin.stats.no_profit_yet') }}</div>
            @endif
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
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.available') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedAvailable() }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.pending_settlement') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedPending() }}</span></div>
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
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 28px; color: #f0fbff;">{{ $referral?->invitationsCount() ?? 0 }}</span>
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

          @include('livewire.partials.overview-plan-breakdown')
        </div>
      </section>
    @endif

    @if($section === 1)
      <section data-screen-label="{{ __('coin.nav.investment_plans') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 24px;">
        @if($changingContract = $this->changingContract)
        <div style="padding: 18px 22px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.28); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.14), rgba(150,235,250,0.03)); display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
          <div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195);">{{ mb_strtoupper(__('coin.invest.change_plan_title')) }}</div>
            <div style="margin-top: 8px; font-size: 15px; font-weight: 600; color: #f0fbff;">{{ $changingContract->plan?->displayName() }} · {{ $changingContract->formattedPrincipal() }}</div>
            <div style="margin-top: 6px; max-width: 640px; font-size: 13px; line-height: 1.55; color: rgba(214,238,248,0.74);">{{ __('coin.invest.change_plan_sub', ['code' => $changingContract->code]) }}</div>
          </div>
          <button type="button" wire:click="cancelChangePlan" style="padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">{{ __('coin.invest.change_plan_cancel') }}</button>
        </div>
        @endif
        @php
          $visiblePlans = $changingContract
            ? $plans->filter(fn ($plan) => (int) $plan->id !== (int) $changingContract->plan_id)
            : $plans;
        @endphp
        <div class="coin-plan-grid">
          @foreach($visiblePlans as $plan)
            @include('livewire.partials.plan-card', [
              'plan' => $plan,
              'primaryPlan' => $changingContract?->plan ?? $primaryPlan,
              'selectedPlanId' => $selectedPlanId,
              'changingContract' => $changingContract ?? null,
            ])
          @endforeach
        </div>
        <div id="coin-plan-calculator" class="coin-plan-calculator" style="padding: 26px 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 340px); gap: 40px; align-items: start; scroll-margin-top: 96px;">
          <div>
            <div style="display: flex; align-items: baseline; gap: 12px;">
              <span style="font-size: 17px; font-weight: 600;">{{ __('coin.invest.calculator') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ $this->calculatorTermLabel }}</span>
            </div>
            <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 13.5px; color: rgba(214,238,248,0.74);">{{ __('coin.invest.investment_amount') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">{{ $this->powerLabel }} <span style="font-size: 12px; color: rgba(214,238,248,0.7);">{{ $this->walletCurrency }}</span></span>
            </div>
            @php
              $calcMin = $this->calculatorMin;
              $calcMax = $this->calculatorMax;
              $calcMid = (int) round(($calcMin + $calcMax) / 2);
              $calcUpperMid = (int) round($calcMin + (($calcMax - $calcMin) * 0.66));
            @endphp
            <input type="range" min="{{ $calcMin }}" max="{{ $calcMax }}" step="{{ $this->calculatorStep }}" wire:model.live="power" wire:key="calc-slider-{{ $selectedPlanId }}" style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;" />
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);">
              <span>{{ number_format($calcMin, 0, '.', ' ') }} {{ $this->walletCurrency }}</span>
              <span>{{ number_format($calcMid, 0, '.', ' ') }} {{ $this->walletCurrency }}</span>
              <span>{{ number_format($calcUpperMid, 0, '.', ' ') }} {{ $this->walletCurrency }}</span>
              <span>{{ number_format($calcMax, 0, '.', ' ') }} {{ $this->walletCurrency }}</span>
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
          <div class="coin-plan-calculator__summary" style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.14); background: linear-gradient(170deg, rgba(20,55,80,0.7), rgba(6,20,35,0.85));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.selected_plan')) }}</div>
            <div style="margin-top: 12px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em;">{{ $this->planName }}</div>
            <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
            <div class="coin-kv-list" style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div class="coin-kv-row"><span>{{ __('coin.invest.min_investment') }}</span><span>{{ $this->planCompute }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.term') }}</span><span>{{ $this->planTerm }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.infrastructure') }}</span><span>{{ $this->planInfra }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.estimated_price') }}</span><span>{{ $this->planPrice }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.currency') }}</span><span>{{ $wallet?->currency ?? 'USDT' }}</span></div>
            </div>
            @if($changingContract)
            <div class="coin-plan-change-summary" style="margin-top: 14px; padding: 14px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
              <div class="coin-kv-row"><span>{{ __('coin.invest.change_plan_from') }}</span><span>{{ $changingContract->plan?->displayName() }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.change_plan_to') }}</span><span style="font-weight: 500;">{{ $this->planName }}</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.change_plan_top_up') }}</span><span>@if($this->planChangeTopUp > 0){{ number_format($this->planChangeTopUp, 2, '.', ',') }} {{ $this->walletCurrency }}@else{{ __('coin.invest.change_plan_no_top_up') }}@endif</span></div>
              <div class="coin-kv-row"><span>{{ __('coin.invest.change_plan_principal_after') }}</span><span>{{ number_format($this->planChangePrincipalAfter, 2, '.', ',') }} {{ $this->walletCurrency }}</span></div>
            </div>
            @if($this->planChangeHasInsufficientFunds && (int) ($selectedPlanId ?? 0) !== (int) $changingContract->plan_id)
            <p style="margin: 16px 0 0; font-size: 13px; line-height: 1.55; color: #ffb454;">
              {{ __('coin.payment_modal.insufficient_funds_short') }}
              <button type="button" wire:click="goToWalletTopUp" style="margin-left: 4px; padding: 0; border: 0; background: none; color: oklch(0.88 0.12 192); font-family: inherit; font-size: 13px; font-weight: 600; text-decoration: underline; cursor: pointer;">{{ __('coin.payment_modal.top_up_balance') }}</button>
            </p>
            @endif
            <button type="button" wire:click="openPlanChangeModal" wire:loading.attr="disabled" wire:target="openPlanChangeModal" @if((int) ($selectedPlanId ?? 0) === (int) $changingContract->plan_id || $this->planChangeHasInsufficientFunds) disabled @endif style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer; opacity: {{ ((int) ($selectedPlanId ?? 0) === (int) $changingContract->plan_id || $this->planChangeHasInsufficientFunds) ? '0.45' : '1' }};">
              <span wire:loading.remove wire:target="openPlanChangeModal">{{ __('coin.invest.change_plan') }}</span>
              <span wire:loading wire:target="openPlanChangeModal">{{ __('coin.payment_modal.confirming') }}</span>
            </button>
            <p style="margin: 16px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.invest.change_plan_admin_note') }} {{ __('coin.invest.change_plan_downgrade_note') }} {{ __('coin.invest.change_plan_time_note') }}</p>
            @else
            <button type="button" wire:click="openInvestmentPaymentModal" wire:loading.attr="disabled" wire:target="openInvestmentPaymentModal" style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">
              <span wire:loading.remove wire:target="openInvestmentPaymentModal">{{ __('coin.actions.invest') }}</span>
              <span wire:loading wire:target="openInvestmentPaymentModal">{{ __('coin.payment_modal.confirming') }}</span>
            </button>
            <p style="margin: 16px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.invest.estimates_note') }}</p>
            @endif
            @error('purchase')<p style="margin: 12px 0 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
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
            @include('livewire.partials.contract-active-card', ['contract' => $contract, 'primaryContract' => $primaryContract, 'pendingPlanChange' => $pendingPlanChanges->get($contract->id)])
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
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.accrued') }}</div>
            <div style="margin-top: 4px; font-size: 11px; color: rgba(214,238,248,0.55);">{{ __('coin.stats.profit_card_all_types_hint') }}</div>
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
          <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <div>
              <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.profit_trend') }}</span>
              <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.66);">{{ __('coin.stats.daily_profit_chart_hint') }}</div>
            </div>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ $this->walletCurrency }} · {{ $this->periodLabel }}</span>
          </div>
          @php $profitTrend = $this->profitTrendChart; @endphp
          @if($profitTrend['hasData'])
          <div style="margin-top: 26px;">
            <x-profit-line-chart :chart="$profitTrend" :height="210" area-id="profitTrendArea" line-id="profitTrendLine" />
          </div>
          @else
          <div style="margin-top: 26px; padding: 48px 16px; text-align: center; font-size: 13px; color: rgba(214,238,248,0.66);">{{ __('coin.stats.no_profit_yet') }}</div>
          @endif
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
            @if(! $showFullProfitHistory)
            <button type="button" wire:click="openFullProfitHistory" style="display: block; width: 100%; margin-top: 18px; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; text-align: center; cursor: pointer;">{{ __('coin.stats.full_history') }}</button>
            @endif
          </div>
        </div>

        @if($showFullProfitHistory && $profitHistoryPage)
        <div id="profit-history-full" style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="margin-bottom: 6px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.profit_history_sub') }}</div>
          @include('livewire.partials.profit-history-list', ['transactions' => $profitHistoryPage, 'wallet' => $wallet])
          <button type="button" wire:click="closeFullProfitHistory" style="display: block; width: 100%; margin-top: 18px; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; text-align: center; cursor: pointer;">{{ __('coin.stats.collapse_history') }}</button>
        </div>
        @endif
      </section>
    @endif

    @if($section === 4)
      <section data-screen-label="{{ __('coin.nav.wallet') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">{{ mb_strtoupper(__('coin.wallet.total_balance')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.available')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedAvailable() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.available_hint') }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.locked')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedLocked() ?? \App\Support\MoneyFormat::zero() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.locked_principal_hint') }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.wallet.pending_payout')) }}</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedPending() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $wallet?->pending_note }}</div>
          </div>
        </div>

        <div class="coin-wallet-actions-grid">
          <div class="coin-wallet-action-card">
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
            @if($this->depositCreditPreview && $depositCurrency !== $this->walletCurrency)
            <p style="margin-top:10px;font-size:12px;line-height:1.5;color:rgba(214,238,248,0.72);">{{ __('coin.wallet.deposit_credit_preview', ['amount' => $this->depositCreditPreview]) }}</p>
            @endif
            <div style="margin-top: 14px; display: flex; justify-content: space-between; gap: 14px; font-size: 12.5px;">
              <span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.min_top_up') }}</span>
              <span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->depositMinLabel }}</span>
            </div>
            <div class="coin-deposit-presets">
              <button type="button" wire:click="setDepositPreset(100)" class="coin-deposit-presets__btn coin-btn-quiet">100 {{ $depositCurrency }}</button>
              <button type="button" wire:click="setDepositPreset(500)" class="coin-deposit-presets__btn coin-btn-quiet">500 {{ $depositCurrency }}</button>
              <button type="button" wire:click="setDepositPreset(1000)" class="coin-deposit-presets__btn coin-btn-quiet">1 000 {{ $depositCurrency }}</button>
            </div>
            <div class="coin-wallet-action-card__footer">
              <button type="button" wire:click="openTopUpPaymentModal" wire:loading.attr="disabled" wire:target="openTopUpPaymentModal" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">
                <span wire:loading.remove wire:target="openTopUpPaymentModal">{{ __('coin.wallet.add_funds') }}</span>
                <span wire:loading wire:target="openTopUpPaymentModal">{{ __('coin.payment_modal.confirming') }}</span>
              </button>
            </div>
          </div>

          <div class="coin-wallet-action-card">
            <div style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.payout_title') }}</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.wallet.payout_sub') }}</div>
            <div style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.amount')) }}</div>
              <button type="button" wire:click="setWithdrawMax" wire:loading.attr="disabled" wire:target="setWithdrawMax" style="border:0;background:transparent;color:oklch(0.88 0.11 195);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;cursor:pointer;padding:0;">{{ mb_strtoupper(__('coin.wallet.max')) }}</button>
            </div>
            <div style="margin-top: 9px; padding: 13px 15px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.16); background: rgba(4,16,28,0.6); display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 15px;">
              <input type="text" inputmode="decimal" wire:model="withdrawAmount" wire:key="withdraw-amount-{{ $withdrawAmount }}" placeholder="0.00" autocomplete="off" style="flex:1;min-width:0;border:0;background:transparent;color:#f0fbff;outline:none;font-family:inherit;font-size:15px;" />
              <span style="flex:none;margin-left:12px;color:rgba(214,238,248,0.78);">{{ $this->walletCurrency }}</span>
            </div>
            @error('withdrawAmount')<p style="margin-top:8px;font-size:12px;color:#ff8f8f;">{{ $message }}</p>@enderror
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 11px; font-size: 12.5px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.network_fee') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->networkFeeLabel }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">{{ __('coin.wallet.processing_time') }}</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~2 h</span></div>
            </div>
            <div class="coin-wallet-action-card__footer">
              <button type="button" wire:click="openPayoutPaymentModal" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">{{ __('coin.wallet.request_payout') }}</button>
            </div>
          </div>

          <div class="coin-wallet-action-card">
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
            <div class="coin-wallet-action-card__footer">
              <button wire:click="setSection(6)" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">{{ __('coin.wallet.manage_addresses') }}</button>
            </div>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 15px; font-weight: 600;">{{ __('coin.wallet.transactions') }}</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ __('coin.stats.total_entries', ['count' => $walletTransactions->total()]) }}</span>
          </div>
          @include('livewire.partials.transaction-list-toolbar', [
            'searchProperty' => 'walletSearch',
            'placeholder' => __('coin.wallet.search_transactions'),
          ])
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
            <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'occurred_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => 'walletSort', 'dirProperty' => 'walletDir', 'sortMethod' => 'sortWallet'])</span>
            <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'type', 'label' => mb_strtoupper(__('coin.table.type')), 'sortProperty' => 'walletSort', 'dirProperty' => 'walletDir', 'sortMethod' => 'sortWallet'])</span>
            <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'source', 'label' => mb_strtoupper(__('coin.table.destination')), 'sortProperty' => 'walletSort', 'dirProperty' => 'walletDir', 'sortMethod' => 'sortWallet'])</span>
            <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'status', 'label' => mb_strtoupper(__('coin.table.status')), 'sortProperty' => 'walletSort', 'dirProperty' => 'walletDir', 'sortMethod' => 'sortWallet'])</span>
            <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'amount', 'label' => mb_strtoupper(__('coin.table.amount')), 'sortProperty' => 'walletSort', 'dirProperty' => 'walletDir', 'sortMethod' => 'sortWallet', 'align' => 'right'])</span>
          </div>
          @forelse($walletTransactions as $transaction)
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last || $walletTransactions->total() > 0) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;"><span style="color: rgba(214,238,248,0.78);">{{ $transaction->formattedOccurredAt() }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->displayType() }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.72);">{{ $transaction->displaySource() }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: {{ $transaction->statusColor() }};">{{ $transaction->displayStatus() }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span></div>
          @empty
          <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.admin.no_transactions') }}</div>
          @endforelse
          @include('livewire.partials.coin-pagination', [
            'paginator' => $walletTransactions,
            'perPageProperty' => 'walletPerPage',
            'perPageOptions' => [10, 20, 50],
          ])
        </div>
      </section>
    @endif

    @if($section === 5)
      @include('livewire.partials.referrals-section')
    @endif

    @if($section === 6)
      @include('livewire.partials.settings-section')
    @endif

    @if($section === 7)
      @include('livewire.partials.support-section')
    @endif

    @if($section === 8)
      @include('livewire.partials.notifications-section')
    @endif
  </main>
  </div>

@include('livewire.partials.payment-gateway')
@include('livewire.partials.payment-modal')
@include('livewire.partials.contract-details-modal')
@include('livewire.partials.sessions-modal')
@include('livewire.partials.wallet-address-modal')
</div>

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

  $wire.on('plan-change-toast', (payload) => {
    const message = window.readLivewireEventPayload?.(payload, 'message') ?? payload?.message;
    const variant = window.readLivewireEventPayload?.(payload, 'variant') ?? payload?.variant ?? 'incoming';

    if (message) {
      window.showSupportToast?.(message, variant);
    }
  });
</script>
@endscript