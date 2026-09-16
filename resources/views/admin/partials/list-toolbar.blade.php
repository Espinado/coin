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

<form method="GET" action="{{ $action }}" class="admin-list-toolbar">
  @if($showSearch)
    <input type="search" name="q" value="{{ $search }}" placeholder="{{ $searchPlaceholder }}">
  @endif

  @if($showStatus && is_array($statuses))
    <select name="status">
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

  <label>
    <span>{{ __('coin.pagination.per_page') }}</span>
    <select name="per_page">
      @foreach([10, 20, 50, 100] as $option)
        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
      @endforeach
    </select>
  </label>

  <button type="submit" class="admin-btn">{{ __('coin.admin.apply') }}</button>
</form>
