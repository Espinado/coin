@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.top_ups')]))

@section('content')
    @include('admin.partials.finance-tabs', ['active' => 'deposits'])

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h2 style="margin:0;font-size:18px;font-weight:600;">{{ __('coin.admin.top_ups') }}</h2>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.top_ups_sub') }}</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.deposits.index'),
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'showStatus' => true,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.search_placeholder_deposits'),
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'id', 'label' => strtoupper(__('coin.admin.id')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'amount', 'label' => strtoupper(__('coin.amount')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'status', 'label' => strtoupper(__('coin.status')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.requested')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.deposits.show', $deposit) }}">#{{ $deposit->id }}</a></td>
                        <td style="padding:14px 18px;">{{ $deposit->user?->email }}</td>
                        <td style="padding:14px 18px;">{{ $deposit->formattedAmount() }}</td>
                        <td style="padding:14px 18px;">{{ $statuses[$deposit->status] ?? ucfirst($deposit->status) }}</td>
                        <td style="padding:14px 18px;">{{ $deposit->created_at?->format('M j, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_top_ups') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $deposits])
@endsection
