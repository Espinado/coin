@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.overview')]))

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.platform_overview')) }}</div>
        <h1 style="margin:12px 0 0;font-size:24px;font-weight:600;">{{ __('coin.admin.console') }}</h1>
        <p style="margin:10px 0 0;font-size:14px;line-height:1.6;color:rgba(232,237,245,0.72);">
            {!! __('coin.admin.signed_in_as', [
                'name' => '<strong>'.$admin->name.'</strong>',
                'count' => $metrics['active_contracts'],
                'locked' => number_format($metrics['total_locked'], 2),
                'symbol' => $metrics['token_symbol'],
            ]) !!}
        </p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:16px;">
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.users')) }}</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['total_users'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.users_today_kyc', ['today' => $metrics['users_today'], 'kyc' => $metrics['kyc_pending']]) }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.locked_principal')) }}</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ number_format($metrics['total_locked'], 0) }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.active_investments', ['count' => $metrics['active_contracts']]) }} · {{ $metrics['token_symbol'] }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.pending_payouts')) }}</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['pending_withdrawals_count'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ number_format($metrics['pending_withdrawals_sum'], 2) }} {{ $metrics['token_symbol'] }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.open_support')) }}</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['open_tickets'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.today_profit_accruals', ['amount' => number_format($metrics['today_profit'], 2), 'symbol' => $metrics['token_symbol']]) }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:16px;">
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.quick_links')) }}</div>
            <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}">{{ __('coin.admin.review_pending_payouts') }}</a>
                <a href="{{ route('admin.profit-accrual.index') }}">{{ __('coin.admin.run_profit') }}</a>
                <a href="{{ route('admin.deposits.index') }}">{{ __('coin.admin.review_top_ups') }}</a>
                <a href="{{ route('admin.plans.index') }}">{{ __('coin.admin.manage_plans') }}</a>
                <a href="{{ route('admin.settings.edit') }}">{{ __('coin.admin.platform_settings') }}</a>
            </div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.domains')) }}</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                {{ __('coin.admin.user_domain') }}: {{ config('coin.user_domain') }}<br>
                {{ __('coin.admin.admin_domain') }}: {{ config('coin.admin_domain') }}
            </div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.risk')) }}</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                {{ __('coin.admin.blocked_users') }}: {{ $metrics['blocked_users'] }}<br>
                {{ __('coin.admin.guard_hint') }}
            </div>
        </div>
    </div>
@endsection
