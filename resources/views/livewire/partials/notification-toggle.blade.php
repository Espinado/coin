@props(['enabled' => false, 'target'])

<button type="button" wire:click="{{ $target }}" wire:loading.attr="disabled" wire:target="{{ $target }}" aria-pressed="{{ $enabled ? 'true' : 'false' }}" style="width: 38px; height: 22px; border-radius: 999px; border: 1px solid {{ $enabled ? 'oklch(0.86 0.11 195 / 0.5)' : 'rgba(150,235,250,0.2)' }}; background: {{ $enabled ? 'oklch(0.6 0.13 200 / 0.5)' : 'rgba(150,235,250,0.14)' }}; position: relative; cursor: pointer; padding: 0;">
  <span style="position: absolute; top: 2px; {{ $enabled ? 'right: 2px;' : 'left: 2px;' }} width: 16px; height: 16px; border-radius: 50%; background: {{ $enabled ? '#eafcff' : 'rgba(214,238,248,0.6)' }};"></span>
</button>
