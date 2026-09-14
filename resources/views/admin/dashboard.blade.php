@extends('layouts.admin')

@section('title', 'Coin Admin — Overview')

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;color:rgba(232,237,245,0.62);">PLATFORM OVERVIEW</div>
        <h1 style="margin:12px 0 0;font-size:24px;font-weight:600;">Admin console</h1>
        <p style="margin:10px 0 0;font-size:14px;line-height:1.6;color:rgba(232,237,245,0.72);">
            Signed in as <strong>{{ $admin->name }}</strong>. {{ $metrics['active_contracts'] }} active investments · {{ number_format($metrics['total_locked'], 2) }} {{ $metrics['token_symbol'] }} locked.
        </p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:16px;">
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">USERS</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['total_users'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">+{{ $metrics['users_today'] }} today · {{ $metrics['kyc_pending'] }} KYC pending</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">LOCKED PRINCIPAL</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ number_format($metrics['total_locked'], 0) }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ $metrics['active_contracts'] }} active investments · {{ $metrics['token_symbol'] }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">PENDING PAYOUTS</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['pending_withdrawals_count'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">{{ number_format($metrics['pending_withdrawals_sum'], 2) }} {{ $metrics['token_symbol'] }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">OPEN SUPPORT</div>
            <div style="margin-top:10px;font-size:28px;font-weight:600;">{{ $metrics['open_tickets'] }}</div>
            <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);">Today's profit accruals: {{ number_format($metrics['today_profit'], 2) }} {{ $metrics['token_symbol'] }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:16px;">
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">QUICK LINKS</div>
            <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}">Review pending payouts</a>
                <a href="{{ route('admin.profit-accrual.index') }}">Run profit accrual</a>
                <a href="{{ route('admin.deposits.index') }}">Review top-ups</a>
                <a href="{{ route('admin.plans.index') }}">Manage plans</a>
                <a href="{{ route('admin.settings.edit') }}">Platform settings</a>
            </div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">DOMAINS</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                User: {{ config('coin.user_domain') }}<br>
                Admin: {{ config('coin.admin_domain') }}
            </div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">RISK</div>
            <div style="margin-top:14px;font-size:13px;line-height:1.8;">
                Blocked users: {{ $metrics['blocked_users'] }}<br>
                Guard: admin / admins table
            </div>
        </div>
    </div>
@endsection
