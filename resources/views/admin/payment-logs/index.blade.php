@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.payment_logs')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.payment_logs') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.payment_logs_sub') }}</p>
        </div>

        <form method="GET" action="{{ route('admin.payment-logs.index') }}" class="admin-list-toolbar">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('coin.admin.search_placeholder_payment_logs') }}">

            <select name="entity_type">
                <option value="">{{ __('coin.admin.all_entity_types') }}</option>
                @foreach($entityTypes as $value => $label)
                    <option value="{{ $value }}" @selected($entityType === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="source">
                <option value="">{{ __('coin.admin.all_sources') }}</option>
                @foreach($sources as $value => $label)
                    <option value="{{ $value }}" @selected($source === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="result">
                <option value="">{{ __('coin.admin.all_results') }}</option>
                @foreach($results as $value => $label)
                    <option value="{{ $value }}" @selected($result === $value)>{{ $label }}</option>
                @endforeach
            </select>

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
                    @include('admin.partials.sortable-th', ['column' => 'id', 'label' => strtoupper(__('coin.admin.id')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.time')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'entity_type', 'label' => strtoupper(__('coin.admin.entity')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:12px 18px;">{{ strtoupper(__('coin.admin.reference')) }}</th>
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'source', 'label' => strtoupper(__('coin.admin.source')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:12px 18px;">{{ strtoupper(__('coin.admin.title')) }}</th>
                    <th style="padding:12px 18px;">{{ strtoupper(__('coin.admin.message')) }}</th>
                    @include('admin.partials.sortable-th', ['column' => 'result', 'label' => strtoupper(__('coin.admin.result')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.payment-logs.show', $log) }}">#{{ $log->id }}</a></td>
                        <td style="padding:14px 18px;white-space:nowrap;">{{ $log->formattedCreatedAt() }}</td>
                        <td style="padding:14px 18px;">{{ $log->entityLabel() }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;font-size:12px;">
                            @if($log->entityAdminUrl())
                                <a href="{{ $log->entityAdminUrl() }}">{{ $log->reference ?? '—' }}</a>
                            @else
                                {{ $log->reference ?? '—' }}
                            @endif
                        </td>
                        <td style="padding:14px 18px;">{{ $log->user?->email ?? '—' }}</td>
                        <td style="padding:14px 18px;">{{ $log->sourceLabel() }}</td>
                        <td style="padding:14px 18px;">{{ $log->title }}</td>
                        <td style="padding:14px 18px;max-width:320px;">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</td>
                        <td style="padding:14px 18px;">{{ $log->resultLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_payment_logs') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $logs])
@endsection
