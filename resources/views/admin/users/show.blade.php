@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => $user->accountLabel()]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $user->name }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ $user->email }} · {{ $user->accountLabel() }}</p>
                <div style="margin-top:14px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;font-size:13px;">
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.registered') }}</span><div style="margin-top:4px;">{{ $user->created_at?->format('M j, Y H:i') ?? '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.last_login') }}</span><div style="margin-top:4px;">{{ $user->last_login_at?->format('M j, Y H:i') ?? '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.phone') }}</span><div style="margin-top:4px;">{{ $user->phone ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.telegram') }}</span><div style="margin-top:4px;">{{ $user->telegram ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.country') }}</span><div style="margin-top:4px;">{{ $user->country_code ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.referrer') }}</span><div style="margin-top:4px;">@if($user->referrer)<a href="{{ route('admin.users.show', $user->referrer) }}">{{ $user->referrer->accountLabel() }}</a>@else — @endif</div></div>
                </div>
            </div>

            @if ($voximplantReady)
                <div class="admin-card" style="margin-top:16px;">
                    <h2 style="margin:0 0 10px;font-size:16px;font-weight:600;">{{ __('coin.voximplant.title') }}</h2>
                    <p style="margin:0 0 16px;font-size:13px;line-height:1.55;color:rgba(232,237,245,0.72);">
                        {{ __('coin.voximplant.hint') }}
                    </p>

                    @if ($voximplantDestination)
                        <div
                            id="admin-vox-call"
                            data-username="{{ $voximplantUsername }}"
                            data-user-name="{{ $user->name }}"
                            data-destination="{{ $voximplantDestination }}"
                            data-caller-id="{{ $voximplantCallerId }}"
                            data-node="{{ $voximplantNode }}"
                            data-one-time-key-url="{{ route('admin.voximplant.one-time-key') }}"
                            data-status-idle="{{ __('coin.voximplant.status_idle') }}"
                            data-status-connecting="{{ __('coin.voximplant.status_connecting') }}"
                            data-status-ready="{{ __('coin.voximplant.status_ready') }}"
                            data-status-calling="{{ __('coin.voximplant.status_calling') }}"
                            data-status-connected="{{ __('coin.voximplant.status_connected') }}"
                            data-status-ended="{{ __('coin.voximplant.status_ended') }}"
                            data-status-failed="{{ __('coin.voximplant.status_failed') }}"
                            data-status-no-phone="{{ __('coin.voximplant.no_phone') }}"
                            data-status-requesting-mic="{{ __('coin.voximplant.status_requesting_mic') }}"
                            data-status-mic-denied="{{ __('coin.voximplant.status_mic_denied') }}"
                            data-status-mic-unsupported="{{ __('coin.voximplant.status_mic_unsupported') }}"
                            data-label-dialing="{{ __('coin.voximplant.modal_dialing') }}"
                            data-label-ringing="{{ __('coin.voximplant.modal_ringing') }}"
                            data-label-connected="{{ __('coin.voximplant.modal_connected') }}"
                            data-label-ended="{{ __('coin.voximplant.modal_ended') }}"
                            data-label-failed="{{ __('coin.voximplant.modal_failed') }}"
                            data-label-duration="{{ __('coin.voximplant.modal_duration') }}"
                            data-label-to-user="{{ __('coin.voximplant.modal_to_user') }}"
                        >
                            <div style="font-size:13px;margin-bottom:12px;">
                                <span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.phone') }}:</span>
                                <span style="font-family:'JetBrains Mono',monospace;">{{ $voximplantDestination }}</span>
                            </div>
                            <div data-vox-status hidden style="margin-bottom:12px;font-size:13px;color:rgba(232,237,245,0.78);"></div>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                                <button type="button" class="admin-btn admin-btn-primary" data-vox-call>{{ __('coin.voximplant.call') }}</button>
                            </div>
                            @if ($voximplantCallerId === '')
                                <p style="margin:12px 0 0;font-size:12.5px;color:rgba(255,180,84,0.9);">{{ __('coin.voximplant.caller_id_missing') }}</p>
                            @endif
                        </div>
                    @else
                        <p style="margin:0;font-size:13px;color:rgba(232,237,245,0.62);">{{ __('coin.voximplant.no_phone') }}</p>
                    @endif
                </div>
            @endif

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 16px;font-size:16px;font-weight:600;">{{ __('coin.admin.account_controls') }}</h2>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.kyc_status')) }}</label>
                        <select name="kyc_status" style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                            @foreach($kycStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('kyc_status', $user->kyc_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.blocked_label')) }}</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;">
                            <input type="hidden" name="is_blocked" value="0">
                            <input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked', $user->is_blocked))>
                            {{ __('coin.admin.block_sign_in') }}
                        </label>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.phone')) }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.telegram')) }}</label>
                        <input type="text" name="telegram" value="{{ old('telegram', $user->telegram) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.country_code')) }}</label>
                        <input type="text" name="country_code" maxlength="2" value="{{ old('country_code', $user->country_code) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.lead_note')) }}</label>
                        <textarea name="admin_lead_note" rows="4" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">{{ old('admin_lead_note', $user->admin_lead_note) }}</textarea>
                    </div>
                    <div style="grid-column:1/-1;">
                        <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.save_changes') }}</button>
                    </div>
                </form>
            </div>

            @include('admin.partials.user-activity-tabs', [
                'user' => $user,
                'activeTab' => $activityTab,
            ])
        </div>

        <div>
            @include('admin.partials.user-context', [
                'user' => $user,
                'referralVolume' => $referralVolume,
                'referralEarnings' => $referralEarnings,
            ])
            @include('admin.partials.user-notification-form', ['user' => $user])
        </div>
    </div>
@endsection

@if ($voximplantReady && $voximplantDestination)
    @include('admin.partials.vox-call-modal')

    @push('scripts')
        @vite(['resources/js/admin-vox-call.js'])
    @endpush
@endif
