<section class="coin-dash-section" data-screen-label="{{ __('coin.nav.overview') }}">
  <div class="coin-dash-stat-grid coin-dash-stat-grid--overview">
    <div class="coin-dash-stat-card">
      <div class="coin-dash-stat-card__label">{{ mb_strtoupper(__('coin.wallet.total_balance')) }}</div>
      <div class="coin-dash-stat-card__value coin-dash-stat-card__value--lg">{{ $wallet?->formattedBalance() }}</div>
      <div class="coin-dash-stat-card__hint">{{ $wallet?->currency ?? 'USDT' }}</div>
    </div>
    <div class="coin-dash-stat-card">
      <div class="coin-dash-stat-card__label">{{ mb_strtoupper(__('coin.overview.active_power')) }}</div>
      <div class="coin-dash-stat-card__value coin-dash-stat-card__value--lg">
        {{ $this->totalAllocatedTflops }} <span class="coin-dash-stat-card__unit">TFLOPS</span>
      </div>
      <div class="coin-dash-stat-card__hint">{{ $this->activePowerSubtitle }}</div>
    </div>
    <div class="coin-dash-stat-card">
      <div class="coin-dash-stat-card__label">{{ mb_strtoupper(__('coin.overview.active_plan')) }}</div>
      <div class="coin-dash-stat-card__value coin-dash-stat-card__value--title">{{ $this->primaryPlanTitle }}</div>
      <div class="coin-dash-stat-card__hint">{{ $this->primaryPlanSubtitle }}</div>
    </div>
    <div class="coin-dash-stat-card coin-dash-stat-card--accent">
      <div class="coin-dash-stat-card__label">{{ mb_strtoupper(__('coin.overview.expected_daily_reward')) }}</div>
      <div class="coin-dash-stat-card__value coin-dash-stat-card__value--lg coin-dash-stat-card__value--accent">{{ $user->formattedDailyReward() }}</div>
      <div class="coin-dash-stat-card__hint">{{ $this->dailyRewardHint }}</div>
    </div>
  </div>

  <div class="coin-dash-quick-actions">
    <span class="coin-dash-quick-actions__label">{{ mb_strtoupper(__('coin.actions.quick')) }}</span>
    <button type="button" wire:click="setSection(1)" class="coin-dash-quick-actions__btn coin-dash-quick-actions__btn--primary">{{ __('coin.actions.activate_plan') }}</button>
    <button type="button" wire:click="setSection(4)" class="coin-dash-quick-actions__btn">{{ __('coin.actions.top_up') }}</button>
    <button type="button" wire:click="setSection(4)" class="coin-dash-quick-actions__btn">{{ __('coin.actions.payout') }}</button>
    <button type="button" wire:click="setSection(2)" class="coin-dash-quick-actions__btn">{{ __('coin.actions.increase_power') }}</button>
    <button type="button" wire:click="setSection(5)" class="coin-dash-quick-actions__btn coin-dash-quick-actions__btn--referral">{{ __('coin.actions.invite') }}</button>
  </div>

  <div class="coin-dash-panel-grid coin-dash-panel-grid--split">
    <div class="coin-dash-panel">
      <div class="coin-dash-panel__head">
        <div>
          <div class="coin-dash-panel__title">{{ __('coin.overview.accruals') }}</div>
          <div class="coin-dash-panel__sub">{{ __('coin.overview.accruals_sub') }}</div>
        </div>
        <div class="coin-dash-period-tabs">
          @foreach([2 => '30D', 1 => '14D', 0 => '24H'] as $chartPeriod => $chartLabel)
            <button type="button" wire:click="setAccrualChartPeriod({{ $chartPeriod }})" class="coin-dash-period-tabs__btn {{ $accrualChartPeriod === $chartPeriod ? 'coin-dash-period-tabs__btn--active' : '' }}">
              {{ $chartLabel }}
            </button>
          @endforeach
        </div>
      </div>
      @php $accrualsChart = $this->accrualsChart; @endphp
      @if($accrualsChart['hasData'])
        <div class="coin-dash-chart-wrap">
          <x-profit-line-chart :chart="$accrualsChart" :height="176" area-id="overviewAccrualsArea" line-id="overviewAccrualsLine" />
        </div>
      @else
        <div class="coin-dash-empty">{{ __('coin.stats.no_profit_yet') }}</div>
      @endif
    </div>

    @php $allocation = $this->planAllocation; @endphp
    <div class="coin-dash-panel">
      <div class="coin-dash-panel__title">{{ __('coin.overview.portfolio_allocation') }}</div>
      <div class="coin-dash-panel__sub">{{ __('coin.overview.portfolio_allocation_sub') }}</div>
      @if($allocation['items'])
        <div class="coin-dash-allocation">
          <div class="coin-dash-allocation__ring" style="background: conic-gradient({{ $allocation['gradient'] }});">
            <div class="coin-dash-allocation__ring-inner">
              <div class="coin-dash-allocation__ring-value">{{ $allocation['utilized'] }}%</div>
              <div class="coin-dash-allocation__ring-label">{{ mb_strtoupper(__('coin.locked')) }}</div>
            </div>
          </div>
          <div class="coin-dash-allocation__legend">
            @foreach($allocation['items'] as $item)
              <div class="coin-dash-allocation__legend-row">
                <span class="coin-dash-allocation__swatch" style="background: {{ $item['color'] }};"></span>
                <span class="coin-dash-allocation__name">{{ $item['name'] }}</span>
                <span class="coin-dash-allocation__percent">{{ $item['percent'] }}%</span>
              </div>
            @endforeach
          </div>
        </div>
      @else
        <div class="coin-dash-empty coin-dash-empty--compact">{{ __('coin.overview.no_allocation') }}</div>
      @endif
    </div>
  </div>

  <div class="coin-dash-stat-grid coin-dash-stat-grid--widgets">
    <div class="coin-dash-panel">
      <div class="coin-dash-panel__head coin-dash-panel__head--compact">
        <span class="coin-dash-panel__title">{{ __('coin.invest.active_investments') }}</span>
        <button type="button" wire:click="setSection(2)" class="coin-dash-link-btn">{{ mb_strtoupper(__('coin.actions.all_investments')) }}</button>
      </div>
      <div class="coin-dash-contract-list">
        @foreach($activeContracts as $contract)
          <div>
            <div class="coin-dash-contract-list__row">
              <span>{{ $contract->plan?->displayName() }} · {{ $contract->formattedTflops() }} TFLOPS</span>
              <span class="coin-dash-contract-list__pct">{{ $contract->computedProgressPercent() }}%</span>
            </div>
            <div class="coin-dash-progress"><div class="coin-dash-progress__fill" style="width: {{ $contract->computedProgressPercent() }}%;"></div></div>
          </div>
        @endforeach
        <div class="coin-dash-contract-list__meta">
          <span>{{ __('coin.overview.next_accrual') }}</span>
          <span>{{ $this->nextSettlementLabel }}</span>
        </div>
      </div>
    </div>

    <div class="coin-dash-panel">
      <div class="coin-dash-panel__head coin-dash-panel__head--compact">
        <span class="coin-dash-panel__title">{{ __('coin.nav.wallet') }}</span>
        <button type="button" wire:click="setSection(4)" class="coin-dash-link-btn">{{ mb_strtoupper(__('coin.actions.open')) }}</button>
      </div>
      <div class="coin-dash-kv">
        <div class="coin-dash-kv__row"><span>{{ __('coin.available') }}</span><span>{{ $wallet?->formattedAvailable() }}</span></div>
        <div class="coin-dash-kv__row"><span>{{ __('coin.wallet.pending_settlement') }}</span><span>{{ $wallet?->formattedPending() }}</span></div>
        <div class="coin-dash-kv__row"><span>{{ __('coin.wallet.payout_address') }}</span><span class="coin-dash-kv__mono">{{ $wallet?->payout_address }}</span></div>
      </div>
      <div class="coin-dash-panel__actions">
        <button type="button" wire:click="setSection(4)" class="coin-dash-panel__action coin-dash-panel__action--primary">{{ __('coin.actions.top_up') }}</button>
        <button type="button" wire:click="setSection(4)" class="coin-dash-panel__action">{{ __('coin.actions.payout') }}</button>
      </div>
    </div>

    <div class="coin-dash-panel coin-dash-panel--referral">
      <div class="coin-dash-panel__head coin-dash-panel__head--compact">
        <span class="coin-dash-panel__title">{{ __('coin.nav.referrals') }}</span>
        <button type="button" wire:click="setSection(5)" class="coin-dash-link-btn">{{ mb_strtoupper(__('coin.actions.open')) }}</button>
      </div>
      <div class="coin-dash-referral-head">
        <span class="coin-dash-referral-head__value">{{ $referral?->invitationsCount() ?? 0 }}</span>
        <span class="coin-dash-referral-head__unit">{{ __('coin.overview.invited') }}</span>
      </div>
      <div class="coin-dash-kv">
        <div class="coin-dash-kv__row"><span>{{ __('coin.overview.referral_rewards') }}</span><span class="coin-dash-kv__accent">{{ $referral?->formattedTotalRewards() }}</span></div>
        <div class="coin-dash-kv__row"><span>{{ __('coin.overview.commission_share') }}</span><span>{{ $referral?->commissionLabel() }}</span></div>
      </div>
      <button type="button" wire:click="setSection(5)" class="coin-dash-panel__action coin-dash-panel__action--referral">{{ __('coin.overview.invite_friends') }}</button>
    </div>
  </div>

  <div class="coin-dash-panel-grid coin-dash-panel-grid--split">
    <div class="coin-dash-panel coin-dash-panel--table">
      <div class="coin-dash-panel__head coin-dash-panel__head--compact">
        <span class="coin-dash-panel__title">{{ __('coin.overview.recent_activity') }}</span>
        <span class="coin-dash-panel__meta">{{ mb_strtoupper(__('coin.overview.last_5_entries')) }}</span>
      </div>
      <div class="coin-dash-table-head">
        <span>{{ mb_strtoupper(__('coin.table.time')) }}</span>
        <span>{{ mb_strtoupper(__('coin.table.type')) }}</span>
        <span>{{ mb_strtoupper(__('coin.table.source')) }}</span>
        <span>{{ mb_strtoupper(__('coin.table.amount')) }}</span>
      </div>
      @foreach($transactions->take(5) as $transaction)
        <div class="coin-dash-table-row">
          <span>{{ $transaction->formattedOccurredAt() }}</span>
          <span>{{ $transaction->displayType() }}</span>
          <span>{{ $transaction->displaySource() }}</span>
          <span style="color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span>
        </div>
      @endforeach
    </div>

    @include('livewire.partials.overview-plan-breakdown')
  </div>
</section>
