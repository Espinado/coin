@php
  $column = $column ?? '';
  $label = $label ?? '';
  $sortProperty = $sortProperty ?? 'walletSort';
  $dirProperty = $dirProperty ?? 'walletDir';
  $sortMethod = $sortMethod ?? 'sortWallet';
  $align = $align ?? 'left';
  $currentSort = $this->{$sortProperty} ?? '';
  $currentDir = $this->{$dirProperty} ?? 'desc';
  $isActive = $currentSort === $column;
@endphp

<button
  type="button"
  wire:click="{{ $sortMethod }}('{{ $column }}')"
  style="display: inline-flex; align-items: center; gap: 5px; padding: 0; border: none; background: none; color: inherit; font: inherit; letter-spacing: inherit; cursor: pointer; text-align: {{ $align }};@if($align === 'right') width: 100%; justify-content: flex-end;@endif"
>
  <span>{{ $label }}</span>
  @if($isActive)
    <span style="font-size: 10px; color: oklch(0.86 0.12 192);">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>
  @endif
</button>
