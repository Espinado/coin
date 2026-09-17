@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.admins.invite')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:20px;">
            <a href="{{ route('admin.admins.index') }}" class="admin-btn">{{ __('coin.back') }}</a>
        </div>
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.admins.invite') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.admins.invite_sub') }}</p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.58);">{{ __('coin.admin.admins.invite_existing_hint') }}</p>
    </div>

    <div class="admin-card" style="margin-top:16px;max-width:520px;">
        <form method="POST" action="{{ route('admin.admins.invite.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf

            <div>
                <label for="email" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.email')) }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                @error('email')
                    <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="name" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.auth.name')) }} ({{ __('coin.admin.admins.optional') }})</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-size:14px;">
                @error('name')
                    <div style="margin-top:8px;font-size:12.5px;color:#ff8f8f;">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.admins.send_invite') }}</button>
            </div>
        </form>
    </div>
@endsection
