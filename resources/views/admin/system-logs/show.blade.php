@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.system_log_detail', ['id' => $log->id])]))

@section('content')
    <div style="margin-bottom:16px;">
        <a href="{{ route('admin.system-logs.index') }}" style="font-size:13px;color:rgba(232,237,245,0.72);">&larr; {{ __('coin.admin.system_logs') }}</a>
    </div>

    <div class="admin-card">
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
            <span style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.system_log_detail', ['id' => $log->id])) }}</span>
            <span style="font-size:11px;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,0.06);">{{ $log->levelLabel() }}</span>
            <span style="font-size:11px;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,0.06);">{{ $log->sourceLabel() }}</span>
        </div>
        <h1 style="margin:0;font-size:22px;font-weight:600;line-height:1.35;">{{ $log->message }}</h1>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ $log->formattedOccurredAt() }}</p>

        <div style="margin-top:18px;display:flex;flex-direction:column;gap:10px;font-size:13px;line-height:1.6;">
            <div><strong>{{ __('coin.admin.channel') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;">{{ $log->channel ?? '—' }}</span></div>
            <div><strong>{{ __('coin.admin.exception') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;">{{ $log->exception_class ?? '—' }}</span></div>
            <div><strong>{{ __('coin.admin.location') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $log->shortLocation() }}</span></div>
        </div>
    </div>

    @if(! empty($log->context))
        <div class="admin-card" style="margin-top:16px;">
            <h2 style="margin:0 0 12px;font-size:16px;font-weight:600;">{{ __('coin.admin.context') }}</h2>
            <pre style="margin:0;padding:14px;border-radius:10px;background:rgba(0,0,0,0.28);overflow:auto;font-family:'JetBrains Mono',monospace;font-size:12px;line-height:1.55;white-space:pre-wrap;">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    @endif
@endsection
