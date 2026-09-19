@php
  $card = 'padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);';
  $hint = 'font-size: 12px; color: rgba(214,238,248,0.66); line-height: 1.45;';
  $selected = $selectedNotificationId ? $userNotifications->firstWhere('id', $selectedNotificationId) : null;
@endphp

<section data-screen-label="{{ __('coin.nav.notifications') }}" style="padding: 28px 32px 40px;">
  <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); gap: 16px; align-items: start;">
    <div style="{{ $card }}">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px;">
        <h2 style="margin: 0; font-size: 15px; font-weight: 600;">{{ __('coin.nav.notifications') }}</h2>
        @if($this->unreadNotificationsCount > 0)
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; padding: 4px 8px; border-radius: 999px; background: oklch(0.86 0.12 192 / 0.18); color: oklch(0.9 0.12 192);">{{ $this->unreadNotificationsCount }} {{ mb_strtolower(__('coin.notifications.unread')) }}</span>
        @endif
      </div>

      @if($userNotifications->isEmpty())
        <p style="margin: 0; {{ $hint }}">{{ __('coin.notifications.empty') }}</p>
      @else
        <div style="display: flex; flex-direction: column; gap: 8px;">
          @foreach($userNotifications as $notification)
            <button
              type="button"
              wire:click="openNotification({{ $notification->id }})"
              wire:key="notification-row-{{ $notification->id }}"
              class="coin-btn-quiet coin-notification-item {{ (int) $selectedNotificationId === (int) $notification->id ? 'coin-notification-item--selected' : '' }}"
              style="width: 100%; text-align: left; padding: 14px 16px; border-radius: 12px; border: 1px solid {{ (int) $selectedNotificationId === (int) $notification->id ? 'rgba(150,235,250,0.28)' : 'rgba(150,235,250,0.12)' }}; background: {{ (int) $selectedNotificationId === (int) $notification->id ? 'rgba(150,235,250,0.06)' : 'rgba(150,235,250,0.03)' }}; color: inherit; cursor: pointer;"
            >
              <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                <div style="min-width: 0;">
                  <div style="font-size: 13.5px; font-weight: {{ $notification->isRead() ? '500' : '600' }}; color: {{ $notification->isRead() ? 'rgba(240,251,255,0.82)' : '#f0fbff' }};">{{ $notification->title }}</div>
                  <div style="margin-top: 5px; font-size: 11.5px; color: rgba(214,238,248,0.62);">{{ $notification->created_at?->format('d.m.Y H:i') }}</div>
                </div>
                @unless($notification->isRead())
                  <span style="flex-shrink: 0; width: 8px; height: 8px; margin-top: 5px; border-radius: 50%; background: oklch(0.86 0.12 192); box-shadow: 0 0 10px oklch(0.86 0.12 192 / 0.8);"></span>
                @endunless
              </div>
            </button>
          @endforeach
        </div>
      @endif
    </div>

    <div style="{{ $card }} min-height: 280px;">
      @if($selected)
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.68);">{{ mb_strtoupper(__('coin.notifications.from_platform')) }} · {{ $selected->created_at?->format('d.m.Y H:i') }}</div>
        <h2 style="margin: 12px 0 0; font-size: 20px; font-weight: 600;">{{ $selected->title }}</h2>
        <div style="margin-top: 18px; font-size: 14px; line-height: 1.7; color: rgba(240,251,255,0.92); white-space: pre-wrap;">{{ $selected->body }}</div>
        <div style="margin-top: 18px; font-size: 12px; color: rgba(214,238,248,0.62);">{{ $selected->isRead() ? __('coin.notifications.read') : __('coin.notifications.unread') }}</div>
      @else
        <p style="margin: 0; {{ $hint }}">{{ __('coin.notifications.open_hint') }}</p>
      @endif
    </div>
  </div>
</section>
