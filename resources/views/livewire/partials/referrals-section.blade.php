<section data-screen-label="{{ __('coin.nav.referrals') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
  <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(180,180,255,0.18); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.12));">
    <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ __('coin.referrals.hero_title') }}</div>
    <p style="margin: 10px 0 0; max-width: 620px; font-size: 14px; line-height: 1.6; color: rgba(214,238,248,0.75);">{{ __('coin.referrals.hero_sub') }}</p>
    <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px; flex-wrap: wrap;">
      <div id="referral-share-url" style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.3); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13.5px; color: #eafcff;">{{ $referral?->shareUrl() }}</div>
      <button type="button" wire:click="copyReferralLink" style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.referrals.copy_link') }}</button>
      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <input type="email" wire:model="referralInviteEmail" placeholder="{{ __('coin.referrals.invite_email_placeholder') }}" style="min-width: 220px; padding: 13px 16px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(4,16,28,0.55); color: #eafcff; font-family: inherit; font-size: 16px; outline: none;" />
        <button type="button" wire:click="sendReferralInvite" wire:loading.attr="disabled" wire:target="sendReferralInvite" style="padding: 13px 20px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">
          <span wire:loading.remove wire:target="sendReferralInvite">{{ __('coin.referrals.invite_email') }}</span>
          <span wire:loading wire:target="sendReferralInvite">{{ __('coin.referrals.invite_sending') }}</span>
        </button>
      </div>
      @error('referralInviteEmail')<p style="width: 100%; margin: 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
    <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.invited')) }}</div>
      <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->invitationsCount() ?? 0 }}</div>
    </div>
    <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.invest.active_investments')) }}</div>
      <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->referralInvestmentsCount() ?? 0 }}</div>
      <div style="margin-top: 7px; font-size: 12px; color: rgba(214,238,248,0.7);">{{ __('coin.referrals.referral_investments_hint') }}</div>
    </div>
    <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.referral_rewards')) }}</div>
      <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: oklch(0.9 0.12 192);">{{ $referral?->formattedRewardsBalance() }}</div>
    </div>
    <div style="padding: 20px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);">
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.14em; color: rgba(214,238,248,0.7);">{{ mb_strtoupper(__('coin.referrals.commission_share')) }}</div>
      <div style="margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 24px; color: #f0fbff;">{{ $referral?->commissionLabel() }}</div>
    </div>
  </div>

  <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
    @php
      $referralTabBtnBase = 'box-sizing:border-box;width:100%;min-height:42px;padding:10px 14px;border-radius:10px;font-family:inherit;font-size:13px;text-align:center;cursor:pointer;appearance:none;-webkit-appearance:none;';
      $referralTabBtnActive = $referralTabBtnBase.'border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-weight:600;box-shadow:0 20px 46px -22px oklch(0.8 0.13 195 / 0.85);';
      $referralTabBtnIdle = $referralTabBtnBase.'border:1px solid rgba(150,235,250,0.2);background:rgba(4,16,28,0.55);color:#e6f4fa;font-weight:500;box-shadow:none;';
    @endphp
    <div class="coin-referral-tabs-bar">
      <div class="coin-referral-tabs" role="tablist" aria-label="{{ __('coin.nav.referrals') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;flex:1 1 280px;min-width:0;max-width:420px;">
        <button type="button" role="tab" wire:click="setReferralTab('accruals')" aria-selected="{{ $referralTab === 'accruals' ? 'true' : 'false' }}" class="coin-referral-tabs__btn {{ $referralTab === 'accruals' ? 'coin-referral-tabs__btn--active' : '' }}" style="{{ $referralTab === 'accruals' ? $referralTabBtnActive : $referralTabBtnIdle }}">{{ __('coin.referrals.accrual_history') }}</button>
        <button type="button" role="tab" wire:click="setReferralTab('invited')" aria-selected="{{ $referralTab === 'invited' ? 'true' : 'false' }}" class="coin-referral-tabs__btn {{ $referralTab === 'invited' ? 'coin-referral-tabs__btn--active' : '' }}" style="{{ $referralTab === 'invited' ? $referralTabBtnActive : $referralTabBtnIdle }}">{{ __('coin.referrals.invited_list') }}</button>
      </div>
      <span class="coin-referral-tabs-bar__meta" style="font-family:'JetBrains Mono',monospace;font-size:10.5px;color:rgba(214,238,248,0.66);">
        @if($referralTab === 'accruals')
          {{ __('coin.stats.total_entries', ['count' => $referralAccrualPage->total()]) }}
        @else
          {{ __('coin.stats.total_entries', ['count' => $referralInvitedPage->total()]) }}
        @endif
      </span>
    </div>

    @if($referralTab === 'accruals')
      @include('livewire.partials.transaction-list-toolbar', [
        'searchProperty' => 'referralAccrualSearch',
        'placeholder' => __('coin.referrals.search_accruals'),
      ])
      <div class="coin-data-list coin-data-list--referrals">
        <div class="coin-data-list__head">
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'created_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'user', 'label' => mb_strtoupper(__('coin.table.user')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'plan', 'label' => mb_strtoupper(__('coin.table.source')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'commission', 'label' => mb_strtoupper(__('coin.table.commission')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals', 'align' => 'right'])</span>
        </div>
        @forelse($referralAccrualPage as $commission)
          <article class="coin-data-list__row coin-referral-accrual">
            <span class="coin-referral-accrual__date">{{ $commission->occurredLabel() }}</span>
            <span class="coin-referral-accrual__user">{{ $commission->referralLabel() }}</span>
            <span class="coin-referral-accrual__detail">
              <span class="coin-referral-accrual__plan">{{ $commission->planName() }}</span>
              <span class="coin-referral-accrual__sep" aria-hidden="true">·</span>
              <span class="coin-referral-accrual__purchase">{{ $commission->formattedPurchaseAmount() }}</span>
            </span>
            <span class="coin-referral-accrual__commission">{{ $commission->formattedCommission() }}</span>
          </article>
        @empty
          @foreach($referralAccruals as $accrual)
            <article class="coin-data-list__row coin-referral-accrual">
              <span class="coin-referral-accrual__date">—</span>
              <span class="coin-referral-accrual__user">{{ $accrual->user_label }}</span>
              <span class="coin-referral-accrual__detail">
                <span class="coin-referral-accrual__plan">{{ $accrual->plan_name }}</span>
              </span>
              <span class="coin-referral-accrual__commission">{{ $accrual->amount_label }}</span>
            </article>
          @endforeach
          @if($referralAccrualPage->total() === 0 && $referralAccruals->isEmpty())
            <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.referrals.no_commissions') }}</div>
          @endif
        @endforelse
      </div>
      @include('livewire.partials.coin-pagination', [
        'paginator' => $referralAccrualPage,
        'perPageProperty' => 'referralAccrualPerPage',
        'perPageOptions' => [10, 20, 50],
      ])
    @else
      @include('livewire.partials.transaction-list-toolbar', [
        'searchProperty' => 'referralInvitedSearch',
        'placeholder' => __('coin.referrals.search_invited'),
      ])
      <div class="coin-data-list coin-data-list--invited">
        <div class="coin-data-list__head">
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'email', 'label' => mb_strtoupper(__('coin.referrals.invite_contact')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'status', 'label' => mb_strtoupper(__('coin.table.status')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'channel', 'label' => mb_strtoupper(__('coin.referrals.invite_channel')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'sent_at', 'label' => mb_strtoupper(__('coin.referrals.invited_at')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'registered_at', 'label' => mb_strtoupper(__('coin.referrals.registered_at')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited', 'align' => 'right'])</span>
        </div>
        @forelse($referralInvitedPage as $invitation)
          <article class="coin-data-list__row coin-referral-invite">
            <span class="coin-referral-invite__contact">{{ $invitation->displayLabel() }}</span>
            <span class="coin-referral-invite__status" style="color: {{ $invitation->isRegistered() ? 'oklch(0.88 0.14 160)' : 'oklch(0.9 0.14 90)' }};">{{ $invitation->statusLabel() }}</span>
            <span class="coin-referral-invite__channel">{{ $invitation->channelLabel() }}</span>
            <span class="coin-referral-invite__sent">{{ $invitation->formattedSentAt() }}</span>
            <span class="coin-referral-invite__registered">{{ $invitation->formattedRegisteredAt() }}</span>
          </article>
        @empty
          <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.referrals.no_invited') }}</div>
        @endforelse
      </div>
      @include('livewire.partials.coin-pagination', [
        'paginator' => $referralInvitedPage,
        'perPageProperty' => 'referralInvitedPerPage',
        'perPageOptions' => [10, 20, 50],
      ])
    @endif

    <p style="margin: 22px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.referrals.one_level_note') }}</p>
  </div>
</section>
