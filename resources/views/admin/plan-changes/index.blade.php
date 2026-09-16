@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.plan_changes')]))

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.plan_changes') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.plan_changes_sub') }}</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.plan-changes.index'),
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'showStatus' => true,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.search_placeholder_plan_changes'),
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'reference', 'label' => strtoupper(__('coin.admin.reference')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.from_plan')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.to_plan')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.top_up')) }}</th>
                    @include('admin.partials.sortable-th', ['column' => 'status', 'label' => strtoupper(__('coin.status')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.requested')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $planChange)
                    <tr data-plan-change-id="{{ $planChange->id }}" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.plan-changes.show', $planChange) }}">{{ $planChange->reference }}</a></td>
                        <td style="padding:14px 18px;">{{ $planChange->user->accountLabel() }}</td>
                        <td style="padding:14px 18px;">{{ $planChange->fromPlan?->displayName() }}</td>
                        <td style="padding:14px 18px;">{{ $planChange->toPlan?->displayName() }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $planChange->formattedTopUp() }}</td>
                        <td data-plan-change-status-cell style="padding:14px 18px;">{{ $planChange->statusLabel() }}</td>
                        <td style="padding:14px 18px;">{{ $planChange->created_at?->format('M j, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_plan_changes_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $requests])
@endsection
