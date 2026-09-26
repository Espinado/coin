<section id="infra" class="coin-landing-section coin-landing-infra" data-screen-label="Infrastructure">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-infra__grid">
      <div>
        <div class="coin-landing-section__kicker">{{ __('coin.landing.infra_kicker') }}</div>
        <h2 class="coin-landing-section__title">{{ __('coin.landing.infra_title') }}</h2>
        <p class="coin-landing-infra__lead">{{ __('coin.landing.infra_lead') }}</p>

        <div class="coin-landing-infra__stats">
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">{{ __('coin.landing.infra_stat_plans') }}</div>
            <div class="coin-landing-infra-stat__value">{{ $landingStats['active_plan_count'] ?? $activePlanCount }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">{{ __('coin.landing.infra_stat_invested') }}</div>
            <div class="coin-landing-infra-stat__value">{{ $landingStats['total_locked_compact'] }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">{{ __('coin.landing.infra_stat_contracts') }}</div>
            <div class="coin-landing-infra-stat__value">{{ number_format($landingStats['active_contracts'], 0, '.', ' ') }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">{{ __('coin.landing.infra_stat_uptime') }}</div>
            <div class="coin-landing-infra-stat__value">99,9%</div>
          </div>
        </div>
      </div>

      <div class="coin-landing-infra-panel">
        <div class="coin-landing-infra-panel__head">
          <span class="coin-landing-infra-panel__title">{{ __('coin.landing.infra_panel_title') }}</span>
          <span class="coin-landing-infra-panel__badge">24/7</span>
        </div>

        <div class="coin-landing-infra-panel__list">
          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">{{ __('coin.landing.mock_datacenter', ['n' => '01']) }}</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--online">
                <span class="coin-landing-infra-dc__dot"></span>{{ __('coin.landing.status_online') }}
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill" style="width: 84%;"></div></div>
            <div class="coin-landing-infra-dc__load">{{ __('coin.landing.infra_load', ['pct' => 84]) }}</div>
          </div>

          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">{{ __('coin.landing.mock_datacenter', ['n' => '02']) }}</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--online">
                <span class="coin-landing-infra-dc__dot"></span>{{ __('coin.landing.status_online') }}
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill coin-landing-infra-dc__fill--mid" style="width: 91%;"></div></div>
            <div class="coin-landing-infra-dc__load">{{ __('coin.landing.infra_load', ['pct' => 91]) }}</div>
          </div>

          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">{{ __('coin.landing.mock_datacenter', ['n' => '03']) }}</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--expand">
                <span class="coin-landing-infra-dc__dot coin-landing-infra-dc__dot--expand"></span>{{ __('coin.landing.status_expanding') }}
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill coin-landing-infra-dc__fill--expand" style="width: 46%;"></div></div>
            <div class="coin-landing-infra-dc__load">{{ __('coin.landing.infra_load', ['pct' => 46]) }}</div>
          </div>

          <div class="coin-landing-infra-dc coin-landing-infra-dc--placeholder">
            <span class="coin-landing-infra-dc__name">{{ __('coin.landing.mock_datacenter_range') }}</span>
            <span class="coin-landing-infra-dc__tbc">{{ __('coin.landing.infra_data_pending') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
