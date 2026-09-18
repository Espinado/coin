<div class="admin-card" style="margin-top:16px;">
    <h2 style="margin:0 0 8px;font-size:16px;font-weight:600;">{{ __('coin.admin.user_notification.title') }}</h2>
    <p style="margin:0 0 16px;font-size:13px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.user_notification.hint') }}</p>
    <form method="POST" action="{{ route('admin.users.notifications.store', $user) }}" style="display:flex;flex-direction:column;gap:14px;">
        @csrf
        <div>
            <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.user_notification.title_field')) }}</label>
            <input type="text" name="notification_title" value="{{ old('notification_title') }}" maxlength="160" required
                style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
            @error('notification_title')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
        </div>
        <div>
            <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.user_notification.body_field')) }}</label>
            <textarea name="notification_body" rows="6" required maxlength="10000"
                style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;">{{ old('notification_body') }}</textarea>
            @error('notification_body')<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
        </div>
        <div>
            <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.user_notification.send') }}</button>
        </div>
    </form>
</div>
