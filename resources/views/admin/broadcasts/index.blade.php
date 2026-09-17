@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.broadcasts.title')]))

@section('content')
    <div class="admin-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.broadcasts.title') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.broadcasts.subtitle') }}</p>
            </div>
            <a href="{{ route('admin.broadcasts.create') }}" class="admin-btn admin-btn-primary">{{ __('coin.admin.broadcasts.new') }}</a>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.broadcasts.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.broadcasts.search_placeholder'),
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'title', 'label' => strtoupper(__('coin.admin.broadcasts.title_field')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'recipients', 'label' => strtoupper(__('coin.admin.broadcasts.recipients')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'sent', 'label' => strtoupper(__('coin.admin.broadcasts.sent_at')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($broadcasts as $broadcast)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.broadcasts.show', $broadcast) }}">{{ $broadcast->title }}</a></td>
                        <td style="padding:14px 18px;">{{ number_format($broadcast->recipients_count) }}</td>
                        <td style="padding:14px 18px;">{{ $broadcast->created_at?->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i') }}</td>
                        <td style="padding:14px 18px;text-align:right;">
                            <a href="{{ route('admin.broadcasts.show', $broadcast) }}" class="admin-btn">{{ __('coin.admin.broadcasts.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.broadcasts.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $broadcasts])
@endsection
