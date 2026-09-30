@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.system_logs')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.system_logs') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.system_logs_sub') }}</p>
        </div>

        <form method="GET" action="{{ route('admin.system-logs.index') }}" class="admin-list-toolbar">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('coin.admin.search_placeholder_system_logs') }}">

            <select name="level">
                <option value="">{{ __('coin.admin.all_levels') }}</option>
                @foreach($levels as $value => $label)
                    <option value="{{ $value }}" @selected($level === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="source">
                <option value="">{{ __('coin.admin.all_sources') }}</option>
                @foreach($sources as $value => $label)
                    <option value="{{ $value }}" @selected($source === $value)>{{ $label }}</option>
                @endforeach
            </select>

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

    <div class="admin-card admin-card--table" style="margin-top:16px;">
        <div class="admin-table-scroll">
            <table>
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                        @include('admin.partials.sortable-th', ['column' => 'occurred_at', 'label' => strtoupper(__('coin.admin.time')), 'sort' => $sort, 'dir' => $dir])
                        @include('admin.partials.sortable-th', ['column' => 'level', 'label' => strtoupper(__('coin.admin.level')), 'sort' => $sort, 'dir' => $dir])
                        @include('admin.partials.sortable-th', ['column' => 'source', 'label' => strtoupper(__('coin.admin.source')), 'sort' => $sort, 'dir' => $dir])
                        @include('admin.partials.sortable-th', ['column' => 'channel', 'label' => strtoupper(__('coin.admin.channel')), 'sort' => $sort, 'dir' => $dir])
                        <th style="padding:12px 18px;">{{ strtoupper(__('coin.admin.message')) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                            <td style="padding:14px 18px;white-space:nowrap;">
                                <a href="{{ route('admin.system-logs.show', $log) }}">{{ $log->formattedOccurredAt() }}</a>
                            </td>
                            <td style="padding:14px 18px;">
                                <span style="font-size:11px;padding:4px 8px;border-radius:999px;background:{{ $log->level === 'critical' ? 'rgba(255,100,100,0.18)' : ($log->level === 'error' ? 'rgba(255,143,143,0.15)' : ($log->level === 'warning' ? 'rgba(255,180,84,0.15)' : 'rgba(255,255,255,0.06)')) }};color:{{ $log->level === 'critical' || $log->level === 'error' ? '#ff8f8f' : ($log->level === 'warning' ? '#ffd39a' : 'rgba(232,237,245,0.82)') }};">{{ $log->levelLabel() }}</span>
                            </td>
                            <td style="padding:14px 18px;">{{ $log->sourceLabel() }}</td>
                            <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $log->channel ?? '—' }}</td>
                            <td style="padding:14px 18px;max-width:420px;">
                                <a href="{{ route('admin.system-logs.show', $log) }}" style="color:inherit;text-decoration:none;">{{ \Illuminate\Support\Str::limit($log->message, 140) }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_system_logs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.partials.list-pagination', ['paginator' => $logs])
    </div>
@endsection
