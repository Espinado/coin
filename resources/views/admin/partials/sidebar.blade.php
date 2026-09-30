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

    $queuesActive = $adminNavActive(
        $routeName,
        'admin.support',
        'admin.deposits',
        'admin.withdrawals',
        'admin.early-unlocks',
        'admin.plan-changes',
    );
    $financeReportsActive = $adminNavActive($routeName, 'admin.commissions', 'admin.profit-accrual', 'admin.epochs');
    $logsActive = $adminNavActive($routeName, 'admin.payment-logs', 'admin.system-logs');
    $systemActive = $adminNavActive($routeName, 'admin.settings', 'admin.legal', 'admin.admins', 'admin.broadcasts');

    $showQueues = ($canManageSupport ?? false)
        || ($canManageDeposits ?? false)
        || ($canManageWithdrawals ?? false)
        || ($canManagePlanChanges ?? false);
    $showFinanceReports = ($canManageWithdrawals ?? false) || ($canManagePlans ?? false);
    $showLogs = ($canAccessPaymentLogs ?? false);
    $showSystem = ($canManageSettings ?? false) || ($canManageLegal ?? false) || ($canManageAdmins ?? false) || ($canManageBroadcasts ?? false);

    $queuesInboxCount = 0;
    if ($canManageSupport ?? false) {
        $queuesInboxCount += (int) ($unreadSupportCount ?? 0);
    }
    if ($canManageDeposits ?? false) {
        $queuesInboxCount += (int) ($pendingDepositsCount ?? 0);
    }
    if ($canManageWithdrawals ?? false) {
        $queuesInboxCount += (int) ($pendingWithdrawalsCount ?? 0);
        $queuesInboxCount += (int) ($pendingEarlyUnlocksCount ?? 0);
    }
    if ($canManagePlanChanges ?? false) {
        $queuesInboxCount += (int) ($pendingPlanChangesCount ?? 0);
    }
@endphp

<aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar__inner">
        <div class="admin-sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                <x-brand-logo variant="horizontal" fluid :max-height="52" class="admin-sidebar-brand__logo" />
            </a>
            <div class="admin-sidebar-brand__meta">
                <span class="admin-badge">STAFF ONLY</span>
                <form method="POST" action="{{ route('admin.logout') }}" class="admin-sidebar-logout-form">
                    @csrf
                    <button type="submit" class="admin-sidebar-logout">{{ __('coin.nav.logout') }}</button>
                </form>
            </div>
        </div>

        <nav class="admin-sidebar-nav" id="admin-sidebar-nav" aria-label="{{ __('coin.admin.open_menu') }}">
            <a href="{{ route('admin.dashboard') }}" class="{{ $navClass($routeName === 'admin.dashboard') }}">{{ __('coin.admin.overview') }}</a>

            @if($showQueues)
                <div class="admin-sidebar-section {{ $queuesActive ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">
                        <span>{{ __('coin.admin.nav_queues') }}</span>
                        @if($queuesInboxCount > 0)
                            <span class="admin-sidebar-badge admin-sidebar-badge--amber">{{ $queuesInboxCount }}</span>
                        @endif
                    </div>
                    <div class="admin-sidebar-section__links">
                        @if($canManageSupport ?? false)
                            <a href="{{ route('admin.support.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.support')) }} admin-sidebar-link--sub" data-admin-support-nav>
                                <span>{{ __('coin.admin.support') }}</span>
                                @if(($unreadSupportCount ?? 0) > 0)
                                    <span class="admin-sidebar-badge admin-sidebar-badge--support admin-support-badge" data-admin-support-nav-badge>{{ $unreadSupportCount }}</span>
                                @endif
                            </a>
                        @endif
                        @if($canManageDeposits ?? false)
                            <a href="{{ route('admin.deposits.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.deposits')) }} admin-sidebar-link--sub">
                                <span>{{ __('coin.admin.top_ups') }}</span>
                                @if(($pendingDepositsCount ?? 0) > 0)
                                    <span class="admin-sidebar-badge admin-sidebar-badge--amber">{{ $pendingDepositsCount }}</span>
                                @endif
                            </a>
                        @endif
                        @if($canManageWithdrawals ?? false)
                            <a href="{{ route('admin.withdrawals.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.withdrawals')) }} admin-sidebar-link--sub" data-admin-withdrawals-nav>
                                <span>{{ __('coin.admin.payouts') }}</span>
                                @if(($pendingWithdrawalsCount ?? 0) > 0)
                                    <span class="admin-sidebar-badge admin-sidebar-badge--red" data-admin-withdrawals-nav-badge>{{ $pendingWithdrawalsCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.early-unlocks.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.early-unlocks')) }} admin-sidebar-link--sub" data-admin-early-unlocks-nav>
                                <span>{{ __('coin.admin.early_unlocks') }}</span>
                                @if(($pendingEarlyUnlocksCount ?? 0) > 0)
                                    <span class="admin-sidebar-badge admin-sidebar-badge--amber" data-admin-early-unlocks-nav-badge>{{ $pendingEarlyUnlocksCount }}</span>
                                @endif
                            </a>
                        @endif
                        @if($canManagePlanChanges ?? false)
                            <a href="{{ route('admin.plan-changes.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plan-changes')) }} admin-sidebar-link--sub" data-admin-plan-changes-nav>
                                <span>{{ __('coin.admin.plan_changes') }}</span>
                                @if(($pendingPlanChangesCount ?? 0) > 0)
                                    <span class="admin-sidebar-badge admin-sidebar-badge--blue" data-admin-plan-changes-nav-badge>{{ $pendingPlanChangesCount }}</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if($canManageUsers ?? false)
                <div class="admin-sidebar-section {{ $adminNavActive($routeName, 'admin.users') ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">{{ __('coin.admin.nav_clients') }}</div>
                    <div class="admin-sidebar-section__links">
                        <a href="{{ route('admin.users.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.users')) }} admin-sidebar-link--sub">{{ __('coin.admin.users') }}</a>
                    </div>
                </div>
            @endif

            @if($showFinanceReports)
                <div class="admin-sidebar-section {{ $financeReportsActive ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">{{ __('coin.admin.nav_finance') }}</div>
                    <div class="admin-sidebar-section__links">
                        @if($canManageWithdrawals ?? false)
                            <a href="{{ route('admin.commissions.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.commissions')) }} admin-sidebar-link--sub">{{ __('coin.admin.commissions') }}</a>
                        @endif
                        @if($canManagePlans ?? false)
                            <a href="{{ route('admin.profit-accrual.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.profit-accrual') || $adminNavActive($routeName, 'admin.epochs')) }} admin-sidebar-link--sub">{{ __('coin.admin.profit_accrual') }}</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($canManagePlans ?? false)
                <div class="admin-sidebar-section {{ $adminNavActive($routeName, 'admin.plans') ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">{{ __('coin.admin.nav_product') }}</div>
                    <div class="admin-sidebar-section__links">
                        <a href="{{ route('admin.plans.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plans')) }} admin-sidebar-link--sub">{{ __('coin.admin.plans') }}</a>
                    </div>
                </div>
            @endif

            @if($showLogs)
                <div class="admin-sidebar-section {{ $logsActive ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">{{ __('coin.admin.logs_nav') }}</div>
                    <div class="admin-sidebar-section__links">
                        <a href="{{ route('admin.payment-logs.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.payment-logs')) }} admin-sidebar-link--sub">{{ __('coin.admin.logs_payments') }}</a>
                        <a href="{{ route('admin.system-logs.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.system-logs')) }} admin-sidebar-link--sub">{{ __('coin.admin.logs_system') }}</a>
                    </div>
                </div>
            @endif

            @if($showSystem)
                <div class="admin-sidebar-section {{ $systemActive ? 'admin-sidebar-section--active' : '' }}">
                    <div class="admin-sidebar-section__label">{{ __('coin.admin.nav_system') }}</div>
                    <div class="admin-sidebar-section__links">
                        @if($canManageSettings ?? false)
                            <a href="{{ route('admin.settings.edit') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.settings')) }} admin-sidebar-link--sub">{{ __('coin.admin.settings') }}</a>
                        @endif
                        @if($canManageLegal ?? false)
                            <a href="{{ route('admin.legal.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.legal')) }} admin-sidebar-link--sub">{{ __('coin.admin.legal.title') }}</a>
                        @endif
                        @if($canManageAdmins ?? false)
                            <a href="{{ route('admin.admins.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.admins')) }} admin-sidebar-link--sub">{{ __('coin.admin.admins.title') }}</a>
                        @endif
                        @if($canManageBroadcasts ?? false)
                            <a href="{{ route('admin.broadcasts.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.broadcasts')) }} admin-sidebar-link--sub">{{ __('coin.admin.broadcasts.title') }}</a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>
    </div>
</aside>
