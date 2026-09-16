<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Support\PlatformBrand::adminName())</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/responsive.css') }}?v={{ file_exists(public_path('admin/responsive.css')) ? filemtime(public_path('admin/responsive.css')) : 1 }}">
    <style>
        body { margin: 0; font-family: 'Sora', sans-serif; background: #0c0f14; color: #e8edf5; -webkit-font-smoothing: antialiased; }
        a { color: #9db4ff; text-decoration: none; }
        a:hover { color: #c5d4ff; }
        .admin-shell { min-height: 100vh; }
        .admin-topbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 28px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); }
        .admin-badge { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: #ffb454; border: 1px solid rgba(255,180,84,0.35); padding: 4px 8px; border-radius: 6px; }
        .admin-content { padding: 28px; max-width: 1200px; margin: 0 auto; }
        .admin-card { padding: 24px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); }
        button, .admin-btn { cursor: pointer; font-family: inherit; }
        .admin-btn { padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color: #e8edf5; font-size: 13px; }
        .admin-btn-primary { border-color: rgba(255,180,84,0.45); background: linear-gradient(140deg, #ffb454, #e8872e); color: #1a1208; font-weight: 600; }
        input { font-family: inherit; }
        .admin-support-badge {
            animation: adminSupportPulse 2s ease-in-out infinite;
        }
        @keyframes adminSupportPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 14px rgba(255,180,84,0.45); }
            50% { transform: scale(1.06); box-shadow: 0 0 20px rgba(255,180,84,0.65); }
        }
    </style>
    @stack('head')
    @auth('admin')
        @include('partials.coin-reverb-config')
        <script>
            window.coinAdminSupport = {
                unreadUrl: @json(route('admin.support.unread-count')),
            };
        </script>
    @endauth
</head>
<body class="admin-shell">
    @include('partials.page-loading-overlay')
    <div class="admin-shell">
        @hasSection('topbar')
            @yield('topbar')
        @else
            <header class="admin-topbar">
                <a href="{{ route('admin.dashboard') }}" class="admin-topbar-brand">
                    <x-brand-logo variant="horizontal" :height="44" class="admin-topbar-brand__logo" />
                    <span class="admin-badge">STAFF ONLY</span>
                </a>
                @auth('admin')
                    <div class="admin-topbar-actions">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="admin-btn">Log out</button>
                        </form>
                    </div>
                @endauth
            </header>
        @endif
        @include('admin.partials.flash-toast')
        <main class="admin-content">
            @yield('content')
        </main>
    </div>
    @include('admin.partials.sweetalert')
    @stack('scripts')
    @auth('admin')
        @vite(['resources/js/admin-support-realtime.js'])
    @endauth
    <script src="{{ asset('coin/page-navigate.js') }}" defer></script>
</body>
</html>
