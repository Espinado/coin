@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.settings_tab_mailboxes')]))

@section('content')
    @include('admin.partials.settings-tabs', ['active' => 'mailboxes'])

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.35)' : 'rgba(255,180,84,0.35)' }};">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h2 style="margin:0;font-size:20px;font-weight:600;">{{ __('coin.admin.mailboxes.server_title') }}</h2>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.mailboxes.server_sub') }}</p>
        <div style="margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 20px;font-size:13.5px;">
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.domain')) }}</div>
                <div style="margin-top:6px;">{{ $server['domain'] }}</div>
            </div>
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.mail_host')) }}</div>
                <div style="margin-top:6px;">{{ $server['mail_host'] }}</div>
            </div>
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.imap')) }}</div>
                <div style="margin-top:6px;">{{ $server['imap_port'] }} / {{ $server['encryption'] }}</div>
            </div>
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.smtp')) }}</div>
                <div style="margin-top:6px;">{{ $server['smtp_port'] }} / {{ $server['encryption'] }}</div>
            </div>
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.app_from')) }}</div>
                <div style="margin-top:6px;">{{ $server['from_name'] }} &lt;{{ $server['from_address'] }}&gt;</div>
            </div>
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.webmail')) }}</div>
                <div style="margin-top:6px;">
                    @if(filled($server['webmail_url']))
                        <a href="{{ $server['webmail_url'] }}" target="_blank" rel="noopener noreferrer">{{ $server['webmail_url'] }}</a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0;font-size:20px;font-weight:600;">{{ __('coin.admin.mailboxes.list_title') }}</h2>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.mailboxes.list_sub') }}</p>

        @if($listError)
            <p style="margin:16px 0 0;font-size:13px;color:#ff8f8f;">{{ $listError }}</p>
        @elseif($mailboxes === [])
            <p style="margin:16px 0 0;font-size:13px;color:rgba(232,237,245,0.6);">{{ __('coin.admin.mailboxes.empty') }}</p>
        @else
            <div style="margin-top:16px;overflow:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                    <thead>
                        <tr style="text-align:left;color:rgba(232,237,245,0.55);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                            <th style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);">{{ mb_strtoupper(__('coin.admin.mailboxes.email')) }}</th>
                            <th style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);">{{ mb_strtoupper(__('coin.admin.mailboxes.password_status')) }}</th>
                            <th style="padding:10px 8px;border-bottom:1px solid rgba(255,255,255,0.08);"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mailboxes as $box)
                            <tr>
                                <td style="padding:12px 8px;border-bottom:1px solid rgba(255,255,255,0.06);">{{ $box['email'] }}</td>
                                <td style="padding:12px 8px;border-bottom:1px solid rgba(255,255,255,0.06);color:rgba(232,237,245,0.72);">
                                    {{ $box['has_password'] ? __('coin.admin.mailboxes.password_known') : __('coin.admin.mailboxes.password_unknown') }}
                                </td>
                                <td style="padding:12px 8px;border-bottom:1px solid rgba(255,255,255,0.06);text-align:right;">
                                    <a class="admin-btn" href="{{ route('admin.settings.mailboxes.show', ['localPart' => $box['local_part']]) }}">{{ __('coin.admin.mailboxes.open') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0;font-size:20px;font-weight:600;">{{ __('coin.admin.mailboxes.create_title') }}</h2>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.mailboxes.create_sub', ['domain' => $server['domain']]) }}</p>

        <form method="POST" action="{{ route('admin.settings.mailboxes.store') }}" style="margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start;">
            @csrf
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.mailboxes.local_part')) }}</label>
                <div style="margin-top:8px;display:flex;align-items:stretch;gap:8px;">
                    <input type="text" name="local_part" value="{{ old('local_part') }}" required autocomplete="off" placeholder="info"
                        style="flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    <span style="display:inline-flex;align-items:center;padding:0 10px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:rgba(232,237,245,0.7);font-size:13px;white-space:nowrap;">{{ '@'.$server['domain'] }}</span>
                </div>
                @error('local_part')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.mailboxes.quota_mb')) }}</label>
                <input type="number" name="quota_mb" value="{{ old('quota_mb') }}" min="0" step="1" placeholder="0"
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.admin.mailboxes.quota_hint') }}</p>
                @error('quota_mb')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.mailboxes.password')) }}</label>
                <div style="margin-top:8px;display:flex;gap:8px;align-items:stretch;">
                    <input id="mailbox-create-password" type="text" name="password" value="{{ old('password') }}" required autocomplete="new-password"
                        style="flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    <button type="button" class="admin-btn" data-generate-password="#mailbox-create-password">{{ __('coin.admin.mailboxes.generate') }}</button>
                </div>
                @error('password')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="admin-btn admin-btn-primary" @disabled(! $enabled)>{{ __('coin.admin.mailboxes.create') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('[data-generate-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.querySelector(btn.getAttribute('data-generate-password'));
                if (! input) return;
                var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
                var out = '';
                for (var i = 0; i < 20; i++) {
                    out += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                input.value = out;
                input.focus();
                input.select();
            });
        });
    </script>
@endsection
