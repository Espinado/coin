<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Support\PlatformBrand::pageTitle('Инвестиционная платформа') }}</title>
    <link rel="icon" href="{{ asset('cloudflops/logo-mark.png') }}" type="image/png" />
    @livewireStyles
    @vite(['resources/js/guest-support.js'])
    @include('partials.coin-reverb-config-guest')
    <script src="{{ asset('coin/support.js') }}"></script>
</head>
<body>
@include('partials.page-loading-overlay')
<script src="{{ asset('coin/page-navigate.js') }}" defer></script>
@verbatim
<x-dc>
<helmet>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
<style>
  body { margin: 0; background: #04101c; -webkit-font-smoothing: antialiased; }
  a { color: oklch(0.86 0.11 195); text-decoration: none; }
  a:hover { color: oklch(0.92 0.09 195); }
  @keyframes paiPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
  input[type="range"] { accent-color: oklch(0.8 0.13 192); }
</style>
<link rel="stylesheet" href="/coin/responsive.css" />
<script src="/coin/mobile.js" defer></script>
</helmet>

<div class="coin-app" style="width: 1440px; margin: 0 auto; position: relative; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; overflow: hidden;">
  <div style="position: absolute; top: -240px; right: -80px; width: 820px; height: 660px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.62 0.13 198 / 0.28), transparent 72%); filter: blur(30px); pointer-events: none;"></div>

  <div class="coin-nav-overlay" onClick="{{ closeMenu }}"></div>
  <header class="coin-header" data-screen-label="Header" style="position: relative; z-index: 20; display: flex; align-items: center; justify-content: space-between; padding: 22px 72px; border-bottom: 1px solid rgba(150,235,250,0.1); background: rgba(6,20,35,0.75); backdrop-filter: blur(14px);">
    <div class="coin-header-brand" style="display: flex; align-items: center;">
      <img src="/cloudflops/logo-horizontal.png" alt="CloudFlops" class="coin-brand-logo" />
    </div>
    <button type="button" class="coin-burger" onClick="{{ toggleMenu }}" aria-label="Open menu"><span></span><span></span><span></span></button>
    <nav class="coin-nav coin-nav-desktop" style="display: flex; align-items: center; gap: 30px;">
      <a href="#product" style="font-size: 14px; color: rgba(230,244,250,0.78);">Продукт</a>
      <a href="#how" style="font-size: 14px; color: rgba(230,244,250,0.78);">Как это работает</a>
      <a href="#plans" style="font-size: 14px; color: rgba(230,244,250,0.78);">Планы</a>
      <a href="#infra" style="font-size: 14px; color: rgba(230,244,250,0.78);">Безопасность</a>
      <a href="/dashboard" style="font-size: 14px; color: rgba(230,244,250,0.78);">Личный кабинет</a>
      <a href="#faq" style="font-size: 14px; color: rgba(230,244,250,0.78);">Вопросы</a>
    </nav>
    <nav class="coin-nav coin-nav-mobile">
      <a href="#product" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Product</a>
      <a href="#how" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">How it works</a>
      <a href="#plans" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Plans</a>
      <a href="#infra" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Security</a>
      <a href="/dashboard" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Dashboard</a>
      <a href="#faq" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">FAQ</a>
    </nav>
    <div class="coin-header-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="/dashboard" style="padding: 11px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Личный кабинет</a>
    </div>
  </header>

  <section data-screen-label="Hero" style="position: relative; z-index: 5; display: grid; grid-template-columns: 1fr 540px; gap: 60px; align-items: center; padding: 96px 72px 104px;">
    <div>
      <div style="display: inline-flex; align-items: center; gap: 10px; padding: 7px 14px; border-radius: 999px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195);">
        <span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); animation: paiPulse 2.4s infinite;"></span>ИНВЕСТИЦИОННАЯ ПЛАТФОРМА
      </div>
      <h1 style="margin: 26px 0 0; font-size: 66px; line-height: 1.06; letter-spacing: -0.04em; font-weight: 600; color: #f0fbff;">Инвестируйте USDT и получайте прибыль каждый день</h1>
      <p style="margin: 24px 0 0; max-width: 520px; font-size: 17px; line-height: 1.62; color: rgba(230,244,250,0.72);">Пополните счёт USDT, купите план с фиксированной доходностью и следите за прибылью в личном кабинете. Тело возвращается в конце срока.</p>
      <div style="display: flex; gap: 14px; margin-top: 36px;">
        <button style="padding: 16px 28px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 15.5px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 50px -22px oklch(0.8 0.13 195 / 0.7);">Начать</button>
        <button style="padding: 16px 26px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.05); color: #e6f4fa; font-family: inherit; font-size: 15.5px; font-weight: 500; cursor: pointer;">Как это работает</button>
      </div>
      <div style="display: flex; gap: 36px; margin-top: 44px; padding-top: 26px; border-top: 1px solid rgba(150,235,250,0.1);">
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">7</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">investment plans</div>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">99,9%</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">network uptime</div>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">24/7</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">monitoring</div>
        </div>
      </div>
    </div>

    <div style="position: relative;">
      <div style="padding: 26px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(20,55,80,0.7), rgba(6,20,35,0.85)); box-shadow: 0 40px 90px -50px #000;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">PORTFOLIO PREVIEW</span>
          <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); animation: paiPulse 2.2s infinite;"></span>ONLINE</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 22px;">
          <div style="padding: 12px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px;">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 84%, rgba(150,235,250,0.12) 84%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 92%, rgba(150,235,250,0.12) 92%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.7 0.15 292) 71%, rgba(150,235,250,0.12) 71%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: rgba(150,235,250,0.3);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(150,235,250,0.14);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: rgba(230,244,250,0.68);">DC 01</div>
          </div>
          <div style="padding: 12px; border-radius: 14px; border: 1px solid oklch(0.86 0.11 195 / 0.3); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px; box-shadow: inset 0 0 26px oklch(0.6 0.13 200 / 0.25);">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.86 0.12 192) 96%, rgba(150,235,250,0.12) 96%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.86 0.12 192) 88%, rgba(150,235,250,0.12) 88%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 93%, rgba(150,235,250,0.12) 93%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 78%, rgba(150,235,250,0.12) 78%);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: oklch(0.88 0.1 205);">DC 02</div>
          </div>
          <div style="padding: 12px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px;">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 67%, rgba(150,235,250,0.12) 67%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.88 0.15 90 / 0.7) 44%, rgba(150,235,250,0.12) 44%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 86%, rgba(150,235,250,0.12) 86%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: rgba(150,235,250,0.3);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(150,235,250,0.14);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: rgba(230,244,250,0.68);">DC 03</div>
          </div>
        </div>

        <div style="margin-top: 20px; padding: 18px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 13.5px; color: rgba(230,244,250,0.78);">Locked principal</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">1,350 USDT</span>
          </div>
          <div style="margin-top: 14px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 74%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
          <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 18px;">
            <span style="font-size: 13.5px; color: rgba(230,244,250,0.78);">Earned today</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: oklch(0.9 0.12 192);">+5,04</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section data-screen-label="Metrics" style="position: relative; z-index: 5; padding: 0 72px 104px;">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">TOTAL AUM</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">12,4 <span style="font-size: 15px; font-weight: 400; color: rgba(230,244,250,0.7);">M USDT</span></div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ACTIVE USERS</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">241 806</div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ACTIVE CONTRACTS</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">18 420</div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.18), rgba(150,235,250,0.03));">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.75);">PROFIT PAID OUT</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">64,2 <span style="font-size: 15px; font-weight: 400; color: rgba(230,244,250,0.75);">M</span></div>
      </div>
    </div>
    <div style="margin-top: 16px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(230,244,250,0.62);">PLACEHOLDER VALUES · REPLACED WITH LIVE DATA</div>
  </section>

  <section id="how" data-screen-label="How it works" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 60px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">HOW IT WORKS</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Four steps</h2>
      </div>
      <p style="max-width: 400px; margin: 0 0 6px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">From account top-up to plan purchase to daily profit. Everything is tracked in your dashboard.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="display: flex; align-items: flex-end; gap: 3px; height: 16px;"><div style="width: 4px; height: 45%; border-radius: 2px; background: rgba(214,238,248,0.4);"></div><div style="width: 4px; height: 72%; border-radius: 2px; background: oklch(0.78 0.11 205);"></div><div style="width: 4px; height: 100%; border-radius: 2px; background: oklch(0.88 0.12 192);"></div></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">01</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Choose a plan</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Each plan defines minimum investment, APR, and contract term.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="width: 17px; height: 17px; border-radius: 5px; background: linear-gradient(145deg, oklch(0.88 0.12 192), oklch(0.62 0.13 210));"></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">02</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Top up account</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Add USDT to your wallet, then invest from available balance.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;"><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.7 0.15 292);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(214,238,248,0.35);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">03</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Principal is locked</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Your investment principal stays locked until the contract matures.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid oklch(0.86 0.11 195 / 0.28); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: oklch(0.6 0.13 200 / 0.25); display: grid; place-items: center;">
            <div style="width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid oklch(0.9 0.11 195);"></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.88 0.11 195 / 0.75);">04</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Earn daily profit</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.75);">Profit accrues daily to your available balance. Request a payout anytime.</p>
      </div>
    </div>
  </section>

  <section id="plans" data-screen-label="Plans" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="text-align: center; max-width: 620px; margin: 0 auto;">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">PLANS</div>
      <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Choose your investment plan</h2>
      <p style="margin: 18px 0 0; font-size: 16px; line-height: 1.6; color: rgba(230,244,250,0.72);">Fixed APR plans with transparent daily profit. Estimates are indicative; terms apply at purchase.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 30px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column;">
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Node</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.7);">Starter volume</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">250 USDT</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.68);">placeholder</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Min investment</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">250 USDT</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">90 days</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">APR</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">12%</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Daily profit est.</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~0.08 / day</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Shared pool</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer;">Select</button>
      </div>

      <div style="position: relative; padding: 30px; border-radius: 20px; border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(150,235,250,0.03)); display: flex; flex-direction: column; box-shadow: 0 30px 70px -44px oklch(0.7 0.14 195 / 0.9);">
        <div style="position: absolute; top: -10px; left: 30px; padding: 4px 11px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: #04121f;">POPULAR</div>
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Core</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.74);">Best balance</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">1,100 USDT</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.7);">placeholder</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.16); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Min investment</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">1,100 USDT</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">180 days</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">APR</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">15%</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Daily profit est.</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~0.45 / day</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Priority pool</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Select</button>
      </div>

      <div style="padding: 30px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column;">
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Cluster</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.7);">Maximum volume</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">3,400 USDT</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.68);">placeholder</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Min investment</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">3,400 USDT</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">365 days</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">APR</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">18%</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Daily profit est.</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~1.68 / day</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Dedicated pods</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer;">Select</button>
      </div>
    </div>

    <div style="margin-top: 20px; padding: 32px 36px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); display: grid; grid-template-columns: 1fr 320px; gap: 48px; align-items: center;">
      <div>
        <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">Profit estimate</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
          <span style="font-size: 13.5px; color: rgba(230,244,250,0.74);">Investment amount</span>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ powerLabel }} <span style="font-size: 12px; color: rgba(230,244,250,0.7);">USDT</span></span>
        </div>
        <input type="range" min="100" max="10000" step="100" value="{{ power }}" onChange="{{ onPower }}" style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;" />
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.65);"><span>100</span><span>5 000</span><span>10 000</span></div>
        <p style="margin: 20px 0 0; font-size: 12px; line-height: 1.55; color: rgba(230,244,250,0.65);">This calculation is indicative only. Actual profit follows plan APR; returns are not guaranteed.</p>
      </div>
      <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(20,55,80,0.75), rgba(6,20,35,0.9));">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">EST. PER DAY</div>
        <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 32px; color: oklch(0.9 0.12 192);">{{ daily }}</div>
        <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px; margin-bottom: 12px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Per month</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ monthly }}</span></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Matching plan</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ planName }}</span></div>
      </div>
    </div>
  </section>

  <section id="product" data-screen-label="Benefits" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">WHY CloudFlops</div>
    <h2 style="margin: 18px 0 0; max-width: 560px; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">A clear investment product</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 4px; background: oklch(0.86 0.12 192);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Account top-ups</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Add USDT to your wallet, then buy investment plans from available balance.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 50%; border: 2px solid oklch(0.86 0.11 195);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Transparent accruals</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Each accrual shows investment, APR, and profit amount.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="display: flex; gap: 4px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.86 0.12 192);"></span><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.7 0.15 292);"></span></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Flexible plans</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Add new investments anytime; each contract has its own term.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 14px; height: 16px; border-radius: 3px; border: 1.5px solid oklch(0.86 0.11 195);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Dashboard</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Investments, wallet, profit history, and referrals in one interface.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 50%; border: 1.5px solid rgba(150,235,250,0.35); border-top-color: oklch(0.88 0.12 192);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Fast payouts</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Fee and processing time are shown before you confirm.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="display: flex; align-items: flex-end; gap: 3px; height: 15px;"><span style="width: 4px; height: 45%; border-radius: 2px; background: rgba(214,238,248,0.4);"></span><span style="width: 4px; height: 72%; border-radius: 2px; background: oklch(0.72 0.11 215);"></span><span style="width: 4px; height: 100%; border-radius: 2px; background: oklch(0.88 0.12 192);"></span></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Analytics and statistics</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Profit trends and portfolio allocation by plan.</p>
      </div>
    </div>
  </section>

  <section id="infra" data-screen-label="Infrastructure" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: grid; grid-template-columns: 1fr 560px; gap: 60px; align-items: center;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">SECURITY</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Secure custody and transparent terms</h2>
        <p style="margin: 22px 0 0; max-width: 470px; font-size: 16.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">Top-ups, investments, daily profit accrual, and maturity release are handled in one system — with full history in your dashboard.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 36px; max-width: 460px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ACTIVE PLANS</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">7</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">TOTAL LOCKED</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">1,7 M</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">AVAILABILITY</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">99,9%</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">MONITORING</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">24/7</div>
          </div>
        </div>
      </div>

      <div style="padding: 28px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.14); background: linear-gradient(170deg, rgba(150,235,250,0.06), rgba(6,20,35,0.5));">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">SITE STATUS</span>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.65);">PLACEHOLDER</span>
        </div>
        <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 12px;">
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Data center 01</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>ONLINE</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 84%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">LOAD 84%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Data center 02</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>ONLINE</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 91%; height: 100%; border-radius: 3px; background: oklch(0.8 0.12 198);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">LOAD 91%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Data center 03</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.9 0.14 90); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>EXPANDING</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 46%; height: 100%; border-radius: 3px; background: oklch(0.88 0.15 90 / 0.8);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">LOAD 46%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px dashed rgba(150,235,250,0.2); background: rgba(150,235,250,0.02); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <span style="font-size: 14px; color: rgba(230,244,250,0.78);">Data center 04 — 07</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.68); flex: none;">DATA TBC</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="account" data-screen-label="Dashboard" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 60px; margin-bottom: 44px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">DASHBOARD</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Everything in one place</h2>
      </div>
      <p style="max-width: 380px; margin: 0 0 6px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">Balance, active investments, plans, wallet, profit history, and referrals — this is what the dashboard looks like.</p>
    </div>

    <div style="border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(180deg, rgba(11,32,49,0.96), rgba(5,16,27,0.98)); box-shadow: 0 60px 120px -60px #000; overflow: hidden;">
      <div style="display: grid; grid-template-columns: 216px 1fr;">
        <aside style="padding: 22px 14px; border-right: 1px solid rgba(150,235,250,0.1); display: flex; flex-direction: column; gap: 3px;">
          <div class="coin-preview-brand" style="display: flex; align-items: center; padding: 0 10px 20px;">
            <img src="/cloudflops/logo-horizontal.png" alt="CloudFlops" class="coin-brand-logo" />
          </div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); font-size: 13px; color: #f0fbff;"><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span>Overview</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Plans</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Contracts</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Statistics</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Wallet</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Referrals</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Settings</div>
        </aside>

        <div style="padding: 24px 26px 28px;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="font-size: 18px; font-weight: 600; letter-spacing: -0.02em;">Overview</div>
              <div style="margin-top: 4px; font-size: 12.5px; color: rgba(230,244,250,0.7);">Account overview · Core plan</div>
            </div>
            <div style="display: flex; gap: 7px;">
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.7);">30D</span>
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.4); background: oklch(0.6 0.13 200 / 0.22); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: #f0fbff;">14D</span>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 22px;">
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">BALANCE</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 482,60</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">LOCKED PRINCIPAL</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 200</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ACTIVE PLAN</div>
              <div style="margin-top: 12px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">Core</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.75);">EARNED TODAY</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: oklch(0.9 0.12 192);">+5,04</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 14px; margin-top: 14px;">
            <div style="padding: 20px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
              <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <span style="font-size: 14px; font-weight: 600;">Accruals</span>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">LAST 14 DAYS</span>
              </div>
              <div style="display: flex; align-items: flex-end; gap: 7px; height: 128px; margin-top: 20px;">
                <div style="flex: 1; height: 34%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 46%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 39%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.72 0.11 210 / 0.8), oklch(0.72 0.11 210 / 0.12));"></div>
                <div style="flex: 1; height: 58%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
                <div style="flex: 1; height: 52%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.76 0.12 202 / 0.85), oklch(0.76 0.12 202 / 0.12));"></div>
                <div style="flex: 1; height: 67%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
                <div style="flex: 1; height: 61%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.8 0.12 198), oklch(0.8 0.12 198 / 0.14));"></div>
                <div style="flex: 1; height: 74%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
                <div style="flex: 1; height: 69%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.84 0.12 195), oklch(0.84 0.12 195 / 0.16));"></div>
                <div style="flex: 1; height: 83%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.18));"></div>
                <div style="flex: 1; height: 78%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.86 0.12 193), oklch(0.86 0.12 193 / 0.18));"></div>
                <div style="flex: 1; height: 91%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.2));"></div>
                <div style="flex: 1; height: 86%; border-radius: 4px; background: linear-gradient(180deg, oklch(0.88 0.12 192), oklch(0.88 0.12 192 / 0.2));"></div>
                <div style="flex: 1; height: 100%; border-radius: 4px; background: linear-gradient(180deg, #eafcff, oklch(0.88 0.12 192 / 0.22));"></div>
              </div>
            </div>

            <div style="padding: 20px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
              <span style="font-size: 14px; font-weight: 600;">Data center status</span>
              <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 14px; font-size: 12.5px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Data center 01</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">84%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Data center 02</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">91%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>Data center 03</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">46%</span></div>
                <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Contracts</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">2 active</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Referrals</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">28</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 14px;">
      <a href="/dashboard" style="padding: 13px 24px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-size: 14px; font-weight: 500;">View dashboard</a>
    </div>
  </section>

  <section data-screen-label="Referrals" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="padding: 44px 48px; border-radius: 22px; border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.1)); display: grid; grid-template-columns: 1fr 360px; gap: 56px; align-items: center;">
      <div>
        <h2 style="margin: 0; font-size: 36px; line-height: 1.1; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Invite and earn more</h2>
        <p style="margin: 16px 0 0; max-width: 480px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.74);">Share your link — earn 20% commission when invited users purchase a plan. Terms are configurable.</p>
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 28px; flex-wrap: wrap;">
          <div style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.28); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #eafcff;">coin/r/<span style="color: oklch(0.88 0.11 195);">COIN-4X9K2</span></div>
          <button style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Invite</button>
        </div>
      </div>
      <div style="padding: 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.16); background: rgba(6,20,35,0.7);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">YOUR NETWORK</div>
        <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">28</span>
          <span style="font-size: 13px; color: rgba(230,244,250,0.72);">invited</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px; margin-bottom: 12px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Active contracts</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">19</span></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Referral earnings</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: oklch(0.9 0.12 192);">+112,40</span></div>
      </div>
    </div>
  </section>

  <section id="faq" data-screen-label="FAQ" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 72px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">FAQ</div>
        <h2 style="margin: 18px 0 0; font-size: 42px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Frequently asked questions</h2>
        <p style="margin: 18px 0 0; font-size: 15px; line-height: 1.62; color: rgba(230,244,250,0.7);">Can't find an answer? <a href="#">Contact us</a>.</p>
      </div>
      <div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle0 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>What is CloudFlops?</span>
            <sc-if value="{{ open0 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed0 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open0 }}" hint-placeholder-val="{{ true }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">An investment platform where you top up USDT, buy a plan with fixed APR, and receive daily profit to your available balance.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle1 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>How do plans work?</span>
            <sc-if value="{{ open1 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed1 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open1 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">A plan defines minimum investment, APR, and contract term. When you buy a plan, principal is locked until maturity.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle2 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Where does profit come from?</span>
            <sc-if value="{{ open2 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed2 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open2 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Daily profit is calculated as principal × APR / 365 and credited to your available balance. At maturity, principal returns to available balance.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle3 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>How do referrals work?</span>
            <sc-if value="{{ open3 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed3 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open3 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">When someone you invited purchases a plan, you receive a one-time commission (default 20%) credited to your available balance.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle4 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>How do I request a payout?</span>
            <sc-if value="{{ open4 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed4 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open4 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Submit a request in the Wallet section of your dashboard. Fee and processing time are shown before confirmation. Specific terms are subject to change.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12); border-bottom: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle5 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>What can I see in the dashboard?</span>
            <sc-if value="{{ open5 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed5 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open5 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Balance, investments, profit history, wallet, payouts, referrals, and support.</p>
          </sc-if>
        </div>
      </div>
    </div>
  </section>

  <section data-screen-label="Final CTA" style="position: relative; z-index: 5; padding: 120px 72px 130px; border-top: 1px solid rgba(150,235,250,0.08); text-align: center; overflow: hidden;">
    <div style="position: absolute; bottom: -280px; left: 50%; width: 900px; height: 520px; margin-left: -450px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.6 0.13 198 / 0.34), transparent 74%); filter: blur(24px);"></div>
    <div style="position: relative;">
      <h2 style="margin: 0 auto; max-width: 720px; font-size: 56px; line-height: 1.06; letter-spacing: -0.04em; font-weight: 600; color: #f2fdff;">Start investing today</h2>
      <p style="margin: 22px auto 0; max-width: 500px; font-size: 16.5px; line-height: 1.6; color: rgba(230,244,250,0.74);">Top up USDT, buy a plan, and track daily profit in your dashboard.</p>
      <div style="display: flex; justify-content: center; gap: 14px; margin-top: 36px;">
        <button style="padding: 17px 32px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 24px 60px -24px oklch(0.8 0.13 195 / 0.8);">Start investing</button>
        <button style="padding: 17px 28px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.05); color: #e6f4fa; font-family: inherit; font-size: 16px; font-weight: 500; cursor: pointer;">Contact us</button>
      </div>
    </div>
  </section>

  <footer data-screen-label="Footer" style="position: relative; z-index: 5; padding: 56px 72px 36px; border-top: 1px solid rgba(150,235,250,0.1); background: rgba(150,235,250,0.02);">
    <div style="display: grid; grid-template-columns: 1.6fr repeat(4, 1fr); gap: 40px;">
      <div>
        <div class="coin-footer-brand" style="display: flex; align-items: center;">
          <img src="/cloudflops/logo-horizontal.png" alt="CloudFlops" class="coin-brand-logo" />
        </div>
        <p style="margin: 16px 0 0; max-width: 280px; font-size: 13px; line-height: 1.6; color: rgba(230,244,250,0.68);">USDT investment platform with daily profit accrual.</p>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">PRODUCT</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#how" style="color: rgba(230,244,250,0.78);">How it works</a>
          <a href="#plans" style="color: rgba(230,244,250,0.78);">Plans</a>
          <a href="#infra" style="color: rgba(230,244,250,0.78);">Security</a>
          <a href="/dashboard" style="color: rgba(230,244,250,0.78);">Dashboard</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">LEGAL</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#" style="color: rgba(230,244,250,0.78);">Terms</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Privacy</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Risks</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">SUPPORT</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#faq" style="color: rgba(230,244,250,0.78);">FAQ</a>
          <a href="#support" style="color: rgba(230,244,250,0.78);">Help</a>
          <a href="#support" style="color: rgba(230,244,250,0.78);">Contact</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">SOCIAL</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#" style="color: rgba(230,244,250,0.78);">Telegram</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">X</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">LinkedIn</a>
        </div>
      </div>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 44px; padding-top: 22px; border-top: 1px solid rgba(150,235,250,0.1); font-size: 12.5px; color: rgba(230,244,250,0.62);">
      <span>© 2026 CloudFlops. Concept — all values are placeholders.</span>
      <span style="font-family: 'JetBrains Mono', monospace; letter-spacing: 0.1em;">NETWORK ONLINE</span>
    </div>
  </footer>
