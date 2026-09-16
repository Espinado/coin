<div class="admin-card">
    <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ mb_strtoupper(__('coin.user_context.title')) }}</div>
    <div style="margin-top:12px;font-size:15px;font-weight:600;">
        <a href="{{ route('admin.users.show', $user) }}">{{ $user->accountLabel() }}</a>
    </div>
    <div style="margin-top:6px;font-size:13px;color:rgba(232,237,245,0.72);">{{ $user->email }}</div>
    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
        <span style="font-size:11px;padding:4px 8px;border-radius:6px;background:rgba(255,255,255,0.05);">KYC: {{ $user->kycLabel() }}</span>
        @if($user->is_blocked)
            <span style="font-size:11px;padding:4px 8px;border-radius:6px;background:rgba(255,143,143,0.15);color:#ff8f8f;">{{ __('coin.user_context.blocked') }}</span>
        @endif
    </div>
    <div style="margin-top:18px;display:flex;flex-direction:column;gap:10px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.balance') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->wallet?->formattedBalance() ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.available') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->wallet?->formattedAvailable() ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.locked') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->wallet?->formattedLocked() ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.pending') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->wallet?->formattedPending() ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.investments') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->contracts->count() }}</span></div>
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.nav.referrals') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ $user->referralProfile?->invited_count ?? 0 }}</span></div>
        @isset($referralEarnings)
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.user_context.referral_earned') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ \App\Support\MoneyFormat::amount($referralEarnings) }}</span></div>
        @endisset
        @isset($referralVolume)
        <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.user_context.referral_volume') }}</span><span style="font-family:'JetBrains Mono',monospace;">{{ \App\Support\MoneyFormat::amount($referralVolume) }}</span></div>
        @endisset
    </div>
    @if($user->relationLoaded('contracts') && $user->contracts->isNotEmpty())
        <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;font-size:12.5px;">
            @foreach($user->contracts->take(5) as $contract)
                <div style="padding:10px 12px;border-radius:10px;background:rgba(255,255,255,0.03);">
                    {{ $contract->plan?->name }} · {{ $contract->formattedPrincipal() }} · {{ $contract->statusLabel() }}
                </div>
            @endforeach
        </div>
    @endif
</div>
