<div x-data x-effect="document.documentElement.classList.toggle('coin-nav-open', @js($menuOpen))" @unless($section === 7) wire:poll.10s="pollSupportUnread" @endunless>
<div class="coin-dashboard" style="display: flex; min-height: 100vh; width: 1440px; margin: 0 auto; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif;">

  <div class="coin-nav-overlay" wire:click="closeMenu"></div>
  <aside class="coin-sidebar" style="width: 248px; flex: none; border-right: 1px solid rgba(150,235,250,0.1); background: rgba(4,16,28,0.6); padding: 22px 16px; display: flex; flex-direction: column; gap: 3px; min-height: 0;">
    <div class="coin-sidebar-nav" style="flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 3px;">
    <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 11px; padding: 4px 10px 24px; color: inherit;">
      <div style="width: 28px; height: 28px; border-radius: 9px; background: linear-gradient(145deg, oklch(0.86 0.12 192), oklch(0.6 0.13 210)); display: grid; place-items: center; box-shadow: 0 8px 22px -8px oklch(0.78 0.13 192 / 0.8);">
        <div style="width: 10px; height: 10px; border-radius: 3px; background: #061423;"></div>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 600; letter-spacing: -0.015em;">Coin</div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.6);">COMPUTE CONSOLE</div>
      </div>
    </a>

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 0 12px 10px;">MAIN</div>

    <button wire:click="setSection(0)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: #e6f4fa; text-align: left; cursor: pointer;">
      @if($section === 0)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: oklch(0.86 0.12 192);"></span>
      <span style="position: relative;">Overview</span>
    </button>
    <button wire:click="setSection(1)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 1)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">Plans</span>
    </button>
    <button wire:click="setSection(2)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 2)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative; flex: 1;">Contracts</span>
      <span style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; padding: 2px 7px; border-radius: 6px; background: rgba(150,235,250,0.1); color: rgba(214,238,248,0.8);">2</span>
    </button>
    <button wire:click="setSection(3)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 3)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">Statistics</span>
    </button>
    <button wire:click="setSection(4)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 4)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">Wallet</span>
    </button>
    <button wire:click="setSection(5)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 5)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">Referrals</span>
    </button>

    <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.16em; color: rgba(214,238,248,0.55); padding: 22px 12px 10px;">ACCOUNT</div>
    <button wire:click="setSection(6)" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: rgba(230,244,250,0.72); text-align: left; cursor: pointer;">
      @if($section === 6)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@endif
      <span style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>
      <span style="position: relative;">Settings</span>
    </button>
    <button type="button" wire:click="openSupport" wire:key="support-nav-{{ $this->unreadSupportCount }}" data-unread-support="{{ $this->unreadSupportCount }}" class="coin-nav-support {{ $this->unreadSupportCount > 0 ? 'coin-nav-support--unread' : '' }}" style="position: relative; display: flex; align-items: center; gap: 11px; padding: 11px 12px; border: 0; border-radius: 10px; background: none; font-family: inherit; font-size: 13.5px; color: {{ $this->unreadSupportCount > 0 ? '#f0fbff' : ($section === 7 ? '#e6f4fa' : 'rgba(230,244,250,0.72)') }}; text-align: left; cursor: pointer; width: 100%; z-index: 2;">
      @if($section === 7)<span style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); pointer-events: none;"></span>@elseif($this->unreadSupportCount > 0)<span data-user-support-nav-bg style="position: absolute; inset: 0; border-radius: 10px; background: oklch(0.72 0.16 35 / 0.12); border: 1px solid oklch(0.82 0.18 35 / 0.35); pointer-events: none;"></span>@endif
      <span class="coin-nav-support-dot" style="position: relative; width: 7px; height: 7px; border-radius: 2px; background: {{ $this->unreadSupportCount > 0 ? 'oklch(0.85 0.18 35)' : ($section === 7 ? 'oklch(0.86 0.12 192)' : 'rgba(150,235,250,0.3)') }}; {{ $this->unreadSupportCount > 0 ? 'box-shadow: 0 0 10px oklch(0.85 0.18 35 / 0.8);' : '' }}"></span>
      <span class="coin-nav-support-label" style="position: relative; flex: 1; font-weight: {{ $this->unreadSupportCount > 0 ? '600' : '400' }};">Live support</span>
      @if($this->unreadSupportCount > 0)
        <span class="coin-support-badge" data-user-support-nav-badge style="position: relative; font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; min-width: 20px; text-align: center; padding: 3px 7px; border-radius: 999px; background: linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25)); color: #1a0a04; box-shadow: 0 0 16px oklch(0.82 0.2 35 / 0.55);">{{ $this->unreadSupportCount }}</span>
      @endif
    </button>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 8px; flex-shrink: 0;">
      @csrf
      <button type="submit" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: rgba(230,244,250,0.78); font-family: inherit; font-size: 13px; cursor: pointer;">Log out</button>
    </form>

    <div style="margin-top: 12px; flex-shrink: 0; padding: 18px; border-radius: 14px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(6,20,35,0.6));">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">ACTIVE PLAN</div>
      <div style="margin-top: 9px; font-size: 15px; font-weight: 600;">{{ $primaryPlan?->name ?? '—' }}</div>
      <div style="margin-top: 4px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.78);">{{ $primaryContract?->formattedTflops() ?? '0' }} TFLOPS · {{ $primaryContract?->duration_days ?? 0 }} d</div>
      <div style="margin-top: 14px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.14);"><div style="width: {{ $primaryContract?->progress_percent ?? 0 }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
      <div style="margin-top: 8px; font-size: 11.5px; color: rgba(214,238,248,0.7);">Elapsed {{ $primaryContract?->days_elapsed ?? 0 }} of {{ $primaryContract?->duration_days ?? 0 }} days</div>
      <button wire:click="setSection(1)" style="width: 100%; margin-top: 16px; padding: 10px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Upgrade plan</button>
    </div>
  </aside>

  <main style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
    <header class="coin-dash-header" style="display: flex; align-items: center; gap: 20px; padding: 20px 32px; border-bottom: 1px solid rgba(150,235,250,0.1); background: rgba(4,16,28,0.4);">
      <button type="button" class="coin-burger" wire:click="toggleMenu" aria-label="Open menu"><span></span><span></span><span></span></button>
      <div style="min-width: 0; flex: 1;">
        <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ $this->title }}</div>
        <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $this->subtitle }}</div>
      </div>
      <div class="coin-dash-meta">
        <div class="coin-hide-mobile" style="display: flex; align-items: center; gap: 9px; padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.8);">
          <span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); box-shadow: 0 0 9px oklch(0.85 0.15 160); animation: dbPulse 2.4s infinite;"></span>
          NETWORK ONLINE
        </div>
        <div class="coin-hide-mobile" style="padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.8);">EPOCH {{ $user->epoch_label }}</div>
        <div style="display: flex; align-items: center; gap: 10px; padding: 6px 12px 6px 6px; border-radius: 999px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04);">
          <span style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(145deg, oklch(0.7 0.13 198), oklch(0.5 0.15 285)); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #04121f;">A</span>
          <span class="coin-hide-mobile" style="font-size: 13px;">{{ $user->accountLabel() }}</span>
        </div>
      </div>
    </header>

    @if($section === 0)
      <section data-screen-label="Overview" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">TOTAL BALANCE</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $symbol }} · {{ $wallet?->usd_estimate_label }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">ACTIVE COMPUTE</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: #f0fbff;">{{ number_format($user->active_tflops, 0, '.', ',') }} <span style="font-size: 13px; color: rgba(214,238,248,0.7);">TFLOPS</span></div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $user->nodes_label }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">ACTIVE PLAN</div>
            <div style="margin-top: 14px; font-size: 24px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">{{ $primaryPlan?->name ?? '—' }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Contract {{ $primaryContract?->duration_days }} days · {{ $primaryPlan?->infra }}</div>
          </div>
          <div style="padding: 22px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">EXPECTED DAILY REWARD</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 26px; color: oklch(0.9 0.12 192);">{{ $user->formattedDailyReward() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.75);">{{ $symbol }} · 3 epochs per day</div>
          </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 10px; padding: 16px 18px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); align-items: center;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7); margin-right: 6px;">QUICK ACTIONS</span>
          <button wire:click="setSection(1)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Activate plan</button>
          <button wire:click="setSection(4)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Deposit</button>
          <button wire:click="setSection(4)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Withdraw</button>
          <button wire:click="setSection(2)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Increase compute</button>
          <button wire:click="setSection(5)" style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(180,180,255,0.26); background: rgba(150,140,255,0.1); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Invite</button>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 15px; font-weight: 600;">Accruals</div>
                <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Daily reward accruals · placeholder data</div>
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

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">Compute distribution</div>
            <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Where your compute ran this epoch</div>
            <div style="display: flex; align-items: center; gap: 22px; margin-top: 24px;">
              <div style="position: relative; width: 122px; height: 122px; flex: none; border-radius: 50%; background: conic-gradient(oklch(0.86 0.12 192) 0 42%, oklch(0.72 0.11 215) 42% 73%, oklch(0.7 0.15 292) 73% 90%, rgba(214,238,248,0.16) 90% 100%);">
                <div style="position: absolute; inset: 16px; border-radius: 50%; background: #081b2c; display: grid; place-items: center;">
                  <div style="text-align: center;">
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 17px; color: #f0fbff;">90%</div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 8px; letter-spacing: 0.1em; color: rgba(214,238,248,0.66);">UTILIZED</div>
                  </div>
                </div>
              </div>
              <div style="display: flex; flex-direction: column; gap: 11px; font-size: 12.5px; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 8px;"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.86 0.12 192);"></span><span style="color: rgba(214,238,248,0.78);">Training</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">42%</span></div>
                <div style="display: flex; align-items: center; gap: 8px;"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.72 0.11 215);"></span><span style="color: rgba(214,238,248,0.78);">Inference</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">31%</span></div>
                <div style="display: flex; align-items: center; gap: 8px;"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.7 0.15 292);"></span><span style="color: rgba(214,238,248,0.78);">Generation</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">17%</span></div>
                <div style="display: flex; align-items: center; gap: 8px;"><span style="width: 8px; height: 8px; border-radius: 2px; background: rgba(214,238,248,0.2);"></span><span style="color: rgba(214,238,248,0.78);">Research</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">10%</span></div>
              </div>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">Active contracts</span>
              <button wire:click="setSection(2)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">ALL CONTRACTS</button>
            </div>
            <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 14px;">
              @foreach($activeContracts as $contract)
              <div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 13px;"><span>{{ $contract->plan?->name }} · {{ $contract->formattedTflops() }} TFLOPS</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $contract->progress_percent }}%</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $contract->progress_percent }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
              </div>
              @endforeach
              <div style="display: flex; justify-content: space-between; font-size: 12.5px; color: rgba(214,238,248,0.7); padding-top: 4px;"><span>Next settlement</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->nextSettlementLabel }}</span></div>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">Wallet</span>
              <button wire:click="setSection(4)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">OPEN</button>
            </div>
            <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 13px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Available</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedAvailable() }} {{ $symbol }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Pending settlement</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->formattedPending() }} {{ $symbol }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Payout address</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.8);">{{ $wallet?->payout_address }}</span></div>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 20px;">
              <button wire:click="setSection(4)" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: oklch(0.6 0.13 200 / 0.22); color: #eafcff; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Deposit</button>
              <button wire:click="setSection(4)" style="flex: 1; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Withdraw</button>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(170deg, rgba(120,110,220,0.13), rgba(150,235,250,0.02));">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">Referrals</span>
              <button wire:click="setSection(5)" style="background: none; border: 0; padding: 0; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.1em; color: oklch(0.88 0.11 195); cursor: pointer;">OPEN</button>
            </div>
            <div style="margin-top: 18px; display: flex; align-items: baseline; gap: 10px;">
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 28px; color: #f0fbff;">{{ $referral?->invited_count ?? 0 }}</span>
              <span style="font-size: 12.5px; color: rgba(214,238,248,0.72);">invited</span>
            </div>
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Referral rewards</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">{{ $referral?->formattedTotalRewards() }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Commission share</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $referral?->commissionLabel() }}</span></div>
            </div>
            <button wire:click="setSection(5)" style="width: 100%; margin-top: 20px; padding: 10px; border-radius: 10px; border: 1px solid rgba(180,180,255,0.3); background: rgba(150,140,255,0.12); color: #eafcff; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Invite friends</button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">Recent activity</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">LAST 5 ENTRIES</span>
            </div>
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>TIME</span><span>TYPE</span><span>SOURCE</span><span style="text-align: right;">AMOUNT</span></div>
            @foreach($transactions->take(5) as $transaction)
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;"><span style="color: rgba(214,238,248,0.78);">{{ $transaction->occurred_label }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->type }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->source }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span></div>
            @endforeach
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">Data center status</div>
            <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Where your compute is deployed</div>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>Frankfurt · FRA-02</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.86 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>ONLINE</span></div>
                <div style="margin-top: 11px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level1BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
                <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">ALLOCATED 768 TFLOPS</div>
              </div>
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>Ashburn · IAD-01</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.86 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>ONLINE</span></div>
                <div style="margin-top: 11px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level2BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.72 0.11 215);"></div></div>
                <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">ALLOCATED 432 TFLOPS</div>
              </div>
              <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(180,180,255,0.16); background: rgba(150,140,255,0.07);">
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;"><span>São Paulo · GRU-01</span><span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: oklch(0.88 0.15 90);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>EXPANDING</span></div>
                <div style="margin-top: 11px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">CAPACITY COMING SOON</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif

    @if($section === 1)
      <section data-screen-label="Plans" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          @foreach($plans as $plan)
            @include('livewire.partials.plan-card', ['plan' => $plan, 'primaryPlan' => $primaryPlan])
          @endforeach
        </div>
        <div style="padding: 26px 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 340px); gap: 40px; align-items: center;">
          <div>
            <div style="display: flex; align-items: baseline; gap: 12px;">
              <span style="font-size: 17px; font-weight: 600;">Reward calculator</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">12-MONTH CONTRACT</span>
            </div>
            <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 13.5px; color: rgba(214,238,248,0.74);">Select compute</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">{{ $this->powerLabel }} <span style="font-size: 12px; color: rgba(214,238,248,0.7);">TFLOPS</span></span>
            </div>
            <input type="range" min="100" max="10000" step="100" wire:model.live="power" style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;" />
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(214,238,248,0.62);"><span>100</span><span>2 500</span><span>5 000</span><span>10 000</span></div>
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 26px;">
              <div style="padding: 16px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PER DAY</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ $this->daily }}</div>
              </div>
              <div style="padding: 16px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PER MONTH</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ $this->monthly }}</div>
              </div>
              <div style="padding: 16px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.3); background: oklch(0.6 0.13 200 / 0.18);">
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.75);">PER YEAR</div>
                <div style="margin-top: 10px; font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.9 0.12 192);">{{ $this->yearly }}</div>
              </div>
            </div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.14); background: linear-gradient(170deg, rgba(20,55,80,0.7), rgba(6,20,35,0.85));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">MATCHING PLAN</div>
            <div style="margin-top: 12px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em;">{{ $this->planName }}</div>
            <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planInfra }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Estimated price</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $this->planPrice }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Reward token</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $symbol }}</span></div>
            </div>
            <button style="width: 100%; margin-top: 22px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">Activate compute</button>
            <p style="margin: 16px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">Estimates are based on current network load. Rewards change with demand — returns are not guaranteed.</p>
          </div>
        </div>
      </section>
    @endif

    @if($section === 2)
      <section data-screen-label="Contracts" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">ACTIVE CONTRACTS</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->activeContractCount }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">TOTAL ALLOCATED</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->totalAllocatedTflops }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">LIFETIME REWARDS</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: oklch(0.9 0.12 192);">{{ $this->lifetimeRewards }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">NEXT EXPIRY</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->nextExpiryLabel }}</div>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
          @foreach($activeContracts as $contract)
            @include('livewire.partials.contract-active-card', ['contract' => $contract, 'primaryContract' => $primaryContract])
          @endforeach

          @foreach($completedContracts as $contract)
          <div style="padding: 24px; border-radius: 16px; border: 1px dashed rgba(150,235,250,0.2); background: rgba(150,235,250,0.02); display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
            <div>
              <div style="font-size: 15px; font-weight: 600;">Completed contract · {{ $contract->plan?->name }}</div>
              <div style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.7);">{{ $contract->completed_summary }}</div>
            </div>
            <button style="padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;">Renew</button>
          </div>
          @endforeach
        </div>
      </section>
    @endif

    @if($section === 3)
      <section data-screen-label="Statistics" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <button wire:click="setPeriod(0)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 0)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">Per day</span>
          </button>
          <button wire:click="setPeriod(1)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 1)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">Per week</span>
          </button>
          <button wire:click="setPeriod(2)" style="position: relative; padding: 10px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">
            @if($period === 2)<span style="position: absolute; inset: -1px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.6); background: oklch(0.6 0.13 200 / 0.22); pointer-events: none;"></span>@endif
            <span style="position: relative;">Per month</span>
          </button>
          <div style="flex: 1;"></div>
          <button style="padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">Export CSV</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">REWARDS {{ $this->periodLabel }}</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $this->periodTotal }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ $symbol }} · accrued</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">AVG PER EPOCH</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->avg_epoch_label }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ $epochsPerDay }} epochs per day</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">AVAILABILITY</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->availability_label }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">Your dedicated nodes</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">LOAD</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $user->load_label }}</div>
            <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">Compute on active tasks</div>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="display: flex; align-items: baseline; justify-content: space-between;">
            <span style="font-size: 15px; font-weight: 600;">Reward trend</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">{{ $symbol }} · {{ $this->periodLabel }}</span>
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

        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">By task type</span>
            <div style="display: flex; height: 10px; border-radius: 6px; overflow: hidden; margin-top: 20px;">
              <div style="width: 42%; background: oklch(0.86 0.12 192);"></div>
              <div style="width: 31%; background: oklch(0.72 0.11 215);"></div>
              <div style="width: 17%; background: oklch(0.7 0.15 292);"></div>
              <div style="width: 10%; background: rgba(214,238,248,0.2);"></div>
            </div>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
              <div style="display: flex; justify-content: space-between;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(214,238,248,0.78);"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.86 0.12 192);"></span>Training</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">42%</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(214,238,248,0.78);"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.72 0.11 215);"></span>Inference</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">31%</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(214,238,248,0.78);"><span style="width: 8px; height: 8px; border-radius: 2px; background: oklch(0.7 0.15 292);"></span>Generation</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">17%</span></div>
              <div style="display: flex; justify-content: space-between;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(214,238,248,0.78);"><span style="width: 8px; height: 8px; border-radius: 2px; background: rgba(214,238,248,0.2);"></span>Research</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">10%</span></div>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">By data center</span>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 16px;">
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Frankfurt · FRA-02</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">53%</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 53%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
              </div>
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Ashburn · IAD-01</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">30%</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 30%; height: 100%; border-radius: 3px; background: oklch(0.72 0.11 215);"></div></div>
              </div>
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Singapore · SIN-03</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">17%</span></div>
                <div style="margin-top: 9px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 17%; height: 100%; border-radius: 3px; background: oklch(0.7 0.15 292);"></div></div>
              </div>
            </div>
          </div>

          <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">Reward history</span>
            <div style="margin-top: 18px; display: flex; flex-direction: column;">
              <div style="display: flex; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid rgba(150,235,250,0.07); font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">8 Sep · epoch 20 914</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">+1,71</span></div>
              <div style="display: flex; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid rgba(150,235,250,0.07); font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">8 Sep · epoch 20 913</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">+1,68</span></div>
              <div style="display: flex; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid rgba(150,235,250,0.07); font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Sep 7 · epoch 20 912</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">+1,65</span></div>
              <div style="display: flex; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid rgba(150,235,250,0.07); font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Sep 7 · epoch 20 911</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">+1,70</span></div>
              <div style="display: flex; justify-content: space-between; padding: 11px 0; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Sep 6 · epoch 20 910</span><span style="font-family: 'JetBrains Mono', monospace; color: oklch(0.88 0.12 192);">+1,66</span></div>
            </div>
            <button style="width: 100%; margin-top: 18px; padding: 10px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">Full history</button>
          </div>
        </div>
      </section>
    @endif

    @if($section === 4)
      <section data-screen-label="Wallet" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.75);">TOTAL BALANCE</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedBalance() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.75);">{{ $symbol }} · {{ $wallet?->usd_estimate_label }}</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">AVAILABLE TO WITHDRAW</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedAvailable() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Accrued and unlocked</div>
          </div>
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">PENDING SETTLEMENT</div>
            <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">{{ $wallet?->formattedPending() }}</div>
            <div style="margin-top: 7px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $wallet?->pending_note }}</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.1fr); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">Deposit</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Top up to activate more compute</div>
            <div style="margin-top: 20px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">AMOUNT</div>
            <div style="margin-top: 9px; padding: 13px 15px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.16); background: rgba(4,16,28,0.6); display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 15px;">
              <span style="color: rgba(214,238,248,0.6);">0,00</span><span style="color: rgba(214,238,248,0.78);">{{ $symbol }}</span>
            </div>
            <div style="display: flex; gap: 7px; margin-top: 12px;">
              <span style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78);">100</span>
              <span style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78);">500</span>
              <span style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.78);">1 000</span>
            </div>
            <button style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">Go to deposit</button>
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">Withdraw</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Send accrued rewards to your address</div>
            <div style="margin-top: 20px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">AMOUNT</div>
            <div style="margin-top: 9px; padding: 13px 15px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.16); background: rgba(4,16,28,0.6); display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 15px;">
              <span style="color: rgba(214,238,248,0.6);">0,00</span><span style="color: oklch(0.88 0.11 195);">MAX</span>
            </div>
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 11px; font-size: 12.5px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Network fee</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">0,40 {{ $symbol }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Processing time</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~2 h</span></div>
            </div>
            <button style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">Request withdrawal</button>
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="font-size: 15px; font-weight: 600;">Payout details</div>
            <div style="margin-top: 5px; font-size: 12.5px; color: rgba(214,238,248,0.7);">Whitelisted addresses only</div>
            <div style="margin-top: 20px; padding: 14px 16px; border-radius: 12px; border: 1px dashed rgba(150,235,250,0.22); background: rgba(150,235,250,0.03);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PRIMARY WALLET</div>
              <div style="margin-top: 9px; font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #eafcff; word-break: break-all;">{{ $wallet?->payout_address }}</div>
              <div style="margin-top: 9px; display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>CONFIRMED</div>
            </div>
            <div style="margin-top: 14px; display: flex; flex-direction: column; gap: 11px; font-size: 12.5px;">
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Network</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->network_label }}</span></div>
              <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Min. withdrawal</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $wallet?->min_withdrawal_label }} {{ $symbol }}</span></div>
            </div>
            <button wire:click="setSection(6)" style="width: 100%; margin-top: 20px; padding: 11px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">Manage addresses</button>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="display: flex; align-items: baseline; justify-content: space-between;">
            <span style="font-size: 15px; font-weight: 600;">Transactions</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">ALL TYPES</span>
          </div>
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>DATE</span><span>TYPE</span><span>DESTINATION</span><span>STATUS</span><span style="text-align: right;">AMOUNT</span></div>
          @foreach($transactions as $transaction)
          <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;"><span style="color: rgba(214,238,248,0.78);">{{ $transaction->occurred_label }}</span><span style="color: rgba(214,238,248,0.78);">{{ $transaction->type }}</span><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.72);">{{ $transaction->source }}</span><span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: {{ $transaction->statusColor() }};">{{ $transaction->status_label }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span></div>
          @endforeach
        </div>
      </section>
    @endif

    @if($section === 5)
      <section data-screen-label="Referrals" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
        <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(180,180,255,0.18); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.12));">
          <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">Invite. Grow. Earn.</div>
          <p style="margin: 10px 0 0; max-width: 620px; font-size: 14px; line-height: 1.6; color: rgba(214,238,248,0.75);">Share your link: when someone activates AI compute, a share of network fees from their contract is credited to your reward balance while the contract is active.</p>
          <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px; flex-wrap: wrap;">
            <div style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.3); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13.5px; color: #eafcff;">coin.local/r/<span style="color: oklch(0.88 0.11 195);">{{ $referral?->code }}</span></div>
            <button style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">Copy link</button>
            <button style="padding: 13px 20px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">Invite by email</button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">INVITED</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->invited_count ?? 0 }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">ACTIVE CONTRACTS</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->active_contracts ?? 0 }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">REFERRAL REWARDS</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: oklch(0.9 0.12 192);">{{ $referral?->formattedRewardsBalance() }}</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">COMMISSION SHARE</div>
            <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->commissionLabel() }}</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 16px;">
          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
              <span style="font-size: 15px; font-weight: 600;">Accrual history</span>
              <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">LAST 5</span>
            </div>
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);"><span>USER</span><span>LEVEL</span><span>PLAN</span><span style="text-align: right;">ACCRUED</span></div>
            @foreach($referralAccruals as $accrual)
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px;"><span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $accrual->user_label }}</span><span style="color: rgba(214,238,248,0.78);">{{ $accrual->level_label }}</span><span style="color: rgba(214,238,248,0.78);">{{ $accrual->plan_name }}</span><span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: oklch(0.88 0.12 192);">{{ $accrual->amount_label }}</span></div>
            @endforeach
          </div>

          <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
            <span style="font-size: 15px; font-weight: 600;">Network structure</span>
            <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 18px;">
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Level 1 · direct</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $referral?->level1_users }} users</span></div>
                <div style="margin-top: 9px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level1BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
                <div style="margin-top: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">SHARE {{ $referral?->level1_percent }}%</div>
              </div>
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;"><span style="color: rgba(214,238,248,0.78);">Level 2 · indirect</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $referral?->level2_users }} users</span></div>
                <div style="margin-top: 9px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $referral?->level2BarPercent() }}%; height: 100%; border-radius: 3px; background: oklch(0.7 0.15 292);"></div></div>
                <div style="margin-top: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.68);">SHARE {{ $referral?->level2_percent }}%</div>
              </div>
            </div>
            <p style="margin: 22px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">Placeholder values. Referral credits come from network fees, not from new deposits.</p>
          </div>
        </div>
      </section>
    @endif

    @if($section === 6)
      <section data-screen-label="Settings" style="padding: 28px 32px 40px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; align-items: start;">
        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="font-size: 15px; font-weight: 600;">Profile</div>
          <div style="display: flex; align-items: center; gap: 16px; margin-top: 20px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(145deg, oklch(0.7 0.13 198), oklch(0.5 0.15 285)); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 17px; color: #04121f;">A</div>
            <div>
              <div style="font-size: 15px; font-weight: 500;">{{ $user->accountLabel() }}</div>
              <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.72);">{{ $user->email }}</div>
            </div>
          </div>
          <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 14px;">
            <div>
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">DISPLAY NAME</div>
              <div style="margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(4,16,28,0.5); font-size: 13.5px; color: rgba(214,238,248,0.85);">{{ $user->name }}</div>
            </div>
            <div>
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">TIME ZONE</div>
              <div style="margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(4,16,28,0.5); font-size: 13.5px; color: rgba(214,238,248,0.85);">UTC+02:00</div>
            </div>
          </div>
          <button style="margin-top: 22px; padding: 11px 20px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Save</button>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="font-size: 15px; font-weight: 600;">Security</div>
          <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 13.5px;">Two-factor authentication</div>
                <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.7);">Authenticator app</div>
              </div>
              <span style="padding: 5px 11px; border-radius: 7px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 10px; color: oklch(0.88 0.14 160);">ENABLED</span>
            </div>
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 13.5px;">Withdrawal whitelist</div>
                <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.7);">1 confirmed address</div>
              </div>
              <span style="padding: 5px 11px; border-radius: 7px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 10px; color: oklch(0.88 0.14 160);">ON</span>
            </div>
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 13.5px;">Active sessions</div>
                <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.7);">2 devices · signed in Sep 8</div>
              </div>
              <button style="padding: 8px 14px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 12.5px; cursor: pointer;">Review</button>
            </div>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="font-size: 15px; font-weight: 600;">Connected wallet</div>
          <div style="margin-top: 20px; padding: 16px; border-radius: 12px; border: 1px dashed rgba(150,235,250,0.22); background: rgba(150,235,250,0.03);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">PRIMARY · PAYOUTS</div>
            <div style="margin-top: 9px; font-family: 'JetBrains Mono', monospace; font-size: 13px; word-break: break-all;">{{ $wallet?->payout_address }}</div>
            <div style="margin-top: 10px; display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>CONFIRMED</div>
          </div>
          <div style="display: flex; gap: 8px; margin-top: 16px;">
            <button style="flex: 1; padding: 11px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">Add address</button>
            <button style="flex: 1; padding: 11px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; cursor: pointer;">Disconnect</button>
          </div>
          <div style="margin-top: 24px; padding: 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <div>
                <div style="font-size: 13.5px;">Identity verification (KYC)</div>
                <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.7);">Required before withdrawals when enabled platform-wide</div>
              </div>
              <span style="padding: 5px 11px; border-radius: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10px; {{ $user->kycBadgeStyle() }}">{{ strtoupper($user->kycLabel()) }}</span>
            </div>
          </div>
        </div>

        <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
          <div style="font-size: 15px; font-weight: 600;">Notifications</div>
          <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 13.5px;">Reward credit</span>
              <span style="width: 38px; height: 22px; border-radius: 999px; background: oklch(0.6 0.13 200 / 0.5); border: 1px solid oklch(0.86 0.11 195 / 0.5); position: relative;"><span style="position: absolute; top: 2px; right: 2px; width: 16px; height: 16px; border-radius: 50%; background: #eafcff;"></span></span>
            </div>
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 13.5px;">Contract expiry reminders</span>
              <span style="width: 38px; height: 22px; border-radius: 999px; background: oklch(0.6 0.13 200 / 0.5); border: 1px solid oklch(0.86 0.11 195 / 0.5); position: relative;"><span style="position: absolute; top: 2px; right: 2px; width: 16px; height: 16px; border-radius: 50%; background: #eafcff;"></span></span>
            </div>
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 13.5px;">Data center status alerts</span>
              <span style="width: 38px; height: 22px; border-radius: 999px; background: rgba(150,235,250,0.14); border: 1px solid rgba(150,235,250,0.2); position: relative;"><span style="position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: rgba(214,238,248,0.6);"></span></span>
            </div>
            <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 13.5px;">Referral activity</span>
              <span style="width: 38px; height: 22px; border-radius: 999px; background: rgba(150,235,250,0.14); border: 1px solid rgba(150,235,250,0.2); position: relative;"><span style="position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: rgba(214,238,248,0.6);"></span></span>
            </div>
          </div>
        </div>
      </section>
    @endif

    @if($section === 7)
      @include('livewire.partials.support-section')
    @endif
  </main>
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
    window.showSupportToast?.(payload?.message ?? 'Message sent');
  });

  $wire.on('support-message-received', (payload) => {
    const incoming = payload?.incomingMessage ?? payload;

    if (incoming?.id || incoming?.body) {
      window.showIncomingMessageToast?.(incoming, 'New message from support');
    } else {
      window.showSupportToast?.('New message from support', 'incoming');
    }
  });

  $wire.on('support-unread-updated', (payload) => {
    window.updateUserSupportNavBadge?.(Number(payload?.count ?? 0));
  });

  $wire.watch('selectedTicketId', () => {
    if ($wire.section === 7) {
      requestAnimationFrame(() => window.scrollSupportThreadToBottom?.('auto'));
    }
  });
</script>
@endscript