@php
    $partners = [
        [
            'slug' => 'bytedance',
            'name' => 'ByteDance',
            'src' => asset('cloudflops/partners/bytedance.png'),
        ],
        [
            'slug' => 'nvidia-cuda',
            'name' => 'NVIDIA CUDA',
            'src' => asset('cloudflops/partners/nvidia-cuda.png'),
        ],
        [
            'slug' => 'deepseek',
            'name' => 'DeepSeek',
            'src' => asset('cloudflops/partners/deepseek.png'),
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
          <figcaption class="coin-landing-powered__label">{{ __('coin.landing.powered_by') }}</figcaption>
          <img
            src="{{ $partner['src'] }}"
            alt="{{ $partner['name'] }}"
            class="coin-landing-powered__logo coin-landing-powered__logo--{{ $partner['slug'] }}"
            loading="lazy"
            decoding="async"
          />
        </figure>
      @endforeach
    </div>
  </div>
</section>
