<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? \App\Support\PlatformBrand::name() }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <style>
      body { margin: 0; background: #04101c; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
      @media (min-width: 769px) {
        .coin-sidebar {
          position: fixed !important;
          top: 0;
          bottom: 0;
          left: max(0px, calc((100vw - min(1440px, 100vw)) / 2));
          z-index: 40;
          display: flex !important;
          flex-direction: column !important;
        }
        .coin-shell { padding-left: 248px; }
      }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      @keyframes dbPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
      input[type="range"] { accent-color: oklch(0.8 0.13 192); }
      .coin-sidebar-brand__logo { max-width: 100%; max-height: 52px; height: auto; width: auto; object-fit: contain; }
      .coin-brand-logo { width: min(260px, 54vw); height: auto; max-height: 56px; display: block; object-fit: contain; }
      .coin-contract-stats-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 20px;
        margin-top: 24px;
      }
      .coin-contract-stats-grid--4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
      .coin-contract-stat { min-width: 0; }
      .coin-contract-stat__label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9.5px;
        letter-spacing: 0.12em;
        color: rgba(214, 238, 248, 0.68);
        line-height: 1.35;
        word-break: break-word;
      }
      .coin-contract-stat__value {
        margin-top: 9px;
        font-size: 14px;
        line-height: 1.35;
        word-break: break-word;
      }
      .coin-contract-stat__value--accent { color: oklch(0.9 0.12 192); }
      @media (max-width: 1024px) {
        .coin-contract-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .coin-contract-stats-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      }
      @media (max-width: 768px) {
        .coin-contract-stats-grid,
        .coin-contract-stats-grid--4 {
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 14px 12px;
        }
        .coin-contract-card { padding: 16px !important; }
        .coin-contract-card__actions { width: 100%; flex-direction: column; }
        .coin-contract-card__actions button { width: 100%; box-sizing: border-box; }
      }
    </style>
</head>
<body>
    @include('partials.page-loading-overlay')
    {{ $slot }}
    @auth
        @include('partials.coin-reverb-config')
    @endauth
    @vite(['resources/js/app.js'])
    @livewireScripts
    <script src="{{ asset('coin/page-navigate.js') }}?v={{ file_exists(public_path('coin/page-navigate.js')) ? filemtime(public_path('coin/page-navigate.js')) : 1 }}" defer></script>
    <script src="{{ asset('coin/mobile.js') }}?v={{ file_exists(public_path('coin/mobile.js')) ? filemtime(public_path('coin/mobile.js')) : 1 }}" defer></script>
</body>
</html>