</div>
</x-dc>
<script type="text/x-dc" data-dc-script data-props="{&quot;rewardRate&quot;:{&quot;editor&quot;:&quot;float&quot;,&quot;default&quot;:0.0042,&quot;tsType&quot;:&quot;number&quot;,&quot;min&quot;:0.001,&quot;max&quot;:0.01,&quot;step&quot;:0.0001,&quot;section&quot;:&quot;Reward model&quot;}}">
class Component extends DCLogic {
  state = { power: 1200, faq: 0, menuOpen: false };

  fmt(n, d) {
    return n.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  setMenuOpen(open) {
    this.setState({ menuOpen: open });
    document.documentElement.classList.toggle('coin-nav-open', open);
  }

  renderVals() {
    const tiers = [
      { max: 600, name: 'Node', mult: 0.86 },
      { max: 2500, name: 'Core', mult: 1 },
      { max: 6000, name: 'Cluster', mult: 1.12 },
      { max: Infinity, name: 'Custom', mult: 1.2 }
    ];
    const rate = this.props.rewardRate ?? 0.0042;
    const tier = tiers.find((t) => this.state.power <= t.max);
    const daily = this.state.power * rate * tier.mult;
    const vals = {
      power: this.state.power,
      powerLabel: this.fmt(this.state.power, 0),
      daily: this.fmt(daily, 2),
      monthly: this.fmt(daily * 30, 1),
      planName: tier.name,
      toggleMenu: () => this.setMenuOpen(!this.state.menuOpen),
      closeMenu: () => this.setMenuOpen(false),
      onPower: (e) => this.setState({ power: Number(e.target.value) })
    };
    for (let i = 0; i < 6; i++) {
      vals['open' + i] = this.state.faq === i;
      vals['closed' + i] = this.state.faq !== i;
      vals['toggle' + i] = () => this.setState((s) => ({ faq: s.faq === i ? -1 : i }));
    }
    return vals;
  }
}
</script>
@endverbatim

@livewire('guest-support-chat')
@livewireScripts
</body>
</html>