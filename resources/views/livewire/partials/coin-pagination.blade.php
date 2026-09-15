@php
  $perPageProperty = $perPageProperty ?? null;
  $perPageOptions = $perPageOptions ?? [10, 20, 50];
@endphp

@if ($paginator->total() > 0)
<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(150,235,250,0.1); flex-wrap: wrap;">
  <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
    <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: rgba(214,238,248,0.66);">
      {{ __('coin.pagination.showing', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
    </div>
    @if($perPageProperty)
    <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: rgba(214,238,248,0.72);">
      <span>{{ __('coin.pagination.per_page') }}</span>
      <select wire:model.live="{{ $perPageProperty }}" style="padding: 7px 10px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.2); background: rgba(4,16,28,0.85); color: #e6f4fa; font-family: inherit; font-size: 12.5px; cursor: pointer;">
        @foreach($perPageOptions as $option)
          <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
      </select>
    </label>
    @endif
  </div>
  <div style="display: flex; align-items: center; gap: 8px;">
    @if ($paginator->onFirstPage())
      <span style="padding: 8px 14px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.1); background: rgba(150,235,250,0.03); color: rgba(214,238,248,0.4); font-size: 12.5px;">{{ __('coin.pagination.previous') }}</span>
    @else
      <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" style="padding: 8px 14px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-family: inherit; font-size: 12.5px; cursor: pointer;">{{ __('coin.pagination.previous') }}</button>
    @endif

    <span style="padding: 8px 12px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: rgba(214,238,248,0.78);">
      {{ $paginator->currentPage() }} / {{ max(1, $paginator->lastPage()) }}
    </span>

    @if ($paginator->hasMorePages())
      <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" style="padding: 8px 14px; border-radius: 9px; border: 1px solid oklch(0.86 0.11 195 / 0.45); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 12.5px; font-weight: 600; cursor: pointer;">{{ __('coin.pagination.next') }}</button>
    @else
      <span style="padding: 8px 14px; border-radius: 9px; border: 1px solid rgba(150,235,250,0.1); background: rgba(150,235,250,0.03); color: rgba(214,238,248,0.4); font-size: 12.5px;">{{ __('coin.pagination.next') }}</span>
    @endif
  </div>
</div>
@endif
