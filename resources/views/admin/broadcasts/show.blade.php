@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => $broadcast->title]))

@section('content')
    <div class="admin-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.broadcasts.sent_notification')) }}</div>
                <h1 style="margin:10px 0 0;font-size:24px;font-weight:600;">{{ $broadcast->title }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">
                    {{ __('coin.admin.broadcasts.sent_meta', [
                        'date' => $broadcast->created_at?->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i'),
                        'admin' => $broadcast->admin?->name ?? '—',
                        'count' => number_format($broadcast->recipients_count),
                    ]) }}
                </p>
            </div>
            <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn">{{ __('coin.admin.broadcasts.back') }}</a>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <div style="font-size:14px;line-height:1.7;white-space:pre-wrap;">{{ $broadcast->body }}</div>
    </div>
@endsection
