<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <title>{{ \App\Support\PlatformBrand::pageTitle(__('coin.seo_hubs.invest.title')) }}</title>
    @include('partials.coin-seo-meta', [
        'title' => \App\Support\PlatformBrand::pageTitle(__('coin.seo_hubs.invest.title')),
        'description' => __('coin.seo_hubs.invest.meta_description', ['brand' => \App\Support\PlatformBrand::name()]),
        'canonical' => route('seo.invest'),
        'jsonLd' => $jsonLd ?? [],
    ])
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <style>
      body { margin: 0; background: #04101c; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      .coin-hub-shell { width: 100%; max-width: 860px; margin: 0 auto; padding: calc(32px + env(safe-area-inset-top, 0px)) calc(24px + env(safe-area-inset-right, 0px)) calc(72px + env(safe-area-inset-bottom, 0px)) calc(24px + env(safe-area-inset-left, 0px)); box-sizing: border-box; }
      .coin-hub-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 36px; padding-bottom: 22px; border-bottom: 1px solid rgba(150,235,250,0.12); }
      .coin-brand-logo { width: min(250px, 58vw); height: auto; max-height: 56px; display: block; object-fit: contain; }
      .coin-hub-back { font-size: 14px; color: rgba(230,244,250,0.78); }
      .coin-hub-kicker { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195); }
      .coin-hub-title { margin: 14px 0 0; font-size: clamp(28px, 4vw, 40px); line-height: 1.12; letter-spacing: -0.03em; font-weight: 600; color: #f0fbff; }
      .coin-hub-lead { margin: 18px 0 0; font-size: 17px; line-height: 1.65; color: rgba(230,244,250,0.82); }
      .coin-hub-section { margin-top: 36px; }
      .coin-hub-section h2 { margin: 0; font-size: 22px; line-height: 1.25; font-weight: 600; color: #f0fbff; }
      .coin-hub-section p, .coin-hub-section li { font-size: 15.5px; line-height: 1.7; color: rgba(230,244,250,0.8); }
      .coin-hub-section ul { margin: 14px 0 0; padding-left: 1.2em; }
      .coin-hub-section li + li { margin-top: 8px; }
      .coin-hub-cta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
      .coin-hub-cta__primary { display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; background: oklch(0.78 0.12 195); color: #04101c; font-weight: 600; font-size: 14px; }
      .coin-hub-cta__primary:hover { color: #04101c; filter: brightness(1.05); }
      .coin-hub-cta__secondary { display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); color: #e6f4fa; font-size: 14px; }
      .coin-hub-note { margin-top: 28px; padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); font-size: 13.5px; line-height: 1.6; color: rgba(230,244,250,0.7); }
      .coin-hub-nav { display: flex; flex-wrap: wrap; gap: 10px 18px; margin-top: 40px; padding-top: 24px; border-top: 1px solid rgba(150,235,250,0.1); font-size: 13.5px; }
    </style>
</head>
<body>
@include('partials.page-loading-overlay')
<script src="{{ asset('coin/page-navigate.js') }}" defer></script>

<div class="coin-hub-shell">
    <div class="coin-hub-top">
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="coin-hub-brand">
            <img src="{{ \App\Support\PlatformBrand::logo('horizontal') }}" alt="{{ \App\Support\PlatformBrand::name() }}" class="coin-brand-logo" />
        </a>
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="coin-hub-back">{{ __('coin.legal.back_home') }}</a>
    </div>

    <div class="coin-hub-kicker">{{ __('coin.seo_hubs.invest.kicker') }}</div>
    <h1 class="coin-hub-title">{{ __('coin.seo_hubs.invest.heading', ['brand' => \App\Support\PlatformBrand::name()]) }}</h1>
    <p class="coin-hub-lead">{{ __('coin.seo_hubs.invest.lead', ['brand' => \App\Support\PlatformBrand::name()]) }}</p>

    <section class="coin-hub-section">
        <h2>{{ __('coin.seo_hubs.invest.what_title') }}</h2>
        <p style="margin: 14px 0 0;">{{ __('coin.seo_hubs.invest.what_body', ['brand' => \App\Support\PlatformBrand::name()]) }}</p>
    </section>

    <section class="coin-hub-section">
        <h2>{{ __('coin.seo_hubs.invest.how_title') }}</h2>
        <ul>
            <li>{{ __('coin.seo_hubs.invest.how_1') }}</li>
            <li>{{ __('coin.seo_hubs.invest.how_2') }}</li>
            <li>{{ __('coin.seo_hubs.invest.how_3') }}</li>
            <li>{{ __('coin.seo_hubs.invest.how_4') }}</li>
            <li>{{ __('coin.seo_hubs.invest.how_5') }}</li>
        </ul>
    </section>

    <section class="coin-hub-section">
        <h2>{{ __('coin.seo_hubs.invest.model_title') }}</h2>
        <p style="margin: 14px 0 0;">{{ __('coin.seo_hubs.invest.model_body', ['brand' => \App\Support\PlatformBrand::name()]) }}</p>
    </section>

    <section class="coin-hub-section">
        <h2>{{ __('coin.seo_hubs.invest.not_title') }}</h2>
        <p style="margin: 14px 0 0;">{{ __('coin.seo_hubs.invest.not_body', ['brand' => \App\Support\PlatformBrand::name()]) }}</p>
    </section>

    <section class="coin-hub-section">
        <h2>{{ __('coin.seo_hubs.invest.risks_title') }}</h2>
        <p style="margin: 14px 0 0;">
            {{ __('coin.seo_hubs.invest.risks_body') }}
            <a href="{{ route('legal.show', ['legalPage' => 'risks']) }}">{{ __('coin.legal.slugs.risks') }}</a>.
        </p>
    </section>

    <div class="coin-hub-cta">
        <a href="{{ route('register') }}" class="coin-hub-cta__primary">{{ __('coin.seo_hubs.invest.cta_start') }}</a>
        <a href="{{ route('home') }}#plans" class="coin-hub-cta__secondary">{{ __('coin.seo_hubs.invest.cta_plans') }}</a>
    </div>

    <p class="coin-hub-note">{{ __('coin.seo_hubs.invest.disclaimer') }}</p>

    <nav class="coin-hub-nav" aria-label="{{ __('coin.seo_hubs.nav_label') }}">
        <a href="{{ route('home') }}">{{ __('coin.seo_hubs.nav_home') }}</a>
        <a href="{{ route('legal.show', ['legalPage' => 'about']) }}">{{ __('coin.legal.slugs.about') }}</a>
        <a href="{{ route('legal.show', ['legalPage' => 'faq']) }}">{{ __('coin.legal.slugs.faq') }}</a>
        <a href="{{ route('legal.show', ['legalPage' => 'risks']) }}">{{ __('coin.legal.slugs.risks') }}</a>
    </nav>
</div>

@livewire('guest-support-chat')
@livewireStyles
@if(config('coin.turnstile.enabled') && filled(config('coin.turnstile.site_key')))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
@endif
@vite(['resources/js/guest-support.js'])
@include('partials.coin-reverb-config-guest')
@livewireScripts
</body>
</html>
