<section class="coin-landing-section coin-landing-metrics" data-screen-label="Metrics">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-metrics__grid">
      <div class="coin-landing-metric-card">
        <div class="coin-landing-metric-card__label">ОБЩАЯ МОЩНОСТЬ</div>
        <div class="coin-landing-metric-card__value">{{ $landingStats['total_power_compact'] }}</div>
      </div>
      <div class="coin-landing-metric-card">
        <div class="coin-landing-metric-card__label">АКТИВНЫЕ ПОЛЬЗОВАТЕЛИ</div>
        <div class="coin-landing-metric-card__value">{{ number_format($landingStats['active_users'], 0, '.', ' ') }}</div>
      </div>
      <div class="coin-landing-metric-card">
        <div class="coin-landing-metric-card__label">АКТИВНЫЕ КОНТРАКТЫ</div>
        <div class="coin-landing-metric-card__value">{{ number_format($landingStats['active_contracts'], 0, '.', ' ') }}</div>
      </div>
      <div class="coin-landing-metric-card coin-landing-metric-card--accent">
        <div class="coin-landing-metric-card__label">ВЫПЛАЧЕННЫЕ НАГРАДЫ</div>
        <div class="coin-landing-metric-card__value">{{ $landingStats['total_rewards_compact'] }}</div>
      </div>
    </div>
  </div>
</section>
