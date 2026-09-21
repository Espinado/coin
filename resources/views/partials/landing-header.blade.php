<div class="coin-nav-overlay" aria-hidden="true"></div>

<header class="coin-header coin-landing-header" data-screen-label="Header">
  <div class="coin-landing-header__inner">
    <a href="{{ route('home') }}" class="coin-header-brand">
      <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-brand-logo coin-landing-header__logo" />
    </a>

    <nav class="coin-nav coin-nav-desktop" aria-label="{{ __('coin.nav.main') }}">
      <a href="#product">Продукт</a>
      <a href="#how">Как это работает</a>
      <a href="#plans">Тарифы</a>
      <a href="#infra">Инфраструктура</a>
      <a href="{{ url('/dashboard') }}">Личный кабинет</a>
      <a href="#faq">FAQ</a>
    </nav>

    <div class="coin-header-actions coin-hide-mobile">
      <a href="{{ route('login') }}" class="coin-header-login">Войти</a>
      <a href="{{ route('register') }}" class="coin-header-cta">Начать</a>
    </div>

    <button type="button" class="coin-burger" aria-label="Открыть меню"><span></span><span></span><span></span></button>
  </div>
</header>

<nav class="coin-nav coin-nav-mobile coin-landing-drawer" aria-label="{{ __('coin.nav.main') }}">
  <div class="coin-landing-drawer__head">
    <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="CloudFlops" class="coin-landing-drawer__logo" />
    <button type="button" class="coin-nav-mobile-close" aria-label="Закрыть меню">&times;</button>
  </div>
  <a href="#product" class="coin-landing-drawer__link">Продукт</a>
  <a href="#how" class="coin-landing-drawer__link">Как это работает</a>
  <a href="#plans" class="coin-landing-drawer__link">Тарифы</a>
  <a href="#infra" class="coin-landing-drawer__link">Инфраструктура</a>
  <a href="{{ url('/dashboard') }}" class="coin-landing-drawer__link">Личный кабинет</a>
  <a href="#faq" class="coin-landing-drawer__link">Вопросы</a>
  <div class="coin-landing-drawer__divider" aria-hidden="true"></div>
  <a href="{{ route('login') }}" class="coin-landing-drawer__cta">Войти</a>
  <a href="{{ route('register') }}" class="coin-landing-drawer__cta coin-landing-drawer__cta--secondary">Создать аккаунт</a>
</nav>
