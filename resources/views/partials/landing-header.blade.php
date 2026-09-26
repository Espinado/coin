<div class="coin-nav-overlay" aria-hidden="true"></div>

<header class="coin-header coin-landing-header" data-screen-label="Header">
  <div class="coin-landing-header__inner">
    <a href="{{ route('home') }}" class="coin-header-brand">
      <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-brand-logo coin-landing-header__logo" />
    </a>

    <nav class="coin-nav coin-nav-desktop" aria-label="{{ __('coin.nav.main') }}">
      <a href="#product">{{ __('coin.landing.nav_product') }}</a>
      <a href="#how">{{ __('coin.landing.nav_how') }}</a>
      <a href="#plans">{{ __('coin.landing.nav_plans') }}</a>
      <a href="#infra">{{ __('coin.landing.nav_infra') }}</a>
      <a href="{{ url('/dashboard') }}">{{ __('coin.landing.nav_dashboard') }}</a>
      <a href="#faq">{{ __('coin.landing.nav_faq') }}</a>
    </nav>

    <div class="coin-header-actions coin-hide-mobile">
      <a href="{{ route('login') }}" class="coin-header-login">{{ __('coin.landing.nav_login') }}</a>
      <a href="{{ route('register') }}" class="coin-header-cta">{{ __('coin.landing.nav_start') }}</a>
    </div>

    <button type="button" class="coin-burger" aria-label="{{ __('coin.landing.aria_open_menu') }}"><span></span><span></span><span></span></button>
  </div>
</header>

<nav class="coin-nav coin-nav-mobile coin-landing-drawer" aria-label="{{ __('coin.nav.main') }}">
  <div class="coin-landing-drawer__head">
    <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-landing-drawer__logo" />
    <button type="button" class="coin-nav-mobile-close" aria-label="{{ __('coin.landing.aria_close_menu') }}">&times;</button>
  </div>
  <a href="#product" class="coin-landing-drawer__link">{{ __('coin.landing.nav_product') }}</a>
  <a href="#how" class="coin-landing-drawer__link">{{ __('coin.landing.nav_how') }}</a>
  <a href="#plans" class="coin-landing-drawer__link">{{ __('coin.landing.nav_plans') }}</a>
  <a href="#infra" class="coin-landing-drawer__link">{{ __('coin.landing.nav_infra') }}</a>
  <a href="{{ url('/dashboard') }}" class="coin-landing-drawer__link">{{ __('coin.landing.nav_dashboard') }}</a>
  <a href="#faq" class="coin-landing-drawer__link">{{ __('coin.landing.nav_faq') }}</a>
  <div class="coin-landing-drawer__divider" aria-hidden="true"></div>
  <a href="{{ route('login') }}" class="coin-landing-drawer__cta">{{ __('coin.landing.nav_login') }}</a>
  <a href="{{ route('register') }}" class="coin-landing-drawer__cta">{{ __('coin.landing.nav_start') }}</a>
</nav>
