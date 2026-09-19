<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
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
        .admin-badge { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: #ffb454; border: 1px solid rgba(255,180,84,0.35); padding: 4px 8px; border-radius: 6px; }
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
        .admin-sidebar-brand__logo,
        .admin-topbar-brand__logo { max-width: 100%; max-height: 44px; height: auto; width: auto; object-fit: contain; }
        .admin-topbar-brand { max-width: min(340px, 72vw); }
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
<body @class(['admin-shell', 'admin-shell--sidebar' => auth('admin')->check() && ! View::hasSection('topbar')])>
    <script>
    (function () {
        document.documentElement.classList.remove('coin-page-navigating');
        document.documentElement.classList.remove('admin-sidebar-open');
        var pageOverlay = document.getElementById('coin-page-loading-overlay');
        if (pageOverlay) {
            pageOverlay.hidden = true;
        }
        try {
            sessionStorage.removeItem('coinPageNavigating');
        } catch (_) {}
    })();
    </script>
    @include('partials.page-loading-overlay')

    @hasSection('topbar')
        <div class="admin-shell">
            @yield('topbar')
            @include('admin.partials.flash-toast')
            <main class="admin-content admin-content--auth">
                @yield('content')
            </main>
        </div>
    @else
        @auth('admin')
            <div class="admin-app">
                <div class="admin-sidebar-overlay" id="admin-sidebar-overlay" hidden></div>
                <div class="admin-main">
                    <header class="admin-main-header">
                        <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-expanded="false" aria-controls="admin-sidebar">
                            <span></span><span></span><span></span>
                            <span class="admin-sidebar-toggle__label">{{ __('coin.admin.open_menu') }}</span>
                        </button>
                    </header>
                    @include('admin.partials.flash-toast')
                    <main class="admin-content">
                        @yield('content')
                    </main>
                </div>
                @include('admin.partials.sidebar')
            </div>
        @else
            <div class="admin-shell">
                @include('admin.partials.flash-toast')
                <main class="admin-content admin-content--auth">
                    @yield('content')
                </main>
            </div>
        @endauth
    @endif

    @include('admin.partials.sweetalert')
    @stack('scripts')
    @auth('admin')
        @vite(['resources/js/admin-support-realtime.js'])
    @endauth
    <script src="{{ asset('coin/page-navigate.js') }}?v={{ file_exists(public_path('coin/page-navigate.js')) ? filemtime(public_path('coin/page-navigate.js')) : 1 }}" defer></script>
    @auth('admin')
        @unless(View::hasSection('topbar'))
            <script>
                (function () {
                    const root = document.documentElement;
                    const toggle = document.getElementById('admin-sidebar-toggle');
                    const overlay = document.getElementById('admin-sidebar-overlay');

                    function setOpen(open) {
                        root.classList.toggle('admin-sidebar-open', open);
                        document.body.style.overflow = open ? 'hidden' : '';
                        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                        if (overlay) {
                            overlay.hidden = ! open;
                        }
                    }

                    toggle?.addEventListener('click', function () {
                        setOpen(! root.classList.contains('admin-sidebar-open'));
                    });

                    overlay?.addEventListener('click', function () {
                        setOpen(false);
                    });

                    document.getElementById('admin-sidebar')?.querySelectorAll('a.admin-sidebar-link').forEach(function (link) {
                        link.addEventListener('click', function () {
                            if (window.matchMedia('(max-width: 1024px)').matches) {
                                setOpen(false);
                            }
                        });
                    });

                    setOpen(false);

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            setOpen(false);
                        }
                    });

                    window.addEventListener('resize', function () {
                        if (window.innerWidth > 1024) {
                            setOpen(false);
                        }
                    });
                })();
            </script>
        @endunless
    @endauth
</body>
</html>
