@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.admins.accept_title')]))

@section('topbar')
    @include('partials.admin-auth-topbar')
@endsection

@section('content')
    <div class="admin-auth-wrap">
        <div class="admin-card">
            <h1 style="margin:0;font-size:22px;font-weight:600;">
                {{ $isPasswordReset ? __('coin.admin.admins.reset_title') : __('coin.admin.admins.accept_title') }}
            </h1>
            <p style="margin:10px 0 0;font-size:13.5px;line-height:1.55;color:rgba(232,237,245,0.72);">
                {{ $isPasswordReset
                    ? __('coin.admin.admins.reset_sub', ['email' => $invitation->email])
                    : __('coin.admin.admins.accept_sub', ['email' => $invitation->email]) }}
            </p>

            @if (session('status'))
                <div style="margin-top:16px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,180,84,0.35);background:rgba(255,180,84,0.08);font-size:13px;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.invite.store', ['token' => $token]) }}" style="margin-top:24px;display:flex;flex-direction:column;gap:16px;">
                @csrf

                <div>
                    <label for="name" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.auth.name')) }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $invitation->name) }}" required autofocus autocomplete="name"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                    @error('name')
                        <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.auth.password')) }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                    @error('password')
                        <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.auth.password_confirm')) }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                </div>

                <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;padding:12px;">
                    {{ $isPasswordReset ? __('coin.admin.admins.reset_submit') : __('coin.admin.admins.accept_submit') }}
                </button>
            </form>

            <p style="margin:20px 0 0;font-size:13px;color:rgba(232,237,245,0.62);">
                <a href="{{ route('admin.login') }}">{{ __('coin.auth.login') }}</a>
            </p>
        </div>
    </div>
@endsection
