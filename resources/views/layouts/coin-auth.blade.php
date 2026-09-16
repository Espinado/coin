<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? \App\Support\PlatformBrand::name() }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('coin/responsive.css') }}" />
    <style>
      body { margin: 0; background: #04101c; -webkit-font-smoothing: antialiased; }
      a { color: oklch(0.86 0.11 195); text-decoration: none; }
      a:hover { color: oklch(0.92 0.09 195); }
      input::placeholder { color: rgba(230,244,250,0.42); }
      input:focus { outline: none; }
      .coin-auth-error { margin-top: 8px; font-size: 12.5px; color: oklch(0.78 0.16 25); }
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
    <script src="{{ asset('coin/page-navigate.js') }}" defer></script>
</body>
</html>
