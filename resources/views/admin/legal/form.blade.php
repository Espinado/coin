@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => $page->slugLabel()]))

@section('content')
    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $page->slugLabel() }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ $page->isFaq() ? __('coin.admin.legal.faq_edit_hint') : __('coin.admin.legal.edit_hint') }}</p>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ route('admin.legal.update', $page) }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PATCH')
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.title_field')) }}</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" maxlength="160" required
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                @error('title')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            @if($page->isFaq())
                @include('admin.legal.faq-items-fields')
                @error('faq_items')<div style="font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            @else
                <div>
                    <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.body_field')) }}</label>
                    <textarea name="body" rows="18" maxlength="50000"
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;font-family:inherit;line-height:1.55;">{{ old('body', $page->body) }}</textarea>
                    @error('body')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                </div>
            @endif
            <label style="display:flex;align-items:center;gap:10px;font-size:14px;color:rgba(232,237,245,0.86);">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
                {{ __('coin.admin.legal.publish') }}
            </label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.save') }}</button>
                <a href="{{ route('admin.legal.index') }}" class="admin-btn">{{ __('coin.cancel') }}</a>
                @if($page->is_published)
                    <a href="{{ route('legal.show', $page) }}" class="admin-btn" target="_blank" rel="noopener">{{ __('coin.admin.legal.preview') }}</a>
                @endif
            </div>
        </form>
    </div>
@endsection
