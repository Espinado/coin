@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.profit_accrual')]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.profit_accrual') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">
                {!! __('coin.admin.profit_accrual_sub', [
                    'time' => config('coin.profit_accrual.schedule_time'),
                    'timezone' => config('coin.profit_accrual.schedule_timezone'),
                    'command' => '<code style="font-family:\'JetBrains Mono\',monospace;">coin:accrue-daily-profits</code>',
                ]) !!}
            </p>
            <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.58);">{{ __('coin.admin.profit_accrual_auto_only') }}</p>
            <p style="margin:8px 0 0;font-size:12.5px;color:rgba(232,237,245,0.52);font-family:'JetBrains Mono',monospace;">{{ __('coin.admin.profit_accrual_log_hint') }}</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.profit-accrual.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.search_placeholder_accruals'),
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'occurred_at', 'label' => strtoupper(__('coin.admin.when')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'source', 'label' => strtoupper(__('coin.admin.source')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'amount', 'label' => strtoupper(__('coin.amount')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($accruals as $tx)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;">{{ $tx->occurred_at?->format('M j, Y H:i') ?? $tx->occurred_label }}</td>
                        <td style="padding:14px 18px;"><a href="{{ route('admin.users.show', $tx->user) }}">{{ $tx->user?->accountLabel() }}</a></td>
                        <td style="padding:14px 18px;">{{ $tx->source }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $tx->amount_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_accruals') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $accruals])
@endsection
