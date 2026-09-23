<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <meta name="robots" content="noindex, nofollow">
    <title>{{ \App\Support\PlatformBrand::pageTitle(__('coin.maintenance.title')) }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <style>
      body {
        margin: 0;
        min-height: 100vh;
        min-height: 100dvh;
        display: grid;
        place-items: center;
        padding: 24px;
        box-sizing: border-box;
        background: radial-gradient(circle at top, rgba(18, 52, 78, 0.55), transparent 42%), #04101c;
        color: #e6f4fa;
        font-family: 'Sora', sans-serif;
        -webkit-font-smoothing: antialiased;
      }
      .coin-maintenance {
        width: min(100%, 520px);
        text-align: center;
        padding: 36px 28px;
        border-radius: 20px;
        border: 1px solid rgba(150,235,250,0.16);
        background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98));
        box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75);
      }
      .coin-maintenance__logo {
        width: min(220px, 60vw);
        height: auto;
        margin: 0 auto 24px;
        display: block;
      }
      .coin-maintenance__eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(214,238,248,0.62);
      }
      .coin-maintenance__title {
        margin: 12px 0 0;
        font-size: clamp(24px, 5vw, 30px);
        font-weight: 600;
        color: #f0fbff;
      }
      .coin-maintenance__body {
        margin: 14px 0 0;
        font-size: 15px;
        line-height: 1.6;
        color: rgba(214,238,248,0.78);
      }
    </style>
</head>
<body>
  <main class="coin-maintenance">
    <img src="{{ asset('cloudflops/logo-horizontal.png') }}" alt="{{ \App\Support\PlatformBrand::name() }}" class="coin-maintenance__logo" />
    <div class="coin-maintenance__eyebrow">{{ __('coin.maintenance.eyebrow') }}</div>
    <h1 class="coin-maintenance__title">{{ __('coin.maintenance.title') }}</h1>
    <p class="coin-maintenance__body">{{ __('coin.maintenance.body') }}</p>
  </main>
</body>
</html>
