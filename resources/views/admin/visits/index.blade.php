@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.visits')]))

@section('content')
    @include('admin.partials.finance-tabs', ['active' => 'visits'])

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h2 style="margin:0;font-size:18px;font-weight:600;">{{ __('coin.admin.visits') }}</h2>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.visits_sub') }}</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:18px;">
            <div style="padding:16px 18px;border-radius:10px;background:rgba(150,235,250,0.06);border:1px solid rgba(150,235,250,0.14);">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.visits_unique_ips')) }}</div>
                <div style="margin-top:8px;font-size:28px;font-weight:600;">{{ number_format($uniqueIps) }}</div>
            </div>
            <div style="padding:16px 18px;border-radius:10px;background:rgba(150,235,250,0.06);border:1px solid rgba(150,235,250,0.14);">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.visits_hits')) }}</div>
                <div style="margin-top:8px;font-size:28px;font-weight:600;">{{ number_format($hits) }}</div>
            </div>
            <div style="padding:16px 18px;border-radius:10px;background:rgba(150,235,250,0.06);border:1px solid rgba(150,235,250,0.14);">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.visits_active_days')) }}</div>
                <div style="margin-top:8px;font-size:28px;font-weight:600;">{{ number_format($activeDays) }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.visits.index') }}" class="admin-list-toolbar">
            <select name="period">
                <option value="all" @selected($period === 'all' || $period === '')>{{ __('coin.admin.period_all') }}</option>
                <option value="today" @selected($period === 'today')>{{ __('coin.admin.period_today') }}</option>
                <option value="week" @selected($period === 'week')>{{ __('coin.admin.period_week') }}</option>
                <option value="month" @selected($period === 'month')>{{ __('coin.admin.period_month') }}</option>
                <option value="custom" @selected($period === 'custom')>{{ __('coin.admin.period_custom') }}</option>
            </select>

            <input type="date" name="from" value="{{ $from }}" aria-label="{{ __('coin.admin.period_from') }}">
            <input type="date" name="to" value="{{ $to }}" aria-label="{{ __('coin.admin.period_to') }}">

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
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'visit_date', 'label' => strtoupper(__('coin.date')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'unique_ips', 'label' => strtoupper(__('coin.admin.visits_unique_ips')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'hits', 'label' => strtoupper(__('coin.admin.visits_hits')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($days as $day)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;white-space:nowrap;">{{ \App\Support\LocaleFormat::date($day->visit_date) }}</td>
                        <td style="padding:14px 18px;">{{ number_format((int) $day->unique_ips) }}</td>
                        <td style="padding:14px 18px;">{{ number_format((int) $day->hits) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_visits') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $days])
@endsection
