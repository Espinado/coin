@extends('layouts.admin')

@section('title', \App\Support\PlatformBrand::adminPageTitle($ticket->reference))

@push('head')
    <script>
        window.supportChatConfig = {
            ticketId: {{ $ticket->id }},
            replyUrl: @json(route('admin.support.reply', $ticket)),
            canSaveToKyc: @json(! $ticket->isGuest() && $ticket->user),
            saveToKycLabel: @json(__('coin.admin.support_save_to_kyc')),
            savedToKycLabel: @json(__('coin.admin.support_saved_to_kyc')),
            csrfToken: @json(csrf_token()),
        };
    </script>
@endpush

@push('scripts')
    @vite(['resources/js/admin-support.js'])
@endpush

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.55)' : 'rgba(255,180,84,0.35)' }};">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,143,143,0.55);">
            <ul style="margin:0;padding-left:18px;font-size:13px;line-height:1.5;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-grid-split">
        <div>
            <div class="admin-card">
                <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                    <div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ $ticket->reference }}</div>
                        <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $ticket->subject }}</h1>
                        <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ $ticket->categoryLabel() }} · <span id="ticket-status-label">{{ $ticket->statusLabel() }}</span></p>
                    </div>
                    <form method="POST" action="{{ route('admin.support.status', $ticket) }}" style="display:flex;gap:8px;align-items:center;">
                        @csrf
                        @method('PATCH')
                        <select name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="admin-btn">Update</button>
                    </form>
                </div>
            </div>

            <div id="support-thread" class="admin-card" style="margin-top:16px;display:flex;flex-direction:column;gap:14px;max-height:420px;overflow-y:auto;padding-right:4px;">
                @foreach($ticket->messages as $message)
                    <div data-message-id="{{ $message->id }}" style="padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:{{ $message->isFromAdmin() ? 'rgba(255,180,84,0.06)' : 'rgba(255,255,255,0.03)' }};">
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(232,237,245,0.62);">
                            <span>{{ $message->isFromAdmin() ? 'Support team' : $ticket->contactLabel() }}</span>
                            <span>{{ $message->created_at?->format('M j, Y H:i') }}</span>
                        </div>
                        @if(filled($message->body) && $message->body !== __('coin.support.attachment_message_body'))
                            <div style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $message->body }}</div>
                        @elseif($message->attachments->isEmpty())
                            <div style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $message->body }}</div>
                        @endif
                        @if($message->attachments->isNotEmpty())
                            <div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                                @foreach($message->attachments as $attachment)
                                    <div data-attachment-id="{{ $attachment->id }}" style="border:1px solid rgba(255,255,255,0.10);border-radius:12px;overflow:hidden;background:rgba(255,255,255,0.02);">
                                        <a href="{{ route('admin.support.attachments.show', $attachment) }}" target="_blank" rel="noopener" style="display:block;aspect-ratio:1;background:#05070c;">
                                            <img src="{{ route('admin.support.attachments.show', $attachment) }}" alt="{{ $attachment->original_name }}" style="width:100%;height:100%;object-fit:cover;display:block;">
                                        </a>
                                        <div style="padding:8px 10px;display:flex;flex-direction:column;gap:8px;">
                                            <span style="font-size:11px;color:rgba(232,237,245,0.62);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $attachment->original_name }}">{{ $attachment->original_name ?: 'JPG' }}</span>
                                            @if(! $ticket->isGuest() && $ticket->user)
                                                @if($attachment->isSavedToKyc())
                                                    <a href="{{ route('admin.users.show', $ticket->user) }}" class="admin-btn" style="padding:6px 8px;font-size:11px;text-align:center;">{{ __('coin.admin.support_saved_to_kyc') }}</a>
                                                @else
                                                    <form method="POST" action="{{ route('admin.support.attachments.save-kyc', $attachment) }}">
                                                        @csrf
                                                        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;padding:6px 8px;font-size:11px;">{{ __('coin.admin.support_save_to_kyc') }}</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
                <div class="admin-card" style="margin-top:16px;">
                    <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">Reply</h2>
                    <form id="support-reply-form" method="POST" action="{{ route('admin.support.reply', $ticket) }}" data-no-page-spinner style="display:flex;flex-direction:column;gap:12px;">
                        @csrf
                        <textarea id="support-reply-body" name="body" rows="5" required maxlength="5000" placeholder="Write a reply to the user..."
                            style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;">{{ old('body') }}</textarea>
                        <div id="support-reply-error" style="font-size:12px;color:#ff8f8f;"></div>
                        @error('body')<div style="font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                            <select id="support-reply-status" name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                                <option value="{{ \App\Models\SupportTicket::STATUS_PENDING }}">Set pending after reply</option>
                                <option value="{{ \App\Models\SupportTicket::STATUS_OPEN }}">Keep open</option>
                                <option value="{{ \App\Models\SupportTicket::STATUS_CLOSED }}">Close ticket</option>
                            </select>
                            <button type="submit" class="admin-btn admin-btn-primary">Send reply</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <div class="admin-card">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ $ticket->isGuest() ? 'GUEST CONTEXT' : 'USER CONTEXT' }}</div>
            <div style="margin-top:12px;font-size:15px;font-weight:600;">
                @if(! $ticket->isGuest() && $ticket->user)
                    <a href="{{ route('admin.users.show', $ticket->user) }}" style="color:inherit;text-decoration:underline;text-underline-offset:3px;">{{ $ticket->contactLabel() }}</a>
                @else
                    {{ $ticket->contactLabel() }}
                @endif
            </div>
            <div style="margin-top:6px;font-size:13px;color:rgba(232,237,245,0.72);">{{ $ticket->contactEmail() }}</div>
            @if($ticket->isGuest())
                <div style="margin-top:18px;padding:12px 14px;border-radius:10px;background:rgba(255,180,84,0.08);font-size:13px;line-height:1.55;color:rgba(232,237,245,0.78);">
                    Guest live chat from the public site. No dashboard account linked yet.
                </div>
            @else
                <div style="margin-top:18px;display:flex;flex-direction:column;gap:10px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.kyc') }}</span><span style="display:inline-flex;padding:2px 8px;border-radius:999px;font-size:11px;{{ $ticket->user->kycBadgeStyle() }}">{{ $ticket->user->kycLabel() }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">Balance</span><span style="font-family:'JetBrains Mono',monospace;">{{ $ticket->user->wallet?->formattedBalance() ?? '—' }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.available') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $ticket->user->wallet?->formattedAvailable() ?? '—' }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">Active TFLOPS</span><span style="font-family:'JetBrains Mono',monospace;">{{ number_format($ticket->user->active_tflops) }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">Contracts</span><span style="font-family:'JetBrains Mono',monospace;">{{ $ticket->user->contracts->count() }}</span></div>
                </div>
                @if($ticket->user->contracts->isNotEmpty())
                    <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;font-size:12.5px;">
                        @foreach($ticket->user->contracts as $contract)
                            <div style="padding:10px 12px;border-radius:10px;background:rgba(255,255,255,0.03);">
                                {{ $contract->plan?->name }} · {{ $contract->formattedTflops() }} TF · {{ $contract->status }}
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
            @if($ticket->assignedAdmin)
                <div style="margin-top:18px;font-size:12.5px;color:rgba(232,237,245,0.72);">Assigned: {{ $ticket->assignedAdmin->name }}</div>
            @endif
        </div>

        @if(($canManagePlans ?? false) && ! $ticket->isGuest() && $ticket->user)
            @include('admin.partials.private-offer-composer', [
                'user' => $ticket->user,
                'ticketId' => $ticket->id,
                'pendingOffers' => $pendingPrivateOffers ?? collect(),
                'defaultExpiresAt' => $privateOfferDefaultExpiresAt ?? null,
            ])
        @endif
    </div>
@endsection
