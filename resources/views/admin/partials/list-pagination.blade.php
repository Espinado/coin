@if ($paginator->total() > 0)
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:16px;padding:16px 18px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.03);flex-wrap:wrap;">
  <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(232,237,245,0.66);">
    {{ __('coin.pagination.showing', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
  </div>
  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
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
