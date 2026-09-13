<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Coin' }}</title>
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}" />
    <style>
      body { margin: 0; background: #04101c; -webkit-font-smoothing: antialiased; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      @keyframes dbPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
      input[type="range"] { accent-color: oklch(0.8 0.13 192); }
    </style>
</head>
<body>
    {{ $slot }}
    @auth
        <script>
            window.coinReverb = @json([
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => env('VITE_REVERB_HOST', env('REVERB_HOST', 'localhost')),
                'port' => (int) env('VITE_REVERB_PORT', env('REVERB_PORT', 8080)),
                'scheme' => env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'http')),
            ]);
        </script>
    @endauth
    @vite(['resources/js/app.js'])
    @livewireScripts
    <script src="{{ asset('coin/mobile.js') }}" defer></script>
</body>
</html>
