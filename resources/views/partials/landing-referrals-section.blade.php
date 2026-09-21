<section class="coin-landing-section coin-landing-referrals" data-screen-label="Рефералы">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-referrals__card">
      <div>
        <h2 class="coin-landing-referrals__title">Приглашайте и получайте больше</h2>
        <p class="coin-landing-referrals__lead">Поделитесь ссылкой — за активные контракты приглашённых пользователей начисляется реферальное вознаграждение. Условия программы настраиваются.</p>
        <div class="coin-landing-referrals__actions">
          <div class="coin-landing-referrals__link">{{ config('coin.user_domain') }}/r/<span>ваш-код</span></div>
          <a href="{{ route('register') }}" class="coin-landing-referrals__cta">Пригласить</a>
        </div>
      </div>
      <div class="coin-landing-referrals__stats">
        <div class="coin-landing-referrals__stats-label">ПЛАТФОРМА</div>
        <div class="coin-landing-referrals__stats-head">
          <span class="coin-landing-referrals__stats-value">{{ number_format($landingStats['referral_invited'], 0, '.', ' ') }}</span>
          <span class="coin-landing-referrals__stats-unit">приглашённых</span>
        </div>
        <div class="coin-landing-referrals__stats-divider"></div>
        <div class="coin-landing-referrals__stats-row">
          <span>Активные контракты</span>
          <span>{{ number_format($landingStats['referral_active_contracts'], 0, '.', ' ') }}</span>
        </div>
        <div class="coin-landing-referrals__stats-row coin-landing-referrals__stats-row--accent">
          <span>Реферальные награды</span>
          <span>{{ $landingStats['referral_rewards_label'] }}</span>
        </div>
      </div>
    </div>
  </div>
</section>
