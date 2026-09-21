<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\PlatformBrand::pageTitle('Инвестиционная платформа') }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    @livewireStyles
    @include('partials.coin-reverb-config-guest')
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <script src="{{ asset('coin/mobile.js') }}?v={{ file_exists(public_path('coin/mobile.js')) ? filemtime(public_path('coin/mobile.js')) : 1 }}" defer></script>
    <script src="{{ asset('coin/landing-plans.js') }}?v={{ file_exists(public_path('coin/landing-plans.js')) ? filemtime(public_path('coin/landing-plans.js')) : 1 }}" defer></script>
    <script src="{{ asset('coin/landing-scroll-top.js') }}?v={{ file_exists(public_path('coin/landing-scroll-top.js')) ? filemtime(public_path('coin/landing-scroll-top.js')) : 1 }}" defer></script>
    <script src="{{ asset('coin/landing-faq.js') }}?v={{ file_exists(public_path('coin/landing-faq.js')) ? filemtime(public_path('coin/landing-faq.js')) : 1 }}" defer></script>
    <style>
      body { margin: 0; background: #04101c; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
      .coin-app { width: 100%; max-width: 1440px; margin: 0 auto; box-sizing: border-box; overflow-x: hidden; }
      .coin-brand-logo { width: min(260px, 54vw); height: auto; max-height: 56px; display: block; object-fit: contain; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      @@keyframes paiPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
      input[type="range"] { accent-color: oklch(0.8 0.13 192); }
    </style>
</head>
<body>
<script>
(function () {
    try {
        sessionStorage.removeItem('coinPageNavigating');
    } catch (_) {}
    document.documentElement.classList.remove('coin-page-navigating');
    document.querySelectorAll('style').forEach(function (node) {
        if (node.textContent === 'x-dc{display:none!important}') {
            node.remove();
        }
    });
    var legacyDc = document.querySelector('x-dc');
    if (legacyDc) {
        legacyDc.style.display = 'block';
    }
})();
</script>
<div class="coin-app coin-landing" style="margin: 0 auto; position: relative; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; overflow: hidden;">
  <div style="position: absolute; top: -240px; right: -80px; width: 820px; height: 660px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.62 0.13 198 / 0.28), transparent 72%); filter: blur(30px); pointer-events: none;"></div>

  @include('partials.landing-header')

  @include('partials.landing-hero')

  @include('partials.landing-metrics')

  @include('partials.landing-how-section')

@include('partials.landing-plans-section', ['plans' => $plans, 'landingPlansPayload' => $landingPlansPayload])

  @include('partials.landing-benefits-section')

  @include('partials.landing-infra-section')

  <section id="account" data-screen-label="Dashboard" style="position: relative; z-index: 5; padding: 48px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 60px; margin-bottom: 44px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">DASHBOARD</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Everything in one place</h2>
      </div>
      <p style="max-width: 380px; margin: 0 0 6px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">Balance, active investments, plans, wallet, profit history, and referrals — this is what the dashboard looks like.</p>
    </div>

    <div style="border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(180deg, rgba(11,32,49,0.96), rgba(5,16,27,0.98)); box-shadow: 0 60px 120px -60px #000; overflow: hidden;">
      <div style="display: grid; grid-template-columns: 216px 1fr;">
        <aside style="padding: 22px 14px; border-right: 1px solid rgba(150,235,250,0.1); display: flex; flex-direction: column; gap: 3px;">
          <div class="coin-preview-brand" style="display: flex; align-items: center; padding: 0 10px 20px;">
            <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-brand-logo" />
          </div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); font-size: 13px; color: #f0fbff;"><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span>Overview</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Plans</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Contracts</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Statistics</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Wallet</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Referrals</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Settings</div>
        </aside>

        <div style="padding: 24px 26px 28px;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="font-size: 18px; font-weight: 600; letter-spacing: -0.02em;">Overview</div>
              <div style="margin-top: 4px; font-size: 12.5px; color: rgba(230,244,250,0.7);">Account overview · Core plan</div>
            </div>
            <div style="display: flex; gap: 7px;">
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.7);">30D</span>
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.4); background: oklch(0.6 0.13 200 / 0.22); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: #f0fbff;">14D</span>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 22px;">
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">BALANCE</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 482,60</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">LOCKED PRINCIPAL</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 200</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ACTIVE PLAN</div>
              <div style="margin-top: 12px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">Core</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.75);">EARNED TODAY</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: oklch(0.9 0.12 192);">+5,04</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 14px; margin-top: 14px;">
            <div style="padding: 20px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
              <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <span style="font-size: 14px; font-weight: 600;">Accruals</span>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">LAST 14 DAYS</span>
              </div>
              <div style="display: flex; align-items: flex-end; gap: 7px; height: 128px; margin-top: 20px;">
                <div style="flex: 1; height: 34%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 46%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 39%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 58%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
                <div style="flex: 1; height: 52%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
                <div style="flex: 1; height: 67%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
                <div style="flex: 1; height: 61%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
                <div style="flex: 1; height: 74%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
                <div style="flex: 1; height: 69%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
                <div style="flex: 1; height: 83%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.18));"></div>
                <div style="flex: 1; height: 78%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.18));"></div>
                <div style="flex: 1; height: 91%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.2));"></div>
                <div style="flex: 1; height: 86%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.2));"></div>
                <div style="flex: 1; height: 100%; border-radius: 4px; background: linear-gradient(180deg, #eafcff, oklch(0.88 0.12 192 / 0.22));"></div>
              </div>
            </div>

            <div style="padding: 20px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
              <span style="font-size: 14px; font-weight: 600;">Data center status</span>
              <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 14px; font-size: 12.5px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Data center 01</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">84%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Data center 02</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">91%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>Data center 03</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">46%</span></div>
                <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Contracts</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">2 active</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Referrals</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">28</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 14px;">
      <a href="/dashboard" style="padding: 13px 24px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-size: 14px; font-weight: 500;">View dashboard</a>
    </div>
  </section>

  @include('partials.landing-referrals-section')

  <section id="faq" data-screen-label="FAQ" style="position: relative; z-index: 5; padding: 48px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 72px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">FAQ</div>
        <h2 style="margin: 18px 0 0; font-size: 42px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">{{ $faqPage?->title ?? 'Frequently asked questions' }}</h2>
