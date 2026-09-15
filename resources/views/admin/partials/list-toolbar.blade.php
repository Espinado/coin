@php
  $search = $search ?? '';
  $status = $status ?? '';
  $statuses = $statuses ?? null;
  $sort = $sort ?? '';
  $dir = $dir ?? 'desc';
  $perPage = $perPage ?? 20;
  $showSearch = $showSearch ?? true;
  $showStatus = $showStatus ?? false;
  $searchPlaceholder = $searchPlaceholder ?? __('coin.admin.search_placeholder');
@endphp

<form method="GET" action="{{ $action }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
  @if($showSearch)
    <input type="search" name="q" value="{{ $search }}" placeholder="{{ $searchPlaceholder }}"
      style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;min-width:220px;">
  @endif

  @if($showStatus && is_array($statuses))
    <select name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
      <option value="">{{ __('coin.admin.all_statuses') }}</option>
      @foreach($statuses as $value => $label)
        <option value="{{ $value }}" @selected($status === (string) $value)>{{ $label }}</option>
      @endforeach
    </select>
  @endif

  @if($sort !== '')
    <input type="hidden" name="sort" value="{{ $sort }}">
  @endif
  @if($dir !== '')
    <input type="hidden" name="dir" value="{{ $dir }}">
  @endif

  <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(232,237,245,0.72);">
    <span>{{ __('coin.pagination.per_page') }}</span>
    <select name="per_page" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
      @foreach([10, 20, 50, 100] as $option)
        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
      @endforeach
    </select>
  </label>

  <button type="submit" class="admin-btn">{{ __('coin.admin.apply') }}</button>
</form>
