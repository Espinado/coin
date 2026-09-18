@props([
    'title' => '',
    'legalNav' => null,
    'currentPage' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <title>{{ \App\Support\PlatformBrand::pageTitle($title) }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <style>
      body { margin: 0; background: #04101c; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      .coin-legal-shell { width: 100%; max-width: 860px; margin: 0 auto; padding: calc(32px + env(safe-area-inset-top, 0px)) calc(24px + env(safe-area-inset-right, 0px)) calc(72px + env(safe-area-inset-bottom, 0px)) calc(24px + env(safe-area-inset-left, 0px)); box-sizing: border-box; }
      .coin-legal-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 36px; padding-bottom: 22px; border-bottom: 1px solid rgba(150,235,250,0.12); }
      .coin-brand-logo { width: min(220px, 54vw); height: auto; max-height: 48px; display: block; object-fit: contain; }
      .coin-legal-back { font-size: 14px; color: rgba(230,244,250,0.78); }
      .coin-legal-kicker { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195); }
      .coin-legal-title { margin: 14px 0 0; font-size: clamp(28px, 4vw, 40px); line-height: 1.12; letter-spacing: -0.03em; font-weight: 600; color: #f0fbff; }
      .coin-legal-body { margin-top: 28px; font-size: 16px; line-height: 1.72; color: rgba(230,244,250,0.82); white-space: pre-wrap; word-break: break-word; }
      .coin-legal-nav { display: flex; flex-wrap: wrap; gap: 10px 18px; margin-top: 40px; padding-top: 24px; border-top: 1px solid rgba(150,235,250,0.1); font-size: 13.5px; }
    </style>
</head>
<body>
@include('partials.page-loading-overlay')
<script src="{{ asset('coin/page-navigate.js') }}" defer></script>

<div class="coin-legal-shell">
    <div class="coin-legal-top">
        <a href="{{ route('home') }}" class="coin-legal-brand">
            <img src="/cloudflops/logo-horizontal.png" alt="CloudFlops" class="coin-brand-logo" />
        </a>
        <a href="{{ route('home') }}" class="coin-legal-back">{{ __('coin.legal.back_home') }}</a>
    </div>

    {{ $slot }}

    @if($legalNav && count($legalNav))
        <nav class="coin-legal-nav" aria-label="{{ __('coin.admin.legal.title') }}">
            @foreach($legalNav as $navPage)
                <a href="{{ route('legal.show', $navPage) }}" @if($currentPage && $currentPage->is($navPage)) style="color:#f0fbff;font-weight:600;" @endif>{{ $navPage->slugLabel() }}</a>
            @endforeach
        </nav>
    @endif
</div>

@livewire('guest-support-chat')
@livewireStyles
@vite(['resources/js/guest-support.js'])
@include('partials.coin-reverb-config-guest')
@livewireScripts
</body>
</html>
