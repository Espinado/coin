<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\PlatformBrand::pageTitle(__('coin.landing.meta_title_suffix')) }}</title>
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

<button type="button" id="coin-scroll-top" class="coin-scroll-top" aria-label="{{ __('coin.landing.scroll_top') }}" title="{{ __('coin.landing.scroll_top') }}">
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
