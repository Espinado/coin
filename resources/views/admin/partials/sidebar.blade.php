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

    $financeActive = $adminNavActive(
        $routeName,
        'admin.deposits',
        'admin.withdrawals',
        'admin.commissions',
        'admin.profit-accrual',
        'admin.epochs',
    );

    $financeBadges = [];
    if (($canManageDeposits ?? false) && ($pendingDepositsCount ?? 0) > 0) {
        $financeBadges[] = ['count' => $pendingDepositsCount, 'class' => 'admin-sidebar-badge--amber'];
    }
    if (($canManageWithdrawals ?? false) && ($pendingWithdrawalsCount ?? 0) > 0) {
        $financeBadges[] = ['count' => $pendingWithdrawalsCount, 'class' => 'admin-sidebar-badge--red', 'attrs' => 'data-admin-withdrawals-nav-badge'];
    }

    $logsActive = $adminNavActive($routeName, 'admin.payment-logs', 'admin.plan-changes');
    $showLogsGroup = ($canAccessPaymentLogs ?? false) || ($canManagePlanChanges ?? false);
@endphp

<aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar__inner">
        <div class="admin-sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                <x-brand-logo variant="horizontal" fluid :max-height="44" class="admin-sidebar-brand__logo" />
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
            @if($canManageUsers ?? false)
                <a href="{{ route('admin.users.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.users')) }}">{{ __('coin.admin.users') }}</a>
            @endif
            @if($canAccessFinance ?? false)
                <a href="{{ $financeNavUrl ?? route('admin.deposits.index') }}" class="{{ $navClass($financeActive) }}" data-admin-withdrawals-nav>
                    <span>{{ __('coin.admin.finance') }}</span>
                    @if($financeBadges !== [])
                        <span class="admin-sidebar-link__badges">
                            @foreach($financeBadges as $badge)
                                <span class="admin-sidebar-badge {{ $badge['class'] }}" @if(! empty($badge['attrs'])) {!! $badge['attrs'] !!} @endif>{{ $badge['count'] }}</span>
                            @endforeach
                        </span>
                    @endif
                </a>
            @endif
            @if($showLogsGroup)
                <div class="admin-sidebar-group" data-admin-sidebar-group="logs">
                    <button
                        type="button"
                        class="admin-sidebar-link admin-sidebar-group__toggle {{ $logsActive ? 'admin-sidebar-link--active' : '' }}"
                        aria-expanded="false"
                        data-admin-sidebar-group-toggle
                    >
                        <span class="admin-sidebar-group__label">
                            <span>{{ __('coin.admin.logs_nav') }}</span>
                        </span>
                        <span class="admin-sidebar-group__chevron" aria-hidden="true"></span>
                    </button>
                    <div class="admin-sidebar-subnav">
                        @if($canAccessPaymentLogs ?? false)
                            <a href="{{ route('admin.payment-logs.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.payment-logs')) }} admin-sidebar-link--sub">{{ __('coin.admin.logs_payments') }}</a>
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
            @if($canManagePlans ?? false)
                <a href="{{ route('admin.plans.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.plans')) }}">{{ __('coin.admin.plans') }}</a>
            @endif
            @if($canManageAdmins ?? false)
                <a href="{{ route('admin.admins.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.admins')) }}">{{ __('coin.admin.admins.title') }}</a>
            @endif
            @if($canManageSettings ?? false)
                <a href="{{ route('admin.settings.edit') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.settings')) }}">{{ __('coin.admin.settings') }}</a>
            @endif
            @if($canManageLegal ?? false)
                <a href="{{ route('admin.legal.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.legal')) }}">{{ __('coin.admin.legal.title') }}</a>
            @endif
            @if($canManageBroadcasts ?? false)
                <a href="{{ route('admin.broadcasts.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.broadcasts')) }}">{{ __('coin.admin.broadcasts.title') }}</a>
            @endif
            @if($canManageSupport ?? false)
                <a href="{{ route('admin.support.index') }}" class="{{ $navClass($adminNavActive($routeName, 'admin.support')) }}" data-admin-support-nav>
                    <span>{{ __('coin.admin.support') }}</span>
                    @if(($unreadSupportCount ?? 0) > 0)
                        <span class="admin-sidebar-badge admin-sidebar-badge--support admin-support-badge" data-admin-support-nav-badge>{{ $unreadSupportCount }}</span>
                    @endif
                </a>
            @endif
        </nav>
    </div>
</aside>
