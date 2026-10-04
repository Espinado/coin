<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.coin-ios-meta')
    <title>{{ $title ?? \App\Support\PlatformBrand::name() }}</title>
    @include('partials.coin-seo-meta', [
        'title' => $title ?? \App\Support\PlatformBrand::name(),
        'description' => __('coin.seo.auth_meta_description', ['brand' => \App\Support\PlatformBrand::name()]),
        'noindex' => true,
    ])
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}?v={{ file_exists(public_path('coin/responsive.css')) ? filemtime(public_path('coin/responsive.css')) : 1 }}" />
    <style>
      body { margin: 0; background: #061423; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
      .coin-auth { width: 100%; max-width: none; min-height: 100vh; box-sizing: border-box; }
      .coin-auth-brand { display: inline-flex; align-items: center; justify-content: center; max-width: min(320px, 86vw); }
      .coin-auth-brand__logo { max-width: 100%; max-height: 52px; height: auto; width: auto; object-fit: contain; }
      .coin-brand-logo { width: min(300px, 58vw); height: auto; max-height: 66px; display: block; object-fit: contain; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      input::placeholder { color: rgba(230,244,250,0.42); }
      input:focus { outline: none; }
      .coin-auth-error { margin-top: 8px; font-size: 13px; font-weight: 600; line-height: 1.35; color: #ff4d4f; }
      .coin-auth-legal-links .coin-legal-links__sep { color: rgba(230,244,250,0.35); }
      .coin-phone-country { position: relative; flex: 0 0 auto; min-width: 132px; }
      .coin-phone-country__trigger {
        display: inline-flex; align-items: center; gap: 8px; width: 100%; height: 100%; min-height: 52px;
        box-sizing: border-box; padding: 10px 12px; border-radius: 12px;
        border: 1px solid var(--coin-phone-border, rgba(150,235,250,0.18));
        background: rgba(4,16,28,0.7); color: #f0fbff; font-family: inherit; font-size: 15px; cursor: pointer;
      }
      .coin-phone-country__flag { width: 22px; height: 16px; object-fit: cover; border-radius: 2px; flex-shrink: 0; box-shadow: 0 0 0 1px rgba(255,255,255,0.12); }
      .coin-phone-country__dial { font-variant-numeric: tabular-nums; white-space: nowrap; }
      .coin-phone-country__name-label { flex: 1; min-width: 0; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .coin-country-select { display: block; width: 100%; min-width: 0; }
      .coin-country-select .coin-phone-country__menu { width: 100%; }
      .coin-phone-country__chevron {
        margin-left: auto; width: 0; height: 0; flex-shrink: 0;
        border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid rgba(230,244,250,0.7);
      }
      .coin-phone-country__menu {
        position: absolute; z-index: 40; top: calc(100% + 6px); left: 0; width: min(320px, 82vw); max-height: 280px; overflow: auto;
        border-radius: 12px; border: 1px solid rgba(150,235,250,0.18); background: #0b1c2c;
        box-shadow: 0 24px 48px -24px rgba(0,0,0,0.85); padding: 6px;
      }
      .coin-phone-country__search-wrap {
        position: sticky; top: 0; z-index: 1; margin: -6px -6px 6px; padding: 6px;
        background: #0b1c2c; border-bottom: 1px solid rgba(150,235,250,0.12);
      }
      .coin-phone-country__search {
        width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px;
        border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.85); color: #f0fbff;
        font-family: inherit; font-size: 14px;
      }
      .coin-phone-country__search::placeholder { color: rgba(230,244,250,0.42); }
      .coin-phone-country__empty {
        padding: 12px 10px; color: rgba(230,244,250,0.55); font-size: 13px; text-align: center;
      }
      .coin-phone-country__option {
        display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 10px; border: 0; border-radius: 8px;
        background: transparent; color: #e6f4fa; font-family: inherit; font-size: 13.5px; text-align: left; cursor: pointer;
      }
      .coin-phone-country__option[hidden] { display: none !important; }
      .coin-phone-country__option:hover,
      .coin-phone-country__option.is-selected { background: rgba(150,235,250,0.1); }
      .coin-phone-country__name { flex: 1; min-width: 0; }
      .coin-phone-country__code { color: rgba(230,244,250,0.62); font-variant-numeric: tabular-nums; }
      @media (max-width: 520px) {
        .coin-auth-phone { flex-direction: column !important; }
        .coin-phone-country { width: 100%; min-width: 0; }
        .coin-phone-country__menu { width: 100%; }
      }
      .coin-auth-status { margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.35); background: oklch(0.6 0.13 200 / 0.15); color: #eafcff; font-size: 13px; }
    </style>
</head>
<body>
    @include('partials.page-loading-overlay')
    {{ $slot }}
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-password-toggle]');

            if (! button) {
                return;
            }

            const form = button.closest('form');

            if (! form) {
                return;
            }

            const inputs = form.querySelectorAll('.js-password-input');
            const reveal = Array.from(inputs).some(function (input) {
                return input.type === 'password';
            });

            inputs.forEach(function (input) {
                input.type = reveal ? 'text' : 'password';
            });

            button.textContent = reveal
                ? button.dataset.hideLabel
                : button.dataset.showLabel;
        });
    </script>
    <script src="{{ asset('coin/page-navigate.js') }}?v={{ file_exists(public_path('coin/page-navigate.js')) ? filemtime(public_path('coin/page-navigate.js')) : 1 }}" defer></script>
    <script src="{{ asset('coin/phone-country.js') }}?v={{ file_exists(public_path('coin/phone-country.js')) ? filemtime(public_path('coin/phone-country.js')) : 1 }}" defer></script>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
