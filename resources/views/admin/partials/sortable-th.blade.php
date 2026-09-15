@php
  $sort = $sort ?? '';
  $dir = $dir ?? 'desc';
  $nextDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
  $query = array_merge(request()->except(['sort', 'dir', 'page']), [
      'sort' => $column,
      'dir' => $nextDir,
  ]);
  $isActive = $sort === $column;
@endphp

<th style="padding:14px 18px;text-align:left;color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
  <a href="{{ request()->url().'?'.http_build_query($query) }}" style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer;@if($isActive) color:#ffb454;@endif" title="{{ __('coin.admin.sort_by_column') }}">
    <span>{{ $label }}</span>
    @if($isActive)
      <span style="font-size:11px;color:#ffb454;">{{ $dir === 'asc' ? '↑' : '↓' }}</span>
    @endif
  </a>
</th>
