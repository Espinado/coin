<section class="coin-landing-section coin-landing-referrals" data-screen-label="Referrals">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-referrals__card">
      <div>
        <h2 class="coin-landing-referrals__title">{{ __('coin.landing.referrals_title') }}</h2>
        <p class="coin-landing-referrals__lead">{{ __('coin.landing.referrals_lead') }}</p>
        <div class="coin-landing-referrals__actions">
          <div class="coin-landing-referrals__link">{{ config('coin.user_domain') }}/r/<span>{{ __('coin.landing.referrals_code_placeholder') }}</span></div>
          <a href="{{ route('register') }}" class="coin-landing-referrals__cta">{{ __('coin.landing.referrals_cta') }}</a>
        </div>
      </div>
      <div class="coin-landing-referrals__stats">
        <div class="coin-landing-referrals__stats-label">{{ __('coin.landing.referrals_stats_label') }}</div>
        <div class="coin-landing-referrals__stats-head">
          <span class="coin-landing-referrals__stats-value">{{ number_format($landingStats['referral_invited'], 0, '.', ' ') }}</span>
          <span class="coin-landing-referrals__stats-unit">{{ __('coin.landing.referrals_invited_unit') }}</span>
        </div>
        <div class="coin-landing-referrals__stats-divider"></div>
        <div class="coin-landing-referrals__stats-row">
          <span>{{ __('coin.landing.referrals_active_contracts') }}</span>
          <span>{{ number_format($landingStats['referral_active_contracts'], 0, '.', ' ') }}</span>
        </div>
        <div class="coin-landing-referrals__stats-row coin-landing-referrals__stats-row--accent">
          <span>{{ __('coin.landing.referrals_rewards') }}</span>
          <span>{{ $landingStats['referral_rewards_label'] }}</span>
        </div>
      </div>
    </div>
  </div>
</section>
