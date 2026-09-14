<nav style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
    <a href="{{ route('admin.dashboard') }}" class="admin-btn">{{ __('coin.admin.overview') }}</a>
    <a href="{{ route('admin.users.index') }}" class="admin-btn">{{ __('coin.admin.users') }}</a>
    <a href="{{ route('admin.deposits.index') }}" class="admin-btn">
        {{ __('coin.admin.top_ups') }}
        @if(($pendingDepositsCount ?? 0) > 0)
            <span style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,180,84,0.18);color:#ffb454;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $pendingDepositsCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.withdrawals.index') }}" class="admin-btn">
        {{ __('coin.admin.payouts') }}
        @if(($pendingWithdrawalsCount ?? 0) > 0)
            <span style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,143,143,0.18);color:#ff8f8f;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $pendingWithdrawalsCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.plans.index') }}" class="admin-btn">{{ __('coin.admin.plans') }}</a>
    <a href="{{ route('admin.profit-accrual.index') }}" class="admin-btn">{{ __('coin.admin.profit_accrual') }}</a>
    <a href="{{ route('admin.settings.edit') }}" class="admin-btn">{{ __('coin.admin.settings') }}</a>
    <a href="{{ route('admin.support.index') }}" class="admin-btn" data-admin-support-nav>
        {{ __('coin.admin.support') }}
        @if(($unreadSupportCount ?? 0) > 0)
            <span class="admin-support-badge" data-admin-support-nav-badge style="margin-left:6px;padding:3px 8px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;box-shadow:0 0 14px rgba(255,180,84,0.45);">{{ $unreadSupportCount }}</span>
        @endif
    </a>
</nav>
