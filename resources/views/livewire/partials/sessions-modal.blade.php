@if($sessionsModalOpen)
<div
  class="coin-payment-overlay"
  style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: safe center; justify-content: center; padding: 24px; background: rgba(2, 8, 16, 0.82); backdrop-filter: blur(8px); overflow-y: auto;"
  wire:click="closeSessionsModal"
  wire:keydown.escape.window="closeSessionsModal"
>
  <div
    style="width: min(100%, 560px); max-height: min(90dvh, 820px); margin: auto; border-radius: 20px; border: 1px solid rgba(150,235,250,0.18); background: linear-gradient(170deg, rgba(12, 34, 52, 0.98), rgba(6, 20, 35, 0.98)); box-shadow: 0 32px 80px -24px rgba(0, 0, 0, 0.75); overflow: hidden; overflow-y: auto;"
    wire:click.stop
  >
    <div style="padding: 18px 22px; border-bottom: 1px solid rgba(150,235,250,0.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
      <div style="min-width: 0;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.14em; color: rgba(214,238,248,0.62);">{{ mb_strtoupper(__('coin.profile.active_sessions')) }}</div>
        <div style="margin-top: 8px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; color: #f0fbff;">{{ __('coin.profile.sessions_modal_title') }}</div>
        <div style="margin-top: 6px; font-size: 12.5px; color: rgba(214,238,248,0.72);">{{ __('coin.profile.sessions_modal_hint') }}</div>
      </div>
      <button type="button" wire:click="closeSessionsModal" style="border: 0; background: rgba(150,235,250,0.08); color: rgba(214,238,248,0.78); width: 32px; height: 32px; border-radius: 9px; cursor: pointer; font-size: 18px; line-height: 1; flex: none;">×</button>
    </div>

    <div style="padding: 22px; display: flex; flex-direction: column; gap: 14px;">
      @forelse($this->activeSessionsList as $session)
      <div style="padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.03); display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="min-width: 0;">
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 13.5px; font-weight: 500;">{{ $session['label'] }}</span>
            @if($session['is_current'])
            <span style="padding: 3px 8px; border-radius: 6px; background: oklch(0.6 0.14 160 / 0.2); border: 1px solid oklch(0.7 0.14 160 / 0.4); font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.08em; color: oklch(0.88 0.14 160);">{{ mb_strtoupper(__('coin.profile.sessions_current')) }}</span>
            @endif
          </div>
          <div style="margin-top: 6px; font-size: 12px; color: rgba(214,238,248,0.68); line-height: 1.45;">
            @if($session['ip'])
            <span>{{ __('coin.profile.sessions_ip', ['ip' => $session['ip']]) }}</span>
            <span style="opacity: 0.45;"> · </span>
            @endif
            <span>{{ __('coin.profile.sessions_last_active', ['time' => $session['last_active']]) }}</span>
          </div>
        </div>
        <button
          type="button"
          wire:click="revokeSession('{{ $session['id'] }}')"
          wire:loading.attr="disabled"
          wire:target="revokeSession"
          style="padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,143,143,0.35); background: rgba(255,143,143,0.08); color: #ffb4b4; font-family: inherit; font-size: 12px; font-weight: 600; cursor: pointer; flex: none;"
        >
          {{ $session['is_current'] ? __('coin.profile.sessions_logout') : __('coin.profile.sessions_revoke') }}
        </button>
      </div>
      @empty
      <p style="margin: 0; font-size: 13px; color: rgba(214,238,248,0.72);">{{ __('coin.profile.sessions_list_empty') }}</p>
      @endforelse

      @if(count($this->activeSessionsList) > 1)
      <div style="margin-top: 8px; padding-top: 18px; border-top: 1px solid rgba(150,235,250,0.08); display: flex; flex-direction: column; gap: 12px;">
        <div>
          <div style="font-size: 13.5px; font-weight: 500;">{{ __('coin.profile.sessions_revoke_all_title') }}</div>
          <div style="margin-top: 4px; font-size: 12px; color: rgba(214,238,248,0.66); line-height: 1.45;">{{ __('coin.profile.sessions_revoke_all_hint') }}</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <input type="password" wire:model="sessionsRevokePassword" autocomplete="current-password" placeholder="{{ __('coin.profile.sessions_password_placeholder') }}" style="flex: 1; min-width: 180px; box-sizing: border-box; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.14); background: rgba(4,16,28,0.5); font-size: 13px; color: #f0fbff;" />
          <button type="button" wire:click="revokeOtherSessions" wire:loading.attr="disabled" wire:target="revokeOtherSessions" style="padding: 10px 16px; border-radius: 10px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 12.5px; font-weight: 600; cursor: pointer;">
            {{ __('coin.profile.sessions_revoke_all') }}
          </button>
        </div>
        @error('sessionsRevokePassword')<p style="margin: 0; font-size: 12px; color: #ff8f8f;">{{ $message }}</p>@enderror
      </div>
      @endif
    </div>
  </div>
</div>
@endif
