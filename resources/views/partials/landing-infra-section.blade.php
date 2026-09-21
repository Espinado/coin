<section id="infra" class="coin-landing-section coin-landing-infra" data-screen-label="Инфраструктура">
  <div class="coin-landing-section__inner">
    <div class="coin-landing-infra__grid">
      <div>
        <div class="coin-landing-section__kicker">ИНФРАСТРУКТУРА</div>
        <h2 class="coin-landing-section__title">Вычислительная инфраструктура на базе дата-центров</h2>
        <p class="coin-landing-infra__lead">Платформа строится вокруг собственных вычислительных площадок. Оборудование, охлаждение, питание и мониторинг остаются на нашей стороне — вы работаете только с мощностью и начислениями.</p>

        <div class="coin-landing-infra__stats">
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">АКТИВНЫХ ТАРИФОВ</div>
            <div class="coin-landing-infra-stat__value">{{ $landingStats['active_plan_count'] ?? $activePlanCount }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">В ИНВЕСТИЦИЯХ</div>
            <div class="coin-landing-infra-stat__value">{{ $landingStats['total_locked_compact'] }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">АКТИВНЫХ КОНТРАКТОВ</div>
            <div class="coin-landing-infra-stat__value">{{ number_format($landingStats['active_contracts'], 0, '.', ' ') }}</div>
          </div>
          <div class="coin-landing-infra-stat">
            <div class="coin-landing-infra-stat__label">ДОСТУПНОСТЬ</div>
            <div class="coin-landing-infra-stat__value">99,9%</div>
          </div>
        </div>
      </div>

      <div class="coin-landing-infra-panel">
        <div class="coin-landing-infra-panel__head">
          <span class="coin-landing-infra-panel__title">СТАТУС ПЛОЩАДОК</span>
          <span class="coin-landing-infra-panel__badge">24/7</span>
        </div>

        <div class="coin-landing-infra-panel__list">
          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">Дата-центр 01</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--online">
                <span class="coin-landing-infra-dc__dot"></span>РАБОТАЕТ
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill" style="width: 84%;"></div></div>
            <div class="coin-landing-infra-dc__load">ЗАГРУЗКА 84%</div>
          </div>

          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">Дата-центр 02</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--online">
                <span class="coin-landing-infra-dc__dot"></span>РАБОТАЕТ
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill coin-landing-infra-dc__fill--mid" style="width: 91%;"></div></div>
            <div class="coin-landing-infra-dc__load">ЗАГРУЗКА 91%</div>
          </div>

          <div class="coin-landing-infra-dc">
            <div class="coin-landing-infra-dc__head">
              <span class="coin-landing-infra-dc__name">Дата-центр 03</span>
              <span class="coin-landing-infra-dc__status coin-landing-infra-dc__status--expand">
                <span class="coin-landing-infra-dc__dot coin-landing-infra-dc__dot--expand"></span>РАСШИРЕНИЕ
              </span>
            </div>
            <div class="coin-landing-infra-dc__bar"><div class="coin-landing-infra-dc__fill coin-landing-infra-dc__fill--expand" style="width: 46%;"></div></div>
            <div class="coin-landing-infra-dc__load">ЗАГРУЗКА 46%</div>
          </div>

          <div class="coin-landing-infra-dc coin-landing-infra-dc--placeholder">
            <span class="coin-landing-infra-dc__name">Дата-центр 04 — 07</span>
            <span class="coin-landing-infra-dc__tbc">ДАННЫЕ УТОЧНЯЮТСЯ</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
