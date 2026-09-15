@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.plans')]))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.plans') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.plans_sub') }}</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="admin-btn admin-btn-primary">{{ __('coin.admin.new_plan') }}</a>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.plans.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.search_placeholder_plans'),
        ])
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'name', 'label' => strtoupper(__('coin.plan')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'min_deposit', 'label' => strtoupper(__('coin.admin.min_purchase')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'annual_profit_percent', 'label' => strtoupper(__('coin.admin.apr')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'duration_days', 'label' => strtoupper(__('coin.admin.lock_period')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'visibility', 'label' => strtoupper(__('coin.admin.visibility')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.plans.edit', $plan) }}">{{ $plan->name }}</a></td>
                        <td style="padding:14px 18px;">{{ $plan->formattedMinDeposit() ?? $plan->price_label }}</td>
                        <td style="padding:14px 18px;">{{ $plan->formattedAnnualProfit() ?? '—' }}</td>
                        <td style="padding:14px 18px;">{{ $plan->formattedDuration() }}</td>
                        <td style="padding:14px 18px;">{{ $plan->is_active ? __('coin.admin.published') : __('coin.admin.hidden') }}@if($plan->is_featured) · {{ __('coin.admin.highlighted') }} @endif</td>
                        <td style="padding:14px 18px;text-align:right;">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="admin-btn">{{ __('coin.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_plans') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $plans])
@endsection