@include('partials.landing-faq-contact')
      </div>
      <div>
@include('partials.landing-faq-accordion')
      </div>
    </div>
  </section>

  <section data-screen-label="Final CTA" style="position: relative; z-index: 5; padding: 64px 72px 72px; border-top: 1px solid rgba(150,235,250,0.08); text-align: center; overflow: hidden;">
    <div style="position: absolute; bottom: -280px; left: 50%; width: 900px; height: 520px; margin-left: -450px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.6 0.13 198 / 0.34), transparent 74%); filter: blur(24px);"></div>
    <div style="position: relative;">
      <h2 style="margin: 0 auto; max-width: 720px; font-size: 56px; line-height: 1.06; letter-spacing: -0.04em; font-weight: 600; color: #f2fdff;">Start investing today</h2>
      <p style="margin: 22px auto 0; max-width: 500px; font-size: 16.5px; line-height: 1.6; color: rgba(230,244,250,0.74);">Top up USDT, buy a plan, and track daily profit in your dashboard.</p>
      <div style="display: flex; justify-content: center; gap: 14px; margin-top: 36px;">
@include('partials.landing-final-cta')
      </div>
    </div>
  </section>

  <footer class="coin-landing-footer" data-screen-label="Footer">
    <div class="coin-landing-footer__grid">
      <div class="coin-landing-footer__brand">
        <div class="coin-footer-brand">
          <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-brand-logo" />
        </div>
        <p class="coin-landing-footer__tagline">USDT investment platform with daily profit accrual.</p>
        @include('partials.landing-footer-company', ['companyLegal' => $companyLegal])
      </div>
      <div class="coin-landing-footer__links">
        <div class="coin-landing-footer__col">
          <div class="coin-landing-footer__heading">PRODUCT</div>
          <div class="coin-landing-footer__nav">
            <a href="#how">How it works</a>
            <a href="#plans">Plans</a>
            <a href="#infra">Security</a>
            <a href="/dashboard">Dashboard</a>
          </div>
        </div>
        <div class="coin-landing-footer__col">
          <div class="coin-landing-footer__heading">LEGAL</div>
          <div class="coin-landing-footer__nav">
            @foreach($legalPages as $legalPage)
              <a href="{{ route('legal.show', $legalPage) }}">{{ $legalPage->slugLabel() }}</a>
            @endforeach
          </div>
        </div>
        <div class="coin-landing-footer__col">
          <div class="coin-landing-footer__heading">SUPPORT</div>
          <div class="coin-landing-footer__nav">
            <a href="{{ ($faqPage ?? null) ? route('legal.show', $faqPage) : '#faq' }}">{{ __('coin.legal.slugs.faq') }}</a>
            <a href="#support">Help</a>
@include('partials.landing-footer-contact', ['companyLegal' => $companyLegal])
          </div>
        </div>
        <div class="coin-landing-footer__col">
          <div class="coin-landing-footer__heading">SOCIAL</div>
          <div class="coin-landing-footer__nav">
            <a href="#">Telegram</a>
            <a href="#">X</a>
            <a href="#">LinkedIn</a>
          </div>
        </div>
      </div>
    </div>
    <div class="coin-landing-footer__bottom">
      @php
          $footerCompany = trim($companyLegal['company_name'] ?? '');
          $footerReg = trim($companyLegal['company_registration_number'] ?? '');
      @endphp
      <span>
          © {{ date('Y') }} {{ $footerCompany !== '' ? $footerCompany : 'CloudFlops' }}.
          @if($footerReg !== '')
              {{ __('coin.footer.registration_number') }} {{ $footerReg }}.
          @endif
      </span>
      <span class="coin-landing-footer__status">NETWORK ONLINE</span>
    </div>
  </footer>
</div>

<button type="button" id="coin-scroll-top" class="coin-scroll-top" aria-label="Наверх" title="Наверх">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="M12 19V5"></path>
    <path d="M5 12l7-7 7 7"></path>
  </svg>
</button>

@livewire('guest-support-chat')
@livewireScripts
@vite(['resources/js/guest-support.js'])
</body>
</html>