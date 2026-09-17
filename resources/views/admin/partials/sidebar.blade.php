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
        return 'admin-sidebar-link'.($active ? ' admin-sidebar-link--active' : '');
    };
@endphp

<aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar__inner">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
            <x-brand-logo variant="horizontal" fluid :max-height="44" class="admin-sidebar-brand__logo" />
            <span class="admin-badge">STAFF ONLY</span>
        </a>

        <nav class="admin-sidebar-nav" id="admin-sidebar-nav" aria-label="{{ __('coin.admin.open_menu') }}">
            <a href="{{ route('admin.dashboard') }}" class="{{ $navClass($routeName === 'admin.dashboard') }}">{{ __('coin.admin.overview') }}</a>
            <a href="{{ route('admin.users.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.users')) }}">{{ __('coin.admin.users') }}</a>
            <a href="{{ route('admin.deposits.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.deposits')) }}">
                <span>{{ __('coin.admin.top_ups') }}</span>
                @if(($pendingDepositsCount ?? 0) > 0)
                    <span class="admin-sidebar-badge admin-sidebar-badge--amber">{{ $pendingDepositsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.withdrawals.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.withdrawals')) }}" data-admin-withdrawals-nav>
                <span>{{ __('coin.admin.payouts') }}</span>
                @if(($pendingWithdrawalsCount ?? 0) > 0)
                    <span class="admin-sidebar-badge admin-sidebar-badge--red" data-admin-withdrawals-nav-badge>{{ $pendingWithdrawalsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.plan-changes.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plan-changes')) }}" data-admin-plan-changes-nav>
                <span>{{ __('coin.admin.plan_changes') }}</span>
                @if(($pendingPlanChangesCount ?? 0) > 0)
                    <span class="admin-sidebar-badge admin-sidebar-badge--blue" data-admin-plan-changes-nav-badge>{{ $pendingPlanChangesCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.plans.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plans')) }}">{{ __('coin.admin.plans') }}</a>
            <a href="{{ route('admin.profit-accrual.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.profit-accrual')) }}">{{ __('coin.admin.profit_accrual') }}</a>
            <a href="{{ route('admin.admins.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.admins')) }}">{{ __('coin.admin.admins.title') }}</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.settings')) }}">{{ __('coin.admin.settings') }}</a>
            <a href="{{ route('admin.broadcasts.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.broadcasts')) }}">{{ __('coin.admin.broadcasts.title') }}</a>
            <a href="{{ route('admin.support.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.support')) }}" data-admin-support-nav>
                <span>{{ __('coin.admin.support') }}</span>
                @if(($unreadSupportCount ?? 0) > 0)
                    <span class="admin-sidebar-badge admin-sidebar-badge--support admin-support-badge" data-admin-support-nav-badge>{{ $unreadSupportCount }}</span>
                @endif
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-sidebar-link admin-sidebar-link--logout">{{ __('coin.nav.logout') }}</button>
            </form>
        </div>
    </div>
</aside>
