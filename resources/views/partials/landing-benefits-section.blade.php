@php
  $brandName = \App\Support\PlatformBrand::name();
  $benefits = [
    ['icon' => 'why_1.svg', 'title' => __('coin.landing.benefit_1_title'), 'text' => __('coin.landing.benefit_1_text')],
    ['icon' => 'why_2.svg', 'title' => __('coin.landing.benefit_2_title'), 'text' => __('coin.landing.benefit_2_text')],
    ['icon' => 'why_3.svg', 'title' => __('coin.landing.benefit_3_title'), 'text' => __('coin.landing.benefit_3_text')],
    ['icon' => 'why_4.svg', 'title' => __('coin.landing.benefit_4_title'), 'text' => __('coin.landing.benefit_4_text')],
    ['icon' => 'why_5.svg', 'title' => __('coin.landing.benefit_5_title'), 'text' => __('coin.landing.benefit_5_text')],
    ['icon' => 'why_6.svg', 'title' => __('coin.landing.benefit_6_title'), 'text' => __('coin.landing.benefit_6_text')],
  ];
@endphp

<section id="product" class="coin-landing-section coin-landing-benefits" data-screen-label="Benefits">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-section__kicker">{{ __('coin.landing.benefits_kicker', ['brand' => strtoupper($brandName)]) }}</div>
    <h2 class="coin-landing-section__title coin-landing-benefits__title">{{ __('coin.landing.benefits_title') }}</h2>

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
