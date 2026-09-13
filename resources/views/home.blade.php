<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coin — AI-вычисления</title>
    <script src="{{ asset('coin/support.js') }}"></script>
</head>
<body>
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
  <header class="coin-header" data-screen-label="Шапка" style="position: relative; z-index: 20; display: flex; align-items: center; justify-content: space-between; padding: 22px 72px; border-bottom: 1px solid rgba(150,235,250,0.1); background: rgba(6,20,35,0.75); backdrop-filter: blur(14px);">
    <div class="coin-header-brand" style="display: flex; align-items: center; gap: 11px;">
      <div style="width: 28px; height: 28px; border-radius: 9px; background: linear-gradient(145deg, oklch(0.86 0.12 192), oklch(0.6 0.13 210)); display: grid; place-items: center;">
        <div style="width: 10px; height: 10px; border-radius: 3px; background: #061423;"></div>
      </div>
      <span style="font-size: 17px; font-weight: 600; letter-spacing: -0.02em;">Coin</span>
    </div>
    <button type="button" class="coin-burger" onClick="{{ toggleMenu }}" aria-label="Открыть меню"><span></span><span></span><span></span></button>
    <nav class="coin-nav coin-nav-desktop" style="display: flex; align-items: center; gap: 30px;">
      <a href="#product" style="font-size: 14px; color: rgba(230,244,250,0.78);">Продукт</a>
      <a href="#how" style="font-size: 14px; color: rgba(230,244,250,0.78);">Как это работает</a>
      <a href="#plans" style="font-size: 14px; color: rgba(230,244,250,0.78);">Тарифы</a>
      <a href="#infra" style="font-size: 14px; color: rgba(230,244,250,0.78);">Инфраструктура</a>
      <a href="#account" style="font-size: 14px; color: rgba(230,244,250,0.78);">Личный кабинет</a>
      <a href="#faq" style="font-size: 14px; color: rgba(230,244,250,0.78);">FAQ</a>
    </nav>
    <nav class="coin-nav coin-nav-mobile">
      <a href="#product" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Продукт</a>
      <a href="#how" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Как это работает</a>
      <a href="#plans" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Тарифы</a>
      <a href="#infra" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Инфраструктура</a>
      <a href="#account" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">Личный кабинет</a>
      <a href="#faq" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.78);">FAQ</a>
      <a href="/dashboard" onClick="{{ closeMenu }}" style="font-size: 14px; color: rgba(230,244,250,0.85); margin-top: 12px;">Войти</a>
    </nav>
    <div class="coin-header-actions" style="display: flex; align-items: center; gap: 12px;">
      <a href="/dashboard" class="coin-header-login" style="background: none; border: 0; padding: 10px 4px; font-family: inherit; font-size: 14px; font-weight: 500; color: rgba(230,244,250,0.85); cursor: pointer;">Войти</a>
      <a href="/dashboard" style="padding: 11px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Начать</a>
    </div>
  </header>

  <section data-screen-label="Герой" style="position: relative; z-index: 5; display: grid; grid-template-columns: 1fr 540px; gap: 60px; align-items: center; padding: 96px 72px 104px;">
    <div>
      <div style="display: inline-flex; align-items: center; gap: 10px; padding: 7px 14px; border-radius: 999px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195);">
        <span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); animation: paiPulse 2.4s infinite;"></span>ПЛАТФОРМА AI-ВЫЧИСЛЕНИЙ
      </div>
      <h1 style="margin: 26px 0 0; font-size: 66px; line-height: 1.06; letter-spacing: -0.04em; font-weight: 600; color: #f0fbff;">Активируйте AI-мощность и получайте вознаграждение</h1>
      <p style="margin: 24px 0 0; max-width: 520px; font-size: 17px; line-height: 1.62; color: rgba(230,244,250,0.72);">Вы выбираете тариф, получаете вычислительную мощность на базе дата-центров и видите начисления в личном кабинете. Без покупки и обслуживания оборудования.</p>
      <div style="display: flex; gap: 14px; margin-top: 36px;">
        <button style="padding: 16px 28px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 15.5px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 50px -22px oklch(0.8 0.13 195 / 0.7);">Активировать мощность</button>
        <button style="padding: 16px 26px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.05); color: #e6f4fa; font-family: inherit; font-size: 15.5px; font-weight: 500; cursor: pointer;">Как это работает</button>
      </div>
      <div style="display: flex; gap: 36px; margin-top: 44px; padding-top: 26px; border-top: 1px solid rgba(150,235,250,0.1);">
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">7</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">дата-центров</div>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">99,9%</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">доступность сети</div>
        </div>
        <div>
          <div style="font-family: 'JetBrains Mono', monospace; font-size: 19px; color: #f0fbff;">24/7</div>
          <div style="margin-top: 6px; font-size: 13px; color: rgba(230,244,250,0.7);">мониторинг</div>
        </div>
      </div>
    </div>

    <div style="position: relative;">
      <div style="padding: 26px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(20,55,80,0.7), rgba(6,20,35,0.85)); box-shadow: 0 40px 90px -50px #000;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">ВЫЧИСЛИТЕЛЬНАЯ ИНФРАСТРУКТУРА</span>
          <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160); animation: paiPulse 2.2s infinite;"></span>РАБОТАЕТ</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 22px;">
          <div style="padding: 12px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px;">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 84%, rgba(150,235,250,0.12) 84%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 92%, rgba(150,235,250,0.12) 92%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.7 0.15 292) 71%, rgba(150,235,250,0.12) 71%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: rgba(150,235,250,0.3);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(150,235,250,0.14);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: rgba(230,244,250,0.68);">ДЦ 01</div>
          </div>
          <div style="padding: 12px; border-radius: 14px; border: 1px solid oklch(0.86 0.11 195 / 0.3); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px; box-shadow: inset 0 0 26px oklch(0.6 0.13 200 / 0.25);">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.86 0.12 192) 96%, rgba(150,235,250,0.12) 96%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.86 0.12 192) 88%, rgba(150,235,250,0.12) 88%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 93%, rgba(150,235,250,0.12) 93%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 78%, rgba(150,235,250,0.12) 78%);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: oklch(0.88 0.1 205);">ДЦ 02</div>
          </div>
          <div style="padding: 12px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.6); display: flex; flex-direction: column; gap: 7px;">
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 67%, rgba(150,235,250,0.12) 67%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.88 0.15 90 / 0.7) 44%, rgba(150,235,250,0.12) 44%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: linear-gradient(90deg, oklch(0.8 0.12 198) 86%, rgba(150,235,250,0.12) 86%);"></span></div>
            <div style="display: flex; align-items: center; gap: 7px;"><span style="width: 5px; height: 5px; border-radius: 50%; background: rgba(150,235,250,0.3);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(150,235,250,0.14);"></span></div>
            <div style="margin-top: 2px; font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.1em; color: rgba(230,244,250,0.68);">ДЦ 03</div>
          </div>
        </div>

        <div style="margin-top: 20px; padding: 18px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 13.5px; color: rgba(230,244,250,0.78);">Активная мощность</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: #f0fbff;">1 200 ед.</span>
          </div>
          <div style="margin-top: 14px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 74%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.72 0.11 215), oklch(0.88 0.12 192));"></div></div>
          <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 18px;">
            <span style="font-size: 13.5px; color: rgba(230,244,250,0.78);">Начислено сегодня</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 15px; color: oklch(0.9 0.12 192);">+5,04</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section data-screen-label="Показатели" style="position: relative; z-index: 5; padding: 0 72px 104px;">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ОБЩАЯ МОЩНОСТЬ</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">12,4 <span style="font-size: 15px; font-weight: 400; color: rgba(230,244,250,0.7);">млн ед.</span></div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">АКТИВНЫЕ ПОЛЬЗОВАТЕЛИ</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">241 806</div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">АКТИВНЫЕ КОНТРАКТЫ</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">18 420</div>
      </div>
      <div style="padding: 26px; border-radius: 18px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.18), rgba(150,235,250,0.03));">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.75);">ВЫПЛАЧЕННЫЕ НАГРАДЫ</div>
        <div style="margin-top: 16px; font-size: 34px; font-weight: 600; letter-spacing: -0.035em; color: #f0fbff;">64,2 <span style="font-size: 15px; font-weight: 400; color: rgba(230,244,250,0.75);">млн</span></div>
      </div>
    </div>
    <div style="margin-top: 16px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: rgba(230,244,250,0.62);">ЗНАЧЕНИЯ-ЗАГЛУШКИ · ЗАМЕНЯЮТСЯ НА РЕАЛЬНЫЕ ДАННЫЕ</div>
  </section>

  <section id="how" data-screen-label="Как это работает" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 60px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">КАК ЭТО РАБОТАЕТ</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Четыре шага</h2>
      </div>
      <p style="max-width: 400px; margin: 0 0 6px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">От выбора тарифа до начислений в личном кабинете. Оборудование и обслуживание остаются на стороне платформы.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="display: flex; align-items: flex-end; gap: 3px; height: 16px;"><div style="width: 4px; height: 45%; border-radius: 2px; background: rgba(214,238,248,0.4);"></div><div style="width: 4px; height: 72%; border-radius: 2px; background: oklch(0.78 0.11 205);"></div><div style="width: 4px; height: 100%; border-radius: 2px; background: oklch(0.88 0.12 192);"></div></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">01</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Выберите тариф</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Тариф определяет объём мощности, срок и тип инфраструктуры.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="width: 17px; height: 17px; border-radius: 5px; background: linear-gradient(145deg, oklch(0.88 0.12 192), oklch(0.62 0.13 210));"></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">02</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Получите AI-мощность</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Мощность закрепляется за вашим аккаунтом и становится активной.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;"><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.7 0.15 292);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(214,238,248,0.35);"></span><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.86 0.11 195 / 0.6);">03</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Работа через дата-центры</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Мощность задействована в вычислительной инфраструктуре платформы.</p>
      </div>
      <div style="padding: 28px 24px; border-radius: 18px; border: 1px solid oklch(0.86 0.11 195 / 0.28); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <div style="width: 44px; height: 44px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: oklch(0.6 0.13 200 / 0.25); display: grid; place-items: center;">
            <div style="width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid oklch(0.9 0.11 195);"></div>
          </div>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 20px; color: oklch(0.88 0.11 195 / 0.75);">04</span>
        </div>
        <h3 style="margin: 22px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Получайте вознаграждение</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.75);">Начисления и статистика доступны в личном кабинете.</p>
      </div>
    </div>
  </section>

  <section id="plans" data-screen-label="Тарифы" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="text-align: center; max-width: 620px; margin: 0 auto;">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">ТАРИФЫ</div>
      <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Выберите объём мощности</h2>
      <p style="margin: 18px 0 0; font-size: 16px; line-height: 1.6; color: rgba(230,244,250,0.72);">Цены, мощность и сроки — редактируемые заглушки. Итоговое вознаграждение зависит от загрузки инфраструктуры.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 30px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column;">
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Узел</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.7);">Стартовый объём</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">$250</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.68);">заглушка</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Мощность</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">250 ед.</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Срок</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">12 месяцев</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Оценка награды</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~0,9 / день</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Инфраструктура</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Общий пул</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer;">Выбрать</button>
      </div>

      <div style="position: relative; padding: 30px; border-radius: 20px; border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(150,235,250,0.03)); display: flex; flex-direction: column; box-shadow: 0 30px 70px -44px oklch(0.7 0.14 195 / 0.9);">
        <div style="position: absolute; top: -10px; left: 30px; padding: 4px 11px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: #04121f;">ПОПУЛЯРНЫЙ</div>
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Ядро</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.74);">Оптимальный баланс</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">$1 100</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.7);">заглушка</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.16); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Мощность</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">1 200 ед.</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Срок</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">12 месяцев</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Оценка награды</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~5,0 / день</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.74); min-width: 0;">Инфраструктура</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Приоритетный пул</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Выбрать</button>
      </div>

      <div style="padding: 30px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04); display: flex; flex-direction: column;">
        <div style="font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">Кластер</div>
        <div style="margin-top: 5px; font-size: 13.5px; color: rgba(230,244,250,0.7);">Максимальный объём</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; gap: 8px;">
          <span style="font-size: 34px; font-weight: 600; letter-spacing: -0.035em;">$3 400</span>
          <span style="font-size: 12.5px; color: rgba(230,244,250,0.68);">заглушка</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 24px 0;"></div>
        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Мощность</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">4 000 ед.</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Срок</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">12 месяцев</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Оценка награды</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">~18,8 / день</span></div>
          <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Инфраструктура</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">Выделенные поды</span></div>
        </div>
        <button style="margin-top: 28px; padding: 12px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer;">Выбрать</button>
      </div>
    </div>

    <div style="margin-top: 20px; padding: 32px 36px; border-radius: 20px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); display: grid; grid-template-columns: 1fr 320px; gap: 48px; align-items: center;">
      <div>
        <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">Оценка вознаграждения</div>
        <div style="margin-top: 22px; display: flex; align-items: baseline; justify-content: space-between;">
          <span style="font-size: 13.5px; color: rgba(230,244,250,0.74);">Объём мощности</span>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #f0fbff;">{{ powerLabel }} <span style="font-size: 12px; color: rgba(230,244,250,0.7);">ед.</span></span>
        </div>
        <input type="range" min="100" max="10000" step="100" value="{{ power }}" onChange="{{ onPower }}" style="width: 100%; margin-top: 16px; height: 4px; cursor: pointer;" />
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.65);"><span>100</span><span>5 000</span><span>10 000</span></div>
        <p style="margin: 20px 0 0; font-size: 12px; line-height: 1.55; color: rgba(230,244,250,0.65);">Расчёт носит справочный характер. Итоговые начисления зависят от загрузки инфраструктуры и не являются гарантированной доходностью.</p>
      </div>
      <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(20,55,80,0.75), rgba(6,20,35,0.9));">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">ОЦЕНКА ЗА ДЕНЬ</div>
        <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 32px; color: oklch(0.9 0.12 192);">{{ daily }}</div>
        <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px; margin-bottom: 12px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">За месяц</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ monthly }}</span></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Подходящий тариф</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ planName }}</span></div>
      </div>
    </div>
  </section>

  <section id="product" data-screen-label="Преимущества" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">ПОЧЕМУ Coin</div>
    <h2 style="margin: 18px 0 0; max-width: 560px; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Понятный продукт вокруг инфраструктуры</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 48px;">
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 4px; background: oklch(0.86 0.12 192);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">AI-инфраструктура</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Мощность работает в дата-центрах платформы — без оборудования у вас дома.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 50%; border: 2px solid oklch(0.86 0.11 195);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Прозрачные начисления</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">По каждому начислению видно объём мощности и период расчёта.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="display: flex; gap: 4px;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.86 0.12 192);"></span><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.7 0.15 292);"></span></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Гибкие тарифы</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Мощность можно увеличить в любой момент, срок контракта сохраняется.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 14px; height: 16px; border-radius: 3px; border: 1.5px solid oklch(0.86 0.11 195);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Личный кабинет</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Мощность, контракты, кошелёк и статистика — в одном интерфейсе.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 50%; border: 1.5px solid rgba(150,235,250,0.35); border-top-color: oklch(0.88 0.12 192);"></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Быстрый вывод</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Комиссия и срок обработки показываются до подтверждения операции.</p>
      </div>
      <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
        <div style="width: 42px; height: 42px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.22); background: rgba(150,235,250,0.07); display: grid; place-items: center;"><span style="display: flex; align-items: flex-end; gap: 3px; height: 15px;"><span style="width: 4px; height: 45%; border-radius: 2px; background: rgba(214,238,248,0.4);"></span><span style="width: 4px; height: 72%; border-radius: 2px; background: oklch(0.72 0.11 215);"></span><span style="width: 4px; height: 100%; border-radius: 2px; background: oklch(0.88 0.12 192);"></span></span></div>
        <h3 style="margin: 20px 0 0; font-size: 17.5px; font-weight: 600; letter-spacing: -0.015em; color: #f0fbff;">Аналитика и статистика</h3>
        <p style="margin: 9px 0 0; font-size: 14px; line-height: 1.6; color: rgba(230,244,250,0.7);">Динамика начислений и распределение мощности по дата-центрам.</p>
      </div>
    </div>
  </section>

  <section id="infra" data-screen-label="Инфраструктура" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: grid; grid-template-columns: 1fr 560px; gap: 60px; align-items: center;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">ИНФРАСТРУКТУРА</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Вычислительная инфраструктура на базе дата-центров</h2>
        <p style="margin: 22px 0 0; max-width: 470px; font-size: 16.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">Платформа строится вокруг собственных вычислительных площадок. Оборудование, охлаждение, питание и мониторинг остаются на нашей стороне — вы работаете только с мощностью и начислениями.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 36px; max-width: 460px;">
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ДАТА-ЦЕНТРЫ</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">7</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ДОСТУПНАЯ МОЩНОСТЬ</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">1,7 млн</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">ДОСТУПНОСТЬ</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">99,9%</div>
          </div>
          <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">МОНИТОРИНГ</div>
            <div style="margin-top: 12px; font-size: 26px; font-weight: 600; letter-spacing: -0.03em;">24/7</div>
          </div>
        </div>
      </div>

      <div style="padding: 28px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.14); background: linear-gradient(170deg, rgba(150,235,250,0.06), rgba(6,20,35,0.5));">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">СТАТУС ПЛОЩАДОК</span>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.65);">ЗАГЛУШКА</span>
        </div>
        <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 12px;">
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Дата-центр 01</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>РАБОТАЕТ</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 84%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">ЗАГРУЗКА 84%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Дата-центр 02</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>РАБОТАЕТ</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 91%; height: 100%; border-radius: 3px; background: oklch(0.8 0.12 198);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">ЗАГРУЗКА 91%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px solid rgba(150,235,250,0.12); background: rgba(4,16,28,0.55);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
              <span style="font-size: 14px;">Дата-центр 03</span>
              <span style="display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.9 0.14 90); flex: none;"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>РАСШИРЕНИЕ</span>
            </div>
            <div style="margin-top: 12px; height: 4px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: 46%; height: 100%; border-radius: 3px; background: oklch(0.88 0.15 90 / 0.8);"></div></div>
            <div style="margin-top: 8px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">ЗАГРУЗКА 46%</div>
          </div>
          <div style="padding: 16px 18px; border-radius: 14px; border: 1px dashed rgba(150,235,250,0.2); background: rgba(150,235,250,0.02); display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <span style="font-size: 14px; color: rgba(230,244,250,0.78);">Дата-центр 04 — 07</span>
            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.68); flex: none;">ДАННЫЕ УТОЧНЯЮТСЯ</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="account" data-screen-label="Личный кабинет" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 60px; margin-bottom: 44px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">ЛИЧНЫЙ КАБИНЕТ</div>
        <h2 style="margin: 18px 0 0; font-size: 46px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Всё управление в одном месте</h2>
      </div>
      <p style="max-width: 380px; margin: 0 0 6px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.72);">Баланс, активная мощность, тариф, контракты, кошелёк и рефералы — так выглядит внутренний интерфейс.</p>
    </div>

    <div style="border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(180deg, rgba(11,32,49,0.96), rgba(5,16,27,0.98)); box-shadow: 0 60px 120px -60px #000; overflow: hidden;">
      <div style="display: grid; grid-template-columns: 216px 1fr;">
        <aside style="padding: 22px 14px; border-right: 1px solid rgba(150,235,250,0.1); display: flex; flex-direction: column; gap: 3px;">
          <div style="display: flex; align-items: center; gap: 10px; padding: 0 10px 20px;">
            <div style="width: 24px; height: 24px; border-radius: 8px; background: linear-gradient(145deg, oklch(0.86 0.12 192), oklch(0.6 0.13 210));"></div>
            <span style="font-size: 14px; font-weight: 600; letter-spacing: -0.015em;">Coin</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: oklch(0.6 0.13 200 / 0.22); border: 1px solid oklch(0.86 0.11 195 / 0.3); font-size: 13px; color: #f0fbff;"><span style="width: 6px; height: 6px; border-radius: 2px; background: oklch(0.88 0.12 192);"></span>Панель</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Тарифы</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Контракты</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Статистика</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Кошелёк</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Рефералы</div>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; color: rgba(230,244,250,0.7);"><span style="width: 6px; height: 6px; border-radius: 2px; background: rgba(150,235,250,0.3);"></span>Настройки</div>
        </aside>

        <div style="padding: 24px 26px 28px;">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
              <div style="font-size: 18px; font-weight: 600; letter-spacing: -0.02em;">Панель</div>
              <div style="margin-top: 4px; font-size: 12.5px; color: rgba(230,244,250,0.7);">Обзор аккаунта · тариф «Ядро»</div>
            </div>
            <div style="display: flex; gap: 7px;">
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(230,244,250,0.7);">30Д</span>
              <span style="padding: 7px 12px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.4); background: oklch(0.6 0.13 200 / 0.22); font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: #f0fbff;">14Д</span>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 22px;">
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">БАЛАНС</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 482,60</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">АКТИВНАЯ МОЩНОСТЬ</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: #f0fbff;">1 200</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.7);">АКТИВНЫЙ ТАРИФ</div>
              <div style="margin-top: 12px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">Ядро</div>
            </div>
            <div style="padding: 18px; border-radius: 15px; border: 1px solid oklch(0.86 0.11 195 / 0.26); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.2), rgba(150,235,250,0.03));">
              <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(230,244,250,0.75);">НАЧИСЛЕНО СЕГОДНЯ</div>
              <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: oklch(0.9 0.12 192);">+5,04</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 14px; margin-top: 14px;">
            <div style="padding: 20px; border-radius: 15px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
              <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <span style="font-size: 14px; font-weight: 600;">Начисления</span>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(230,244,250,0.66);">ЗА 14 ДНЕЙ</span>
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
              <span style="font-size: 14px; font-weight: 600;">Статус дата-центров</span>
              <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 14px; font-size: 12.5px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Дата-центр 01</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">84%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>Дата-центр 02</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">91%</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="display: flex; align-items: center; gap: 8px; color: rgba(230,244,250,0.78);"><span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.88 0.15 90);"></span>Дата-центр 03</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">46%</span></div>
                <div style="height: 1px; background: rgba(150,235,250,0.1);"></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Контракты</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">2 активных</span></div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;"><span style="color: rgba(230,244,250,0.72);">Рефералы</span><span style="font-family: 'JetBrains Mono', monospace; flex: none;">28</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 14px;">
      <a href="/dashboard" style="padding: 13px 24px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-size: 14px; font-weight: 500;">Посмотреть личный кабинет</a>
    </div>
  </section>

  <section data-screen-label="Рефералы" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="padding: 44px 48px; border-radius: 22px; border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.1)); display: grid; grid-template-columns: 1fr 360px; gap: 56px; align-items: center;">
      <div>
        <h2 style="margin: 0; font-size: 36px; line-height: 1.1; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Приглашайте и получайте больше</h2>
        <p style="margin: 16px 0 0; max-width: 480px; font-size: 15.5px; line-height: 1.62; color: rgba(230,244,250,0.74);">Поделитесь ссылкой — за активные контракты приглашённых пользователей начисляется реферальное вознаграждение. Условия программы настраиваются.</p>
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 28px; flex-wrap: wrap;">
          <div style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.28); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #eafcff;">coin/r/<span style="color: oklch(0.88 0.11 195);">COIN-4X9K2</span></div>
          <button style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;">Пригласить</button>
        </div>
      </div>
      <div style="padding: 24px; border-radius: 18px; border: 1px solid rgba(150,235,250,0.16); background: rgba(6,20,35,0.7);">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.72);">ВАША СЕТЬ</div>
        <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 10px;">
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 30px; color: #f0fbff;">28</span>
          <span style="font-size: 13px; color: rgba(230,244,250,0.72);">приглашённых</span>
        </div>
        <div style="height: 1px; background: rgba(150,235,250,0.14); margin: 20px 0;"></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px; margin-bottom: 12px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Активные контракты</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">19</span></div>
        <div style="display: flex; justify-content: space-between; gap: 14px; font-size: 13px;"><span style="color: rgba(230,244,250,0.72); min-width: 0;">Реферальные награды</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right; color: oklch(0.9 0.12 192);">+112,40</span></div>
      </div>
    </div>
  </section>

  <section id="faq" data-screen-label="FAQ" style="position: relative; z-index: 5; padding: 100px 72px; border-top: 1px solid rgba(150,235,250,0.08);">
    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 72px;">
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.16em; color: oklch(0.88 0.11 195);">FAQ</div>
        <h2 style="margin: 18px 0 0; font-size: 42px; line-height: 1.08; letter-spacing: -0.035em; font-weight: 600; color: #f0fbff;">Частые вопросы</h2>
        <p style="margin: 18px 0 0; font-size: 15px; line-height: 1.62; color: rgba(230,244,250,0.7);">Если нужного ответа нет — <a href="#">напишите нам</a>.</p>
      </div>
      <div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle0 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Что такое AI-мощность?</span>
            <sc-if value="{{ open0 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed0 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open0 }}" hint-placeholder-val="{{ true }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Это объём вычислительных ресурсов, закреплённый за вашим аккаунтом. Мощность выражается в условных единицах и работает на оборудовании дата-центров платформы.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle1 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Как работает тариф?</span>
            <sc-if value="{{ open1 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed1 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open1 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Тариф определяет объём мощности, срок действия контракта и тип инфраструктуры. Параметры тарифов — редактируемые заглушки и будут уточнены.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle2 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Откуда начисляются награды?</span>
            <sc-if value="{{ open2 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed2 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open2 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Награды формируются за счёт использования вычислительной мощности в инфраструктуре платформы и распределяются пропорционально активному объёму. Размер начислений переменный.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle3 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Как продукт связан с дата-центрами?</span>
            <sc-if value="{{ open3 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed3 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open3 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Вся мощность размещена на вычислительных площадках платформы. Пользователь не покупает и не подключает собственное оборудование — инфраструктура обслуживается нашей командой.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle4 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Как вывести средства?</span>
            <sc-if value="{{ open4 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed4 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open4 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Заявка оформляется в разделе «Кошелёк» личного кабинета. Комиссия и срок обработки отображаются до подтверждения. Конкретные условия уточняются.</p>
          </sc-if>
        </div>
        <div style="border-top: 1px solid rgba(150,235,250,0.12); border-bottom: 1px solid rgba(150,235,250,0.12);">
          <button onClick="{{ toggle5 }}" style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
            <span>Что можно увидеть в личном кабинете?</span>
            <sc-if value="{{ open5 }}" hint-placeholder-val="{{ false }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span></sc-if>
            <sc-if value="{{ closed5 }}" hint-placeholder-val="{{ true }}"><span style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span></sc-if>
          </button>
          <sc-if value="{{ open5 }}" hint-placeholder-val="{{ false }}">
            <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">Баланс, активную мощность и тариф, историю начислений, контракты, кошелёк, рефералов и статус дата-центров.</p>
          </sc-if>
        </div>
      </div>
    </div>
  </section>

  <section data-screen-label="Финальный CTA" style="position: relative; z-index: 5; padding: 120px 72px 130px; border-top: 1px solid rgba(150,235,250,0.08); text-align: center; overflow: hidden;">
    <div style="position: absolute; bottom: -280px; left: 50%; width: 900px; height: 520px; margin-left: -450px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.6 0.13 198 / 0.34), transparent 74%); filter: blur(24px);"></div>
    <div style="position: relative;">
      <h2 style="margin: 0 auto; max-width: 720px; font-size: 56px; line-height: 1.06; letter-spacing: -0.04em; font-weight: 600; color: #f2fdff;">Начните с AI-мощности</h2>
      <p style="margin: 22px auto 0; max-width: 500px; font-size: 16.5px; line-height: 1.6; color: rgba(230,244,250,0.74);">Выберите тариф, активируйте мощность и следите за начислениями в личном кабинете.</p>
      <div style="display: flex; justify-content: center; gap: 14px; margin-top: 36px;">
        <button style="padding: 17px 32px; border-radius: 13px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 24px 60px -24px oklch(0.8 0.13 195 / 0.8);">Активировать мощность</button>
        <button style="padding: 17px 28px; border-radius: 13px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.05); color: #e6f4fa; font-family: inherit; font-size: 16px; font-weight: 500; cursor: pointer;">Связаться с нами</button>
      </div>
    </div>
  </section>

  <footer data-screen-label="Футер" style="position: relative; z-index: 5; padding: 56px 72px 36px; border-top: 1px solid rgba(150,235,250,0.1); background: rgba(150,235,250,0.02);">
    <div style="display: grid; grid-template-columns: 1.6fr repeat(4, 1fr); gap: 40px;">
      <div>
        <div style="display: flex; align-items: center; gap: 11px;">
          <div style="width: 26px; height: 26px; border-radius: 8px; background: linear-gradient(145deg, oklch(0.86 0.12 192), oklch(0.6 0.13 210));"></div>
          <span style="font-size: 16px; font-weight: 600; letter-spacing: -0.02em;">Coin</span>
        </div>
        <p style="margin: 16px 0 0; max-width: 280px; font-size: 13px; line-height: 1.6; color: rgba(230,244,250,0.68);">Платформа AI-вычислений на базе дата-центров. Рабочее название проекта.</p>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">ПРОДУКТ</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#how" style="color: rgba(230,244,250,0.78);">Как это работает</a>
          <a href="#plans" style="color: rgba(230,244,250,0.78);">Тарифы</a>
          <a href="#infra" style="color: rgba(230,244,250,0.78);">Инфраструктура</a>
          <a href="#account" style="color: rgba(230,244,250,0.78);">Личный кабинет</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">ДОКУМЕНТЫ</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#" style="color: rgba(230,244,250,0.78);">Условия</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Конфиденциальность</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Риски</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">ПОДДЕРЖКА</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#faq" style="color: rgba(230,244,250,0.78);">FAQ</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Помощь</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">Контакты</a>
        </div>
      </div>
      <div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; color: rgba(230,244,250,0.65);">СОЦСЕТИ</div>
        <div style="display: flex; flex-direction: column; gap: 11px; margin-top: 16px; font-size: 13.5px;">
          <a href="#" style="color: rgba(230,244,250,0.78);">Telegram</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">X</a>
          <a href="#" style="color: rgba(230,244,250,0.78);">LinkedIn</a>
        </div>
      </div>
    </div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 44px; padding-top: 22px; border-top: 1px solid rgba(150,235,250,0.1); font-size: 12.5px; color: rgba(230,244,250,0.62);">
      <span>© 2026 Coin. Концепт — все значения являются заглушками.</span>
      <span style="font-family: 'JetBrains Mono', monospace; letter-spacing: 0.1em;">СЕТЬ РАБОТАЕТ</span>
    </div>
  </footer>
</div>
</x-dc>
<script type="text/x-dc" data-dc-script data-props="{&quot;rewardRate&quot;:{&quot;editor&quot;:&quot;float&quot;,&quot;default&quot;:0.0042,&quot;tsType&quot;:&quot;number&quot;,&quot;min&quot;:0.001,&quot;max&quot;:0.01,&quot;step&quot;:0.0001,&quot;section&quot;:&quot;Модель наград&quot;}}">
class Component extends DCLogic {
  state = { power: 1200, faq: 0, menuOpen: false };

  fmt(n, d) {
    return n.toLocaleString('ru-RU', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  setMenuOpen(open) {
    this.setState({ menuOpen: open });
    document.documentElement.classList.toggle('coin-nav-open', open);
  }

  renderVals() {
    const tiers = [
      { max: 600, name: 'Узел', mult: 0.86 },
      { max: 2500, name: 'Ядро', mult: 1 },
      { max: 6000, name: 'Кластер', mult: 1.12 },
      { max: Infinity, name: 'Индивидуальный', mult: 1.2 }
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
</body>
</html>