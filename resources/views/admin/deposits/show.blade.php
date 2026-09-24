@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.top_up_detail', ['id' => $deposit->id])]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.top_up_detail', ['id' => $deposit->id])) }}</div>
                <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $deposit->formattedAmount() }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ ucfirst($deposit->status) }} · {{ \App\Support\LocaleFormat::dateTimeLocal($deposit->created_at) }} ({{ \App\Support\LocaleFormat::timezoneLabel() }})</p>
                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>{{ __('coin.admin.method') }}:</strong> {{ $deposit->method }}</div>
                    @if($deposit->payment_address)
                    <div><strong>{{ __('coin.admin.payment_address') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;font-size:12px;word-break:break-all;">{{ $deposit->payment_address }}</span></div>
                    @endif
                    @if($deposit->gateway_uniq_id)
                    <div><strong>{{ __('coin.admin.gateway_reference') }}:</strong> {{ $deposit->gateway_uniq_id }}</div>
                    @endif
                    @if($deposit->txid)
                    <div><strong>{{ __('coin.admin.txid') }}:</strong> <span style="font-family:'JetBrains Mono',monospace;font-size:12px;word-break:break-all;">{{ $deposit->txid }}</span></div>
                    @endif
                    @if($deposit->status === \App\Models\Deposit::STATUS_CONFIRMED && $deposit->formattedCreditedAmount())
                    <div><strong>{{ __('coin.admin.credited_amount') }}:</strong> {{ $deposit->formattedCreditedAmount() }}</div>
                    @elseif($deposit->status === \App\Models\Deposit::STATUS_REJECTED && $deposit->received_amount !== null)
                    <div><strong>{{ __('coin.admin.received_amount') }}:</strong> {{ $deposit->formattedChainReceivedAmount() }}</div>
                    @endif
                    @if($deposit->exchange_rate)
                    <div><strong>{{ __('coin.admin.exchange_rate') }}:</strong> {{ rtrim(rtrim(number_format((float) $deposit->exchange_rate, 8, '.', ''), '0'), '.') }} BTC</div>
                    @endif
                    @if($deposit->status === \App\Models\Deposit::STATUS_PENDING && $deposit->expires_at)
                    <div>
                        <strong>{{ __('coin.admin.expires_at') }}:</strong>
                        {{ \App\Support\LocaleFormat::dateTimeLocal($deposit->expires_at) }} ({{ \App\Support\LocaleFormat::timezoneLabel() }})
                        @if($deposit->expires_at->isPast())
                        <span style="margin-left:8px;color:#ffb0b0;">{{ __('coin.admin.deposit_expired_pending') }}</span>
                        @else
                        <span style="margin-left:8px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.deposit_expires_in', ['minutes' => max(1, (int) now()->diffInMinutes($deposit->expires_at, false))]) }}</span>
                        @endif
                    </div>
                    @endif
                    @if($deposit->confirmed_at)<div><strong>{{ __('coin.admin.processed') }}:</strong> {{ $deposit->confirmed_at->format('M j, Y H:i') }}</div>@endif
                    @if($deposit->status === \App\Models\Deposit::STATUS_REJECTED && $deposit->adminRejectionMessage())
                        <div style="margin-top:10px;"><strong>{{ __('coin.admin.status_reason') }}:</strong> {{ $deposit->adminRejectionMessage() }}</div>
                        <div style="margin-top:6px;font-size:12px;color:rgba(232,237,245,0.62);"><strong>{{ __('coin.admin.user_facing_reason') }}:</strong> {{ $deposit->userRejectionMessage() }}</div>
                    @endif
                </div>
            </div>

            @if($deposit->status === \App\Models\Deposit::STATUS_PENDING)
            <div class="admin-card" style="margin-top:16px;">
                <p style="margin:0;font-size:13px;line-height:1.6;color:rgba(232,237,245,0.72);">{{ __('coin.admin.deposit_auto_processing_hint') }}</p>
            </div>
            @endif

            <div class="admin-card" style="margin-top:16px;">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.deposit_webhook_log')) }}</div>
                @if($webhookLogs->isEmpty())
                    <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:rgba(232,237,245,0.62);">{{ __('coin.admin.deposit_webhook_log_empty') }}</p>
                @else
                    @if(! empty($webhookLogsOnlyDuplicates))
                    <p style="margin:12px 0 0;font-size:12.5px;line-height:1.55;color:rgba(232,237,245,0.62);">{{ __('coin.admin.deposit_webhook_duplicate_only_hint') }}</p>
                    @endif
                    <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px;">
                        @foreach($webhookLogs as $log)
                            <div style="font-size:12px;line-height:1.55;color:rgba(232,237,245,0.72);padding:10px 12px;border-radius:8px;background:rgba(255,255,255,0.03);">
                                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:rgba(232,237,245,0.52);">{{ $log->processed_at?->format('M j, Y H:i:s') ?? $log->created_at?->format('M j, Y H:i:s') }} · {{ $log->event_type }} · {{ $log->signature_valid ? 'sign ok' : 'sign bad' }}</div>
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
                @endif
            </div>
        </div>

        @include('admin.partials.user-context', ['user' => $deposit->user])
    </div>

    @if($deposit->status === \App\Models\Deposit::STATUS_PENDING && $deposit->expires_at)
        @push('scripts')
            <script>
                (function () {
                    const statusUrl = @json(route('admin.deposits.status', $deposit));
                    const pendingStatus = @json(\App\Models\Deposit::STATUS_PENDING);
                    const expiresAtMs = @json($deposit->expires_at->getTimestamp() * 1000);
                    let pollTimer = null;
                    let expiryTimer = null;

                    function reloadIfStatusChanged(status) {
                        if (status !== pendingStatus) {
                            window.location.reload();
                        }
                    }

                    function pollStatus() {
                        fetch(statusUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        })
                            .then(function (response) {
                                if (! response.ok) {
                                    throw new Error('status poll failed');
                                }

                                return response.json();
                            })
                            .then(function (payload) {
                                reloadIfStatusChanged(payload.status);
                            })
                            .catch(function () {});
                    }

                    function schedulePoll() {
                        if (pollTimer !== null) {
                            clearInterval(pollTimer);
                        }

                        pollTimer = window.setInterval(pollStatus, 3000);
                    }

                    function scheduleExpiryCheck() {
                        if (expiryTimer !== null) {
                            clearTimeout(expiryTimer);
                        }

                        const delay = Math.max(0, expiresAtMs - Date.now());

                        expiryTimer = window.setTimeout(function () {
                            pollStatus();
                            schedulePoll();
                        }, delay);
                    }

                    schedulePoll();
                    scheduleExpiryCheck();
                })();
            </script>
        @endpush
    @endif
@endsection
