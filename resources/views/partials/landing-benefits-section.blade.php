@php
  $brandName = \App\Support\PlatformBrand::name();
  $benefits = [
    ['icon' => 'why_1.svg', 'title' => 'AI-инфраструктура', 'text' => 'Мощность работает в дата-центрах платформы — без оборудования у вас дома.'],
    ['icon' => 'why_2.svg', 'title' => 'Прозрачные начисления', 'text' => 'По каждому начислению видно объём мощности и период расчёта.'],
    ['icon' => 'why_3.svg', 'title' => 'Гибкие тарифы', 'text' => 'Мощность можно увеличить в любой момент, срок контракта сохраняется.'],
    ['icon' => 'why_4.svg', 'title' => 'Личный кабинет', 'text' => 'Мощность, контракты, кошелёк и статистика — в одном интерфейсе.'],
    ['icon' => 'why_5.svg', 'title' => 'Быстрый вывод', 'text' => 'Комиссия и срок обработки показываются до подтверждения операции.'],
    ['icon' => 'why_6.svg', 'title' => 'Аналитика и статистика', 'text' => 'Динамика начислений и распределение мощности по дата-центрам.'],
  ];
@endphp

<section id="product" class="coin-landing-section coin-landing-benefits" data-screen-label="Преимущества">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-section__kicker">ПОЧЕМУ {{ strtoupper($brandName) }}</div>
    <h2 class="coin-landing-section__title coin-landing-benefits__title">Понятный продукт вокруг инфраструктуры</h2>

    <div class="coin-landing-benefits__grid">
      @foreach ($benefits as $benefit)
        <div class="coin-landing-benefit-card">
          <div class="coin-landing-benefit-card__icon">
            <img src="{{ asset('coin/landing/' . $benefit['icon']) }}" alt="" width="24" height="24" />
          </div>
          <h3 class="coin-landing-benefit-card__title">{{ $benefit['title'] }}</h3>
          <p class="coin-landing-benefit-card__text">{{ $benefit['text'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
