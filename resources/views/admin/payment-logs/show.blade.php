@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.payment_log_detail', ['id' => $log->id])]))

@section('content')
    <div style="margin-bottom:16px;">
        <a href="{{ route('admin.payment-logs.index') }}" style="font-size:13px;color:rgba(232,237,245,0.72);">&larr; {{ __('coin.admin.payment_logs') }}</a>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
                    <span style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.payment_log_detail', ['id' => $log->id])) }}</span>
                    <span style="font-size:11px;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,0.06);color:rgba(232,237,245,0.82);">{{ $log->kindLabel() }}</span>
                    <span style="font-size:11px;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,0.06);color:rgba(232,237,245,0.82);">{{ $log->resultLabel() }}</span>
                </div>
                <h1 style="margin:0;font-size:22px;font-weight:600;">{{ $log->title }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ $log->formattedCreatedAt() }} ({{ \App\Support\LocaleFormat::timezoneLabel() }})</p>

                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>{{ __('coin.admin.entity') }}:</strong> {{ $log->entityLabel() }}</div>
                    <div><strong>{{ __('coin.admin.reference') }}:</strong>
                        @if($log->entityAdminUrl())
                            <a href="{{ $log->entityAdminUrl() }}" style="font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $log->reference ?? '—' }}</a>
                        @else
                            {{ $log->reference ?? '—' }}
                        @endif
                    </div>
                    @if($log->user)
                    <div><strong>{{ __('coin.user') }}:</strong> <a href="{{ route('admin.users.show', $log->user) }}">{{ $log->user->email }}</a></div>
                    @endif
                    @if($log->showsEntityStatus())
                    <div><strong>{{ __('coin.payment_log.entity_status_label') }}:</strong>
                        @if($log->entityAdminUrl())
                            <a href="{{ $log->entityAdminUrl() }}">{{ $log->currentEntityStatusLabel() }}</a>
                        @else
                            {{ $log->currentEntityStatusLabel() }}
                        @endif
                    </div>
                    @elseif($log->hasStatusTransition())
                    <div><strong>{{ __('coin.payment_log.entity_status_label') }}:</strong> {{ $log->transitionLabel() }}</div>
                    @elseif(in_array($log->result, ['ignored', 'failed'], true) && in_array($log->event_type, ['payout_poll', 'deposit_ipn', 'payout_ipn'], true))
                    <div style="color:rgba(232,237,245,0.62);">{{ __('coin.payment_log.detail_no_status_change') }}</div>
                    @endif
                    <div><strong>{{ __('coin.admin.source') }}:</strong> {{ $log->sourceLabel() }}</div>
                    <div><strong>{{ __('coin.admin.event_type') }}:</strong> {{ $log->eventTypeLabel() }}</div>
                    @if($log->gateway)
                    <div><strong>{{ __('coin.admin.gateway') }}:</strong> {{ $log->gateway }}</div>
                    @endif
                    @if($log->gateway_state || $log->gateway_result)
                    <div><strong>{{ __('coin.admin.gateway_state') }}:</strong> {{ $log->gatewayStateLabel() }}</div>
                    @endif
                    @if($log->statusReasonLabel())
                    <div><strong>{{ __('coin.admin.status_reason') }}:</strong> {{ $log->statusReasonLabel() }}</div>
                    @endif
                </div>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 12px;font-size:16px;font-weight:600;">{{ __('coin.admin.message') }}</h2>
                <p style="margin:0;font-size:13px;line-height:1.7;white-space:pre-wrap;">{{ $log->displayMessage() }}</p>
                @if($log->entity_type === 'withdrawal' && $log->new_status === 'rejected' && in_array($log->event_type, ['status_change', 'payout_poll'], true))
                <p style="margin:12px 0 0;font-size:12px;color:rgba(232,237,245,0.62);">{{ __('coin.payment_log.detail_funds_restored') }}</p>
                @endif
            </div>
        </div>

        @if(is_array($log->payload) && $log->payload !== [])
        <div class="admin-card">
            <h2 style="margin:0 0 12px;font-size:16px;font-weight:600;">{{ __('coin.admin.payload') }}</h2>
            <pre style="margin:0;font-size:11px;line-height:1.5;overflow:auto;max-height:480px;color:rgba(232,237,245,0.82);">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
        @endif
    </div>
@endsection
