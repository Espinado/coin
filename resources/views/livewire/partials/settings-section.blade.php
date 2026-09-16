@php
  $card = 'padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); height: 100%; box-sizing: border-box; display: flex; flex-direction: column;';
  $cardTitle = 'font-size: 15px; font-weight: 600; margin: 0;';
  $cardBody = 'margin-top: 20px; display: flex; flex-direction: column; gap: 14px; flex: 1;';
  $fieldLabel = 'font-family: "JetBrains Mono", monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);';
  $input = 'width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.14);background:rgba(4,16,28,0.5);font-size:13.5px;color:#f0fbff;';
  $hint = 'font-size: 12px; color: rgba(214,238,248,0.66); line-height: 1.45;';
  $error = 'margin: 0; font-size: 12px; color: #ff8f8f;';
  $btnSecondary = 'padding: 11px 20px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer; align-self: flex-start;';
  $btnPrimary = 'padding: 11px 20px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; align-self: flex-start;';
  $innerRow = 'padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03);';
  $grid = 'display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: stretch;';
@endphp

<section data-screen-label="{{ __('coin.nav.settings') }}" style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
  <div style="{{ $grid }}">
    {{-- Профиль и контакты --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.title') }}</h2>
      <div style="display: flex; align-items: center; gap: 16px; margin-top: 20px; padding-bottom: 18px; border-bottom: 1px solid rgba(150,235,250,0.08);">
        <div style="width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(145deg, oklch(0.7 0.13 198), oklch(0.5 0.15 285)); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 17px; color: #04121f; flex-shrink: 0;">{{ $user->avatarInitial() }}</div>
        <div style="min-width: 0;">
          <div style="font-size: 15px; font-weight: 500;">{{ $user->accountLabel() }}</div>
          <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.72); word-break: break-all;">{{ $user->email }}</div>
        </div>
      </div>
      <div style="{{ $cardBody }}">
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.display_name')) }}</div>
          <div style="margin-top: 8px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(4,16,28,0.5); font-size: 13.5px; color: rgba(214,238,248,0.85);">{{ $user->name }}</div>
        </div>
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.phone')) }}</div>
          <input type="text" wire:model="profilePhone" style="{{ $input }}; margin-top: 8px;" />
        </div>
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.telegram')) }}</div>
          <input type="text" wire:model="profileTelegram" style="{{ $input }}; margin-top: 8px;" />
        </div>
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.country_iso')) }}</div>
          <input type="text" wire:model="profileCountry" maxlength="2" style="{{ $input }}; margin-top: 8px;" />
        </div>
        @error('profilePhone')<p style="{{ $error }}">{{ $message }}</p>@enderror
        @error('profileTelegram')<p style="{{ $error }}">{{ $message }}</p>@enderror
        @error('profileCountry')<p style="{{ $error }}">{{ $message }}</p>@enderror
        <button type="button" wire:click="saveProfile" style="{{ $btnPrimary }}; margin-top: auto;">{{ __('coin.profile.save_contacts') }}</button>
      </div>
    </div>

    {{-- Безопасность --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.security') }}</h2>
      <div style="{{ $cardBody }}; gap: 12px;">
        <div style="{{ $innerRow }}; display: flex; flex-direction: column; gap: 14px;">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
              <div style="font-size: 13.5px;">{{ __('coin.profile.two_factor') }}</div>
              <div style="margin-top: 4px; {{ $hint }}">{{ __('coin.profile.email_two_factor_hint') }}</div>
            </div>
            @if($user->hasEmailTwoFactorEnabled())
            <span style="padding: 5px 11px; border-radius: 7px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 10px; color: oklch(0.88 0.14 160);">{{ mb_strtoupper(__('coin.profile.enabled')) }}</span>
            @else
            <span style="padding: 5px 11px; border-radius: 7px; background: rgba(150,235,250,0.06); border: 1px solid rgba(150,235,250,0.16); font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.profile.disabled')) }}</span>
            @endif
          </div>
          @if($user->hasEmailTwoFactorEnabled())
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <input type="password" wire:model="profileTwoFactorPassword" autocomplete="current-password" placeholder="{{ __('coin.profile.two_factor_password_placeholder') }}" style="{{ $input }}; flex: 1; min-width: 180px;" />
            <button type="button" wire:click="disableEmailTwoFactor" wire:loading.attr="disabled" wire:target="disableEmailTwoFactor" style="{{ $btnSecondary }}; padding: 10px 16px; font-size: 12.5px;">{{ __('coin.profile.disable_two_factor') }}</button>
          </div>
          @error('profileTwoFactorPassword')<p style="{{ $error }}">{{ $message }}</p>@enderror
          @else
          <button type="button" wire:click="enableEmailTwoFactor" wire:loading.attr="disabled" wire:target="enableEmailTwoFactor" style="{{ $btnPrimary }}; padding: 10px 16px; font-size: 12.5px;">{{ __('coin.profile.enable_two_factor') }}</button>
          @endif
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <div>
            <div style="font-size: 13.5px;">{{ __('coin.profile.payout_whitelist') }}</div>
            <div style="margin-top: 4px; {{ $hint }}">{{ __('coin.profile.confirmed_address') }}</div>
          </div>
          <span style="padding: 5px 11px; border-radius: 7px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 10px; color: oklch(0.88 0.14 160);">{{ mb_strtoupper(__('coin.profile.on')) }}</span>
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <div>
            <div style="font-size: 13.5px;">{{ __('coin.profile.active_sessions') }}</div>
            <div style="margin-top: 4px; {{ $hint }}">{{ __('coin.profile.sessions_devices') }}</div>
          </div>
          <button type="button" style="{{ $btnSecondary }}; padding: 8px 14px; font-size: 12.5px;">{{ __('coin.profile.review') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div style="{{ $grid }}">
    {{-- E-mail --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.email_section') }}</h2>
      <p style="margin: 8px 0 0; {{ $hint }}">{{ __('coin.profile.email_hint') }}</p>
      <div style="{{ $cardBody }}">
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.auth.email')) }}</div>
          <input type="email" wire:model="profileEmail" autocomplete="email" style="{{ $input }}; margin-top: 8px;" />
        </div>
        @error('profileEmail')<p style="{{ $error }}">{{ $message }}</p>@enderror
        <button type="button" wire:click="saveProfileEmail" wire:loading.attr="disabled" wire:target="saveProfileEmail" style="{{ $btnSecondary }}; margin-top: auto;">
          <span wire:loading.remove wire:target="saveProfileEmail">{{ __('coin.profile.save_email') }}</span>
          <span wire:loading wire:target="saveProfileEmail">{{ __('coin.profile.saving_email') }}</span>
        </button>
      </div>
    </div>

    {{-- Смена пароля --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.password_section') }}</h2>
      <p style="margin: 8px 0 0; {{ $hint }}">{{ __('coin.profile.password_hint') }}</p>
      <div style="{{ $cardBody }}">
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.current_password')) }}</div>
          <input type="password" wire:model="profileCurrentPassword" autocomplete="current-password" placeholder="{{ __('coin.auth.password_placeholder') }}" style="{{ $input }}; margin-top: 8px;" />
        </div>
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.new_password')) }}</div>
          <input type="password" wire:model="profileNewPassword" autocomplete="new-password" placeholder="{{ __('coin.auth.password_new_placeholder') }}" style="{{ $input }}; margin-top: 8px;" />
        </div>
        <div>
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.auth.password_confirm')) }}</div>
          <input type="password" wire:model="profileNewPasswordConfirmation" autocomplete="new-password" placeholder="{{ __('coin.auth.password_confirm_placeholder') }}" style="{{ $input }}; margin-top: 8px;" />
        </div>
        @error('profileCurrentPassword')<p style="{{ $error }}">{{ $message }}</p>@enderror
        @error('profileNewPassword')<p style="{{ $error }}">{{ $message }}</p>@enderror
        @error('profileNewPasswordConfirmation')<p style="{{ $error }}">{{ $message }}</p>@enderror
        <button type="button" wire:click="saveProfilePassword" wire:loading.attr="disabled" wire:target="saveProfilePassword" style="{{ $btnPrimary }}; margin-top: auto;">
          <span wire:loading.remove wire:target="saveProfilePassword">{{ __('coin.profile.save_password') }}</span>
          <span wire:loading wire:target="saveProfilePassword">{{ __('coin.profile.saving_password') }}</span>
        </button>
      </div>
    </div>
  </div>

  <div style="{{ $grid }}">
    {{-- Кошелёк --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.connected_wallet') }}</h2>
      <div style="{{ $cardBody }}; gap: 16px;">
        <div style="{{ $innerRow }}; border-style: dashed;">
          <div style="{{ $fieldLabel }}">{{ mb_strtoupper(__('coin.profile.primary_payouts')) }}</div>
          <div style="margin-top: 9px; font-family: 'JetBrains Mono', monospace; font-size: 13px; word-break: break-all;">{{ $wallet?->payout_address }}</div>
          <div style="margin-top: 10px; display: flex; align-items: center; gap: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: oklch(0.88 0.14 160);">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span>{{ mb_strtoupper(__('coin.profile.confirmed')) }}
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;">
          <button type="button" style="{{ $btnSecondary }}; width: 100%; text-align: center; align-self: stretch;">{{ __('coin.profile.add_address') }}</button>
          <button type="button" style="{{ $btnSecondary }}; width: 100%; text-align: center; align-self: stretch;">{{ __('coin.profile.disconnect') }}</button>
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: auto;">
          <div>
            <div style="font-size: 13.5px;">{{ __('coin.profile.kyc') }}</div>
            <div style="margin-top: 4px; {{ $hint }}">{{ __('coin.profile.kyc_hint') }}</div>
          </div>
          <span style="padding: 5px 11px; border-radius: 7px; font-family: 'JetBrains Mono', monospace; font-size: 10px; {{ $user->kycBadgeStyle() }}">{{ mb_strtoupper($user->kycLabel()) }}</span>
        </div>
      </div>
    </div>

    {{-- Уведомления --}}
    <div style="{{ $card }}">
      <h2 style="{{ $cardTitle }}">{{ __('coin.profile.notifications') }}</h2>
      <div style="{{ $cardBody }}; gap: 12px;">
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <span style="font-size: 13.5px;">{{ __('coin.profile.reward_credit') }}</span>
          @include('livewire.partials.notification-toggle', ['enabled' => $user->notify_profit_credit, 'target' => 'toggleNotifyProfitCredit'])
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <span style="font-size: 13.5px;">{{ __('coin.profile.contract_expiry') }}</span>
          @include('livewire.partials.notification-toggle', ['enabled' => $user->notify_contract_expiry, 'target' => 'toggleNotifyContractExpiry'])
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <span style="font-size: 13.5px;">{{ __('coin.profile.maturity_alerts') }}</span>
          @include('livewire.partials.notification-toggle', ['enabled' => $user->notify_maturity_alerts, 'target' => 'toggleNotifyMaturityAlerts'])
        </div>
        <div style="{{ $innerRow }}; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
          <div>
            <span style="font-size: 13.5px;">{{ __('coin.profile.referral_activity') }}</span>
            <p style="margin: 4px 0 0; font-size: 11.5px; color: oklch(0.72 0.02 250); max-width: 28rem;">{{ __('coin.profile.referral_activity_hint') }}</p>
          </div>
          @include('livewire.partials.notification-toggle', ['enabled' => $user->notify_referral_activity, 'target' => 'toggleNotifyReferralActivity'])
        </div>
      </div>
    </div>
  </div>
</section>
