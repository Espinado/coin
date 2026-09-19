@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.auth.two_factor_title')]))

@section('topbar')
    @include('partials.admin-auth-topbar')
@endsection

@section('content')
    <div class="admin-auth-wrap">
        <div class="admin-card">
            <h1 style="margin:0;font-size:22px;font-weight:600;">{{ __('coin.auth.two_factor_title') }}</h1>
            <p style="margin:10px 0 0;font-size:13.5px;line-height:1.55;color:rgba(232,237,245,0.72);">
                {{ __('coin.auth.two_factor_sub', ['email' => $email]) }}
            </p>

            @if (session('status') && ! $errors->any())
                <div style="margin-top:16px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,180,84,0.35);background:rgba(255,180,84,0.08);font-size:13px;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.two-factor.store') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:16px;">
                @csrf

                <div>
                    <label for="code" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.auth.two_factor_code')) }}</label>
                    <input id="code" type="text" name="code" value="{{ old('code') }}" required autofocus autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-family:'JetBrains Mono',monospace;font-size:24px;letter-spacing:0.22em;text-align:center;">
                    @error('code')
                        <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;padding:12px;">{{ __('coin.auth.two_factor_confirm') }}</button>
            </form>

            <form method="POST" action="{{ route('admin.login.two-factor.resend') }}" style="margin-top:16px;text-align:center;">
                @csrf
                <button type="submit" style="border:0;background:transparent;color:rgba(232,237,245,0.78);font-family:inherit;font-size:13px;cursor:pointer;text-decoration:underline;">{{ __('coin.auth.two_factor_resend') }}</button>
            </form>

            <p style="margin:20px 0 0;font-size:13px;color:rgba(232,237,245,0.62);text-align:center;">
                <a href="{{ route('admin.login', ['cancel' => 1]) }}">{{ __('coin.auth.two_factor_back') }}</a>
            </p>
        </div>
    </div>

    @include('partials.auth-two-factor-back-handler', ['cancelUrl' => route('admin.login', ['cancel' => 1])])
@endsection
