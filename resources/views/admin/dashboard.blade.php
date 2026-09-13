@extends('layouts.admin')

@section('title', 'Coin Admin — Dashboard')

@section('content')
    <div class="admin-card">
        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;color:rgba(232,237,245,0.62);">OVERVIEW</div>
        <h1 style="margin:12px 0 0;font-size:24px;font-weight:600;">Admin console</h1>
        <p style="margin:10px 0 0;font-size:14px;line-height:1.6;color:rgba(232,237,245,0.72);">
            Signed in as <strong>{{ $admin->name }}</strong> ({{ $admin->email }}).
            User dashboard and admin panel are fully separated by domain and authentication guard.
        </p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:16px;">
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">USER APP</div>
            <div style="margin-top:10px;font-size:14px;">{{ config('coin.user_domain') }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">ADMIN APP</div>
            <div style="margin-top:10px;font-size:14px;">{{ config('coin.admin_domain') }}</div>
        </div>
        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">GUARD</div>
            <div style="margin-top:10px;font-size:14px;">admin / admins table</div>
        </div>
    </div>
@endsection
