@extends('layouts.admin')

@section('title', 'Coin Admin — Sign in')

@section('topbar')
    <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-weight:600;">Coin Admin</span>
            <span class="admin-badge">STAFF ONLY</span>
        </div>
    </header>
@endsection

@section('content')
    <div style="max-width:420px;margin:80px auto 0;">
        <div class="admin-card">
            <h1 style="margin:0;font-size:22px;font-weight:600;">Admin sign in</h1>
            <p style="margin:10px 0 0;font-size:13.5px;line-height:1.55;color:rgba(232,237,245,0.72);">
                Separate staff access for {{ config('coin.admin_domain') }}. Regular user accounts cannot sign in here.
            </p>

            @if (session('status'))
                <div style="margin-top:16px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,180,84,0.35);background:rgba(255,180,84,0.08);font-size:13px;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:16px;">
                @csrf

                <div>
                    <label for="email" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">EMAIL</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                    @error('email')
                        <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">PASSWORD</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                    @error('password')
                        <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                    @enderror
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(232,237,245,0.78);">
                    <input type="checkbox" name="remember" style="accent-color:#ffb454;">
                    Remember me
                </label>

                <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;padding:12px;">Sign in</button>
            </form>
        </div>
    </div>
@endsection
