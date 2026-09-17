@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.overview')]))

@section('content')
    <div class="admin-card">
        <div class="admin-kicker">{{ strtoupper(__('coin.admin.platform_overview')) }}</div>
        <h1 class="admin-title">{{ __('coin.admin.console') }}</h1>
        <p class="admin-subtitle">
            {!! __('coin.admin.signed_in_as', [
                'name' => '<strong>'.$admin->name.'</strong>',
                'count' => $metrics['active_contracts'],
                'locked' => \App\Support\MoneyFormat::amount($metrics['total_locked'], $metrics['token_symbol']),
            ]) !!}
        </p>
    </div>

    <div class="admin-grid-4">
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.users')) }}</div>
            <div class="admin-stat-value">{{ $metrics['total_users'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.users_today_kyc', ['today' => $metrics['users_today'], 'kyc' => $metrics['kyc_pending']]) }}</div>
        </div>
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.locked_principal')) }}</div>
            <div class="admin-stat-value">{{ \App\Support\MoneyFormat::amount($metrics['total_locked'], $metrics['token_symbol'], 0) }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.active_investments', ['count' => $metrics['active_contracts']]) }}</div>
        </div>
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.pending_payouts')) }}</div>
            <div class="admin-stat-value">{{ $metrics['pending_withdrawals_count'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ \App\Support\MoneyFormat::amount($metrics['pending_withdrawals_sum'], $metrics['token_symbol']) }}</div>
        </div>
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.open_support')) }}</div>
            <div class="admin-stat-value">{{ $metrics['open_tickets'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.today_profit_accruals', ['amount' => \App\Support\MoneyFormat::amount($metrics['today_profit'], $metrics['token_symbol'])]) }}</div>
        </div>
    </div>

    <div class="admin-grid-3">
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.quick_links')) }}</div>
            <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}">{{ __('coin.admin.review_pending_payouts') }}</a>
                <a href="{{ route('admin.profit-accrual.index') }}">{{ __('coin.admin.view_profit_accruals') }}</a>
                <a href="{{ route('admin.deposits.index') }}">{{ __('coin.admin.review_top_ups') }}</a>
                <a href="{{ route('admin.plans.index') }}">{{ __('coin.admin.manage_plans') }}</a>
                <a href="{{ route('admin.settings.edit') }}">{{ __('coin.admin.platform_settings') }}</a>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.domains')) }}</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                {{ __('coin.admin.user_domain') }}: {{ config('coin.user_domain') }}<br>
                {{ __('coin.admin.admin_domain') }}: {{ config('coin.admin_domain') }}
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-kicker">{{ strtoupper(__('coin.admin.risk')) }}</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                {{ __('coin.admin.blocked_users') }}: {{ $metrics['blocked_users'] }}<br>
                {{ __('coin.admin.guard_hint') }}
            </div>
        </div>
    </div>
@endsection
