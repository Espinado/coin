@if ($paginator->total() > 0)
<div class="admin-pagination">
  <div class="admin-pagination-meta">
    {{ __('coin.pagination.showing', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
  </div>
  <div class="admin-pagination-actions">
    @if ($paginator->onFirstPage())
      <span class="admin-btn" style="opacity:0.45;cursor:default;">{{ __('coin.pagination.previous') }}</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="admin-btn">{{ __('coin.pagination.previous') }}</a>
    @endif

    <span style="padding:8px 12px;font-family:'JetBrains Mono',monospace;font-size:11.5px;color:rgba(232,237,245,0.78);">
      {{ $paginator->currentPage() }} / {{ max(1, $paginator->lastPage()) }}
    </span>

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="admin-btn admin-btn-primary">{{ __('coin.pagination.next') }}</a>
    @else
      <span class="admin-btn admin-btn-primary" style="opacity:0.45;cursor:default;">{{ __('coin.pagination.next') }}</span>
    @endif
  </div>
</div>
@endif
