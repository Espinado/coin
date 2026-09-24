@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.payout_detail', ['reference' => $withdrawal->reference])]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.35)' : 'rgba(120,230,180,0.35)' }};">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ $withdrawal->reference }}</div>
                <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $withdrawal->formattedAmount() }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ $withdrawal->statusLabel() }} · {{ $withdrawal->created_at?->format('M j, Y H:i') }}</p>
                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>{{ __('coin.admin.payout_address') }}:</strong> {{ $withdrawal->payout_address }}</div>
                    @if($withdrawal->network_label)<div><strong>{{ __('coin.admin.network') }}:</strong> {{ $withdrawal->network_label }}</div>@endif
                    @if($withdrawal->gateway_request_id)<div><strong>{{ __('coin.admin.gateway_reference') }}:</strong> {{ $withdrawal->gateway_request_id }}</div>@endif
                    @if($withdrawal->gateway_state)<div><strong>{{ __('coin.admin.gateway_state') }}:</strong> {{ $withdrawal->gateway_state }}</div>@endif
                    @if($withdrawal->status === \App\Models\Withdrawal::STATUS_PROCESSING)
                        @if($withdrawal->gateway_poll_checked_at)
                            <div><strong>{{ __('coin.admin.gateway_poll_checked_at') }}:</strong> {{ $withdrawal->gateway_poll_checked_at->format('M j, Y H:i:s') }}</div>
                        @endif
                        @if($withdrawal->gateway_poll_summary)
                            <div><strong>{{ __('coin.admin.gateway_poll_summary') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $withdrawal->gateway_poll_summary }}</span></div>
                        @endif
                    @endif
                    @if($withdrawal->txid)<div><strong>{{ __('coin.admin.txid') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;font-size:12px;word-break:break-all;">{{ $withdrawal->txid }}</span></div>@endif
                    @if($withdrawal->status === \App\Models\Withdrawal::STATUS_REJECTED && $withdrawal->adminRejectionMessage())
                        <div style="margin-top:10px;"><strong>{{ __('coin.admin.status_reason') }}:</strong> {{ $withdrawal->adminRejectionMessage() }}</div>
                        <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);"><strong>{{ __('coin.admin.user_facing_reason') }}:</strong> {{ $withdrawal->userRejectionMessage() }}</div>
                    @endif
                    @if($withdrawal->shouldShowAdminNote())<div style="margin-top:10px;"><strong>{{ __('coin.admin.admin_note') }}:</strong> {{ $withdrawal->admin_note }}</div>@endif
                </div>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.update_status') }}</h2>
                @if($withdrawal->isClosed())
                    <p style="margin:0;font-size:13px;line-height:1.65;color:rgba(232,237,245,0.72);">{{ __('coin.admin.withdrawal_closed_hint') }}</p>
                    @if($withdrawal->processed_at)
                        <p style="margin:10px 0 0;font-size:12.5px;color:rgba(232,237,245,0.58);">{{ __('coin.admin.withdrawal_closed_at', ['date' => $withdrawal->processed_at->format('M j, Y H:i')]) }}</p>
                    @endif
                @elseif($withdrawal->status === \App\Models\Withdrawal::STATUS_PROCESSING)
                    <p style="margin:0;font-size:13px;line-height:1.65;color:rgba(232,237,245,0.72);">{{ __('coin.admin.withdrawal_processing_hint') }}</p>
                    @if($pollLogs->isNotEmpty())
                        <div style="margin-top:18px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.08);">
                            <h3 style="margin:0 0 10px;font-size:14px;font-weight:600;">{{ __('coin.admin.gateway_poll_log') }}</h3>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                @foreach($pollLogs as $log)
                                    <div style="font-size:12px;line-height:1.55;color:rgba(232,237,245,0.72);padding:10px 12px;border-radius:8px;background:rgba(255,255,255,0.03);">
                                        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:rgba(232,237,245,0.52);">{{ $log->processed_at?->format('M j, Y H:i:s') }}</div>
                                        <div style="margin-top:4px;">{{ $log->processing_result }}</div>
                                        @if(is_array($log->payload) && $log->payload !== [])
                                            <details style="margin-top:6px;">
                                                <summary style="cursor:pointer;color:rgba(232,237,245,0.58);">{{ __('coin.admin.gateway_poll_response') }}</summary>
                                                <pre style="margin:8px 0 0;padding:10px;border-radius:8px;background:#070a10;overflow:auto;font-size:11px;line-height:1.45;">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
                        @csrf
                        <p style="margin:0;font-size:13px;line-height:1.65;color:rgba(232,237,245,0.72);">{{ __('coin.admin.withdrawal_approve_hint') }}</p>
                        <textarea name="admin_note" rows="3" placeholder="{{ __('coin.admin.audit_note_placeholder') }}"
                            style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">{{ old('admin_note', $withdrawal->admin_note) }}</textarea>
                        <button type="submit" class="admin-btn admin-btn-primary" style="align-self:flex-start;">{{ __('coin.admin.approve_payout') }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.withdrawals.status', $withdrawal) }}" style="display:flex;flex-direction:column;gap:12px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.08);">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ \App\Models\Withdrawal::STATUS_REJECTED }}">
                        <p style="margin:0;font-size:13px;line-height:1.65;color:rgba(232,237,245,0.72);">{{ __('coin.admin.withdrawal_reject_hint') }}</p>
                        <textarea name="admin_note" rows="3" placeholder="{{ __('coin.admin.audit_note_placeholder') }}"
                            style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">{{ old('admin_note') }}</textarea>
                        <button type="submit" class="admin-btn" style="align-self:flex-start;border-color:rgba(255,143,143,0.35);color:#ffb4b4;">{{ __('coin.admin.reject_payout') }}</button>
                    </form>
                @endif
            </div>
        </div>

        @include('admin.partials.user-context', ['user' => $withdrawal->user])
    </div>
@endsection
