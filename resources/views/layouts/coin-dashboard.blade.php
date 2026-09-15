<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Coin' }}</title>
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <style>
      body { margin: 0; background: #04101c; -webkit-font-smoothing: antialiased; }
      @media (min-width: 769px) {
        .coin-sidebar { position: fixed !important; top: 0; bottom: 0; left: max(0px, calc((100vw - min(1440px, 100vw)) / 2)); z-index: 40; }
        .coin-shell { padding-left: 248px; }
      }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      @keyframes dbPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
      input[type="range"] { accent-color: oklch(0.8 0.13 192); }
    </style>
</head>
<body>
    {{ $slot }}
    @auth
        @include('partials.coin-reverb-config')
    @endauth
    @vite(['resources/js/app.js'])
    @livewireScripts
    <script src="{{ asset('coin/mobile.js') }}" defer></script>
</body>
</html>
