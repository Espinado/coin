@php
    $partners = [
        [
            'slug' => 'bytedance',
            'name' => 'ByteDance',
            'src' => asset('cloudflops/partners/powered-by-bytedance.png'),
        ],
        [
            'slug' => 'nvidia-cuda',
            'name' => 'NVIDIA CUDA',
            'src' => asset('cloudflops/partners/powered-by-nvidia-cuda.png'),
        ],
        [
            'slug' => 'deepseek',
            'name' => 'DeepSeek',
            'src' => asset('cloudflops/partners/powered-by-deepseek.png'),
        ],
    ];
@endphp

<section id="powered-by" class="coin-landing-section coin-landing-powered" data-screen-label="Powered by">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-powered__intro">
      <div class="coin-landing-section__kicker">{{ __('coin.landing.powered_kicker') }}</div>
      <h2 class="coin-landing-section__title">{{ __('coin.landing.powered_title') }}</h2>
      <p class="coin-landing-powered__lead">{{ __('coin.landing.powered_lead') }}</p>
    </div>

    <div class="coin-landing-powered__grid" role="list">
      @foreach($partners as $partner)
        <figure class="coin-landing-powered__item" role="listitem">
          <img
            src="{{ $partner['src'] }}"
            alt="{{ __('coin.landing.powered_by_alt', ['partner' => $partner['name']]) }}"
            class="coin-landing-powered__badge"
            loading="lazy"
            decoding="async"
          />
        </figure>
      @endforeach
    </div>
  </div>
</section>
