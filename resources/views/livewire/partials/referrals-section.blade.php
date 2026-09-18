@php
  $tabButton = fn (string $tab, string $label) => $referralTab === $tab
    ? 'padding: 10px 16px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;'
    : 'padding: 10px 16px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.16); background: rgba(150,235,250,0.04); color: rgba(230,244,250,0.82); font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer;';
@endphp

<section data-screen-label="{{ __('coin.nav.referrals') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
  <div style="padding: 28px; border-radius: 18px; border: 1px solid rgba(180,180,255,0.18); background: linear-gradient(120deg, oklch(0.6 0.13 200 / 0.16), rgba(120,110,220,0.12));">
    <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ __('coin.referrals.hero_title') }}</div>
    <p style="margin: 10px 0 0; max-width: 620px; font-size: 14px; line-height: 1.6; color: rgba(214,238,248,0.75);">{{ __('coin.referrals.hero_sub') }}</p>
    <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px; flex-wrap: wrap;">
      <div id="referral-share-url" style="padding: 13px 18px; border-radius: 11px; border: 1px dashed rgba(150,235,250,0.3); background: rgba(4,16,28,0.5); font-family: 'JetBrains Mono', monospace; font-size: 13.5px; color: #eafcff;">{{ $referral?->shareUrl() }}</div>
      <button type="button" wire:click="copyReferralLink" style="padding: 13px 22px; border-radius: 11px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;">{{ __('coin.referrals.copy_link') }}</button>
      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <input type="email" wire:model="referralInviteEmail" placeholder="{{ __('coin.referrals.invite_email_placeholder') }}" style="min-width: 220px; padding: 13px 16px; border-radius: 11px; border: 1px solid rgba(150,235,250,0.2); background: rgba(4,16,28,0.55); color: #eafcff; font-family: inherit; font-size: 13.5px; outline: none;" />
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
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <button type="button" wire:click="setReferralTab('accruals')" style="{{ $tabButton('accruals', __('coin.referrals.accrual_history')) }}">{{ __('coin.referrals.accrual_history') }}</button>
        <button type="button" wire:click="setReferralTab('invited')" style="{{ $tabButton('invited', __('coin.referrals.invited_list')) }}">{{ __('coin.referrals.invited_list') }}</button>
      </div>
      <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">
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
      <div style="display: grid; grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'created_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'user', 'label' => mb_strtoupper(__('coin.table.user')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'plan', 'label' => mb_strtoupper(__('coin.table.plan')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'purchase', 'label' => mb_strtoupper(__('coin.table.purchase')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'commission', 'label' => mb_strtoupper(__('coin.table.commission')), 'sortProperty' => 'referralAccrualSort', 'dirProperty' => 'referralAccrualDir', 'sortMethod' => 'sortReferralAccruals', 'align' => 'right'])</span>
      </div>
      @forelse($referralAccrualPage as $commission)
        <div style="display: grid; grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last || $referralAccrualPage->total() > 0) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;">
          <span style="color: rgba(214,238,248,0.78);">{{ $commission->occurredLabel() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $commission->referralLabel() }}</span>
          <span style="color: rgba(214,238,248,0.78);">{{ $commission->planName() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $commission->formattedPurchaseAmount() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: oklch(0.88 0.12 192);">{{ $commission->formattedCommission() }}</span>
        </div>
      @empty
        @foreach($referralAccruals as $accrual)
          <div style="display: grid; grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px;">
            <span style="color: rgba(214,238,248,0.78);">—</span>
            <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78);">{{ $accrual->user_label }}</span>
            <span style="color: rgba(214,238,248,0.78);">{{ $accrual->plan_name }}</span>
            <span style="color: rgba(214,238,248,0.78);">—</span>
            <span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: oklch(0.88 0.12 192);">{{ $accrual->amount_label }}</span>
          </div>
        @endforeach
        @if($referralAccrualPage->total() === 0 && $referralAccruals->isEmpty())
          <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.referrals.no_commissions') }}</div>
        @endif
      @endforelse
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
      <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 1fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'email', 'label' => mb_strtoupper(__('coin.referrals.invite_contact')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'status', 'label' => mb_strtoupper(__('coin.table.status')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'channel', 'label' => mb_strtoupper(__('coin.referrals.invite_channel')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'sent_at', 'label' => mb_strtoupper(__('coin.referrals.invited_at')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited'])</span>
        <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'registered_at', 'label' => mb_strtoupper(__('coin.referrals.registered_at')), 'sortProperty' => 'referralInvitedSort', 'dirProperty' => 'referralInvitedDir', 'sortMethod' => 'sortReferralInvited', 'align' => 'right'])</span>
      </div>
      @forelse($referralInvitedPage as $invitation)
        <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 1fr); padding: 13px 0;@if(!$loop->last || $referralInvitedPage->total() > 0) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;">
          <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.78); word-break: break-word;">{{ $invitation->displayLabel() }}</span>
          <span style="color: {{ $invitation->isRegistered() ? 'oklch(0.88 0.14 160)' : 'oklch(0.9 0.14 90)' }};">{{ $invitation->statusLabel() }}</span>
          <span style="color: rgba(214,238,248,0.78);">{{ $invitation->channelLabel() }}</span>
          <span style="color: rgba(214,238,248,0.78);">{{ $invitation->formattedSentAt() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: rgba(214,238,248,0.78);">{{ $invitation->formattedRegisteredAt() }}</span>
        </div>
      @empty
        <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.referrals.no_invited') }}</div>
      @endforelse
      @include('livewire.partials.coin-pagination', [
        'paginator' => $referralInvitedPage,
        'perPageProperty' => 'referralInvitedPerPage',
        'perPageOptions' => [10, 20, 50],
      ])
    @endif

    <p style="margin: 22px 0 0; font-size: 11.5px; line-height: 1.5; color: rgba(214,238,248,0.66);">{{ __('coin.referrals.one_level_note') }}</p>
  </div>
</section>
