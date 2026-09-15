@if($actionMessage && ! filled($paymentModal))
  @php
    $tone = $actionMessageTone ?? 'info';
    $toneClass = match ($tone) {
        'success' => 'coin-action-toast--success',
        'error' => 'coin-action-toast--error',
        default => 'coin-action-toast--info',
    };
    $icon = match ($tone) {
        'error' => '!',
        default => '✓',
    };
  @endphp
  <div
    wire:key="action-toast-{{ md5($actionMessage.$tone) }}"
    x-data="{ visible: true }"
    x-show="visible"
    x-transition.opacity.duration.250ms
    x-init="setTimeout(() => { visible = false; $wire.set('actionMessage', null); $wire.set('actionMessageTone', null); }, 5500)"
    class="coin-action-toast {{ $toneClass }}"
    role="status"
    aria-live="polite"
  >
    <span class="coin-action-toast__icon">{{ $icon }}</span>
    <span class="coin-action-toast__text">{{ $actionMessage }}</span>
    <button
      type="button"
      class="coin-action-toast__close"
      aria-label="{{ __('coin.close') }}"
      @click="visible = false; $wire.set('actionMessage', null); $wire.set('actionMessageTone', null);"
    >×</button>
  </div>
@endif
