@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.broadcasts.new')]))

@section('content')
    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.broadcasts.new') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.broadcasts.compose_hint') }}</p>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ route('admin.broadcasts.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.broadcasts.title_field')) }}</label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="160" required
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                @error('title')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.broadcasts.body_field')) }}</label>
                <textarea name="body" rows="10" required maxlength="10000"
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;">{{ old('body') }}</textarea>
                @error('body')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.broadcasts.send') }}</button>
                <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn">{{ __('coin.admin.broadcasts.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
