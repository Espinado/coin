@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => $mailbox['email']]))

@section('content')
    @include('admin.partials.settings-tabs', ['active' => 'mailboxes'])

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.35)' : 'rgba(255,180,84,0.35)' }};">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start;">
            <div>
                <h2 style="margin:0;font-size:20px;font-weight:600;">{{ $mailbox['email'] }}</h2>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.mailboxes.show_sub') }}</p>
            </div>
            <a class="admin-btn" href="{{ route('admin.settings.mailboxes.index') }}">{{ __('coin.admin.mailboxes.back') }}</a>
        </div>

        <div style="margin-top:20px;display:grid;gap:14px;">
            <div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.55);">{{ mb_strtoupper(__('coin.admin.mailboxes.password')) }}</div>
                @if(filled($mailbox['password'] ?? null))
                    <div style="margin-top:8px;display:flex;gap:8px;align-items:stretch;">
                        <input id="mailbox-stored-password" type="password" readonly value="{{ $mailbox['password'] }}"
                            style="flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <button type="button" class="admin-btn" data-toggle-password="#mailbox-stored-password">{{ __('coin.admin.mailboxes.show_password') }}</button>
                        <button type="button" class="admin-btn" data-copy-password="#mailbox-stored-password">{{ __('coin.admin.mailboxes.copy') }}</button>
                    </div>
                @else
                    <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.mailboxes.password_unknown_hint') }}</p>
                @endif
            </div>

            @if(filled($server['webmail_url']))
                <div>
                    <a class="admin-btn" href="{{ $server['webmail_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('coin.admin.mailboxes.open_webmail') }}</a>
                </div>
            @endif
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0;font-size:20px;font-weight:600;">{{ __('coin.admin.mailboxes.change_password_title') }}</h2>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.mailboxes.change_password_sub') }}</p>

        <form method="POST" action="{{ route('admin.settings.mailboxes.password', ['localPart' => $mailbox['local_part']]) }}" style="margin-top:18px;display:grid;gap:16px;">
            @csrf
            @method('PATCH')
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.mailboxes.new_password')) }}</label>
                <div style="margin-top:8px;display:flex;gap:8px;align-items:stretch;">
                    <input id="mailbox-new-password" type="text" name="password" value="{{ old('password') }}" required autocomplete="new-password"
                        style="flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    <button type="button" class="admin-btn" data-generate-password="#mailbox-new-password">{{ __('coin.admin.mailboxes.generate') }}</button>
                </div>
                @error('password')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
            </div>
            <div>
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.mailboxes.save_password') }}</button>
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
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.querySelector(btn.getAttribute('data-toggle-password'));
                if (! input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show
                    ? @json(__('coin.admin.mailboxes.hide_password'))
                    : @json(__('coin.admin.mailboxes.show_password'));
            });
        });
        document.querySelectorAll('[data-copy-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.querySelector(btn.getAttribute('data-copy-password'));
                if (! input) return;
                navigator.clipboard.writeText(input.value).then(function () {
                    btn.textContent = @json(__('coin.admin.mailboxes.copied'));
                    setTimeout(function () {
                        btn.textContent = @json(__('coin.admin.mailboxes.copy'));
                    }, 1500);
                });
            });
        });
    </script>
@endsection
