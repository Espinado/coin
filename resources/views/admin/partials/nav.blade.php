@php
    $routeName = request()->route()?->getName() ?? '';

    $adminNavActive = static function (string $routeName, string ...$prefixes): bool {
        foreach ($prefixes as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return true;
            }
        }

        return false;
    };

    $navClass = static function (bool $active): string {
        return 'admin-btn admin-nav-link'.($active ? ' admin-nav-link--active' : '');
    };
@endphp

<nav class="admin-nav" id="admin-nav">
    <button type="button" class="admin-nav-toggle" id="admin-nav-toggle" aria-expanded="false" aria-controls="admin-nav-links">
        {{ __('coin.admin.open_menu') }}
    </button>
    <div class="admin-nav-links" id="admin-nav-links">
        <a href="{{ route('admin.dashboard') }}" class="{{ $navClass($routeName === 'admin.dashboard') }}">{{ __('coin.admin.overview') }}</a>
        <a href="{{ route('admin.users.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.users')) }}">{{ __('coin.admin.users') }}</a>
        <a href="{{ route('admin.deposits.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.deposits')) }}">
            {{ __('coin.admin.top_ups') }}
            @if(($pendingDepositsCount ?? 0) > 0)
                <span style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,180,84,0.18);color:#ffb454;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $pendingDepositsCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.withdrawals.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.withdrawals')) }}" data-admin-withdrawals-nav>
            {{ __('coin.admin.payouts') }}
            @if(($pendingWithdrawalsCount ?? 0) > 0)
                <span data-admin-withdrawals-nav-badge style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,143,143,0.18);color:#ff8f8f;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $pendingWithdrawalsCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.plan-changes.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plan-changes')) }}" data-admin-plan-changes-nav>
            {{ __('coin.admin.plan_changes') }}
            @if(($pendingPlanChangesCount ?? 0) > 0)
                <span data-admin-plan-changes-nav-badge style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(150,200,255,0.18);color:#9ecbff;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $pendingPlanChangesCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.plans.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plans')) }}">{{ __('coin.admin.plans') }}</a>
        <a href="{{ route('admin.profit-accrual.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.profit-accrual')) }}">{{ __('coin.admin.profit_accrual') }}</a>
        <a href="{{ route('admin.admins.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.admins')) }}">{{ __('coin.admin.admins.title') }}</a>
        <a href="{{ route('admin.settings.edit') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.settings')) }}">{{ __('coin.admin.settings') }}</a>
        <a href="{{ route('admin.support.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.support')) }}" data-admin-support-nav>
            {{ __('coin.admin.support') }}
            @if(($unreadSupportCount ?? 0) > 0)
                <span class="admin-support-badge" data-admin-support-nav-badge style="margin-left:6px;padding:3px 8px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;box-shadow:0 0 14px rgba(255,180,84,0.45);">{{ $unreadSupportCount }}</span>
            @endif
        </a>
    </div>
</nav>
<script>
document.getElementById('admin-nav-toggle')?.addEventListener('click', function () {
    const nav = document.getElementById('admin-nav');
    const open = nav?.classList.toggle('is-open');
    this.setAttribute('aria-expanded', open ? 'true' : 'false');
});
</script>
