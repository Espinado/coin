@php
  $perPageProperty = $perPageProperty ?? null;
  $perPageOptions = $perPageOptions ?? [10, 20, 50];
@endphp

@if ($paginator->total() > 0)
<div class="coin-pagination">
  <div class="coin-pagination__meta">
    <div class="coin-pagination__range">
      {{ __('coin.pagination.showing', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
    </div>
    @if($perPageProperty)
    <label class="coin-pagination__per-page">
      <span>{{ __('coin.pagination.per_page') }}</span>
      <select wire:model.live="{{ $perPageProperty }}">
        @foreach($perPageOptions as $option)
          <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
      </select>
    </label>
    @endif
  </div>
  <div class="coin-pagination__actions">
    @if ($paginator->onFirstPage())
      <span class="coin-pagination__btn coin-pagination__btn--disabled">{{ __('coin.pagination.previous') }}</span>
    @else
      <button type="button" class="coin-pagination__btn coin-pagination__btn--primary" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">{{ __('coin.pagination.previous') }}</button>
    @endif

    <span class="coin-pagination__page">
      {{ $paginator->currentPage() }} / {{ max(1, $paginator->lastPage()) }}
    </span>

    @if ($paginator->hasMorePages())
      <button type="button" class="coin-pagination__btn coin-pagination__btn--primary" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">{{ __('coin.pagination.next') }}</button>
    @else
      <span class="coin-pagination__btn coin-pagination__btn--disabled">{{ __('coin.pagination.next') }}</span>
    @endif
  </div>
</div>
@endif
