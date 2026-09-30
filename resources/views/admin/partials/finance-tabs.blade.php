@php
    $active = $active ?? 'deposits';
    if (! in_array($active, ['deposits', 'withdrawals', 'profit-accrual', 'commissions', 'early-unlocks'], true)) {
        $active = 'deposits';
    }

    $operationTabs = [];
    $reportTabs = [];

    if ($canManageDeposits ?? false) {
        $operationTabs['deposits'] = [
            'label' => __('coin.admin.top_ups'),
            'url' => route('admin.deposits.index'),
            'badge' => ($pendingDepositsCount ?? 0) > 0 ? (int) $pendingDepositsCount : null,
            'badge_class' => 'admin-sidebar-badge--amber',
        ];
    }

    if ($canManageWithdrawals ?? false) {
        $operationTabs['withdrawals'] = [
            'label' => __('coin.admin.payouts'),
            'url' => route('admin.withdrawals.index'),
            'badge' => ($pendingWithdrawalsCount ?? 0) > 0 ? (int) $pendingWithdrawalsCount : null,
            'badge_class' => 'admin-sidebar-badge--red',
            'nav_attrs' => 'data-admin-withdrawals-nav',
            'badge_attrs' => 'data-admin-withdrawals-nav-badge',
        ];

        $operationTabs['early-unlocks'] = [
            'label' => __('coin.admin.early_unlocks'),
            'url' => route('admin.early-unlocks.index'),
            'badge' => ($pendingEarlyUnlocksCount ?? 0) > 0 ? (int) $pendingEarlyUnlocksCount : null,
            'badge_class' => 'admin-sidebar-badge--amber',
            'nav_attrs' => 'data-admin-early-unlocks-nav',
            'badge_attrs' => 'data-admin-early-unlocks-nav-badge',
        ];

        $reportTabs['commissions'] = [
            'label' => __('coin.admin.commissions'),
            'url' => route('admin.commissions.index'),
        ];
    }

    if ($canManagePlans ?? false) {
        $reportTabs['profit-accrual'] = [
            'label' => __('coin.admin.profit_accrual'),
            'url' => route('admin.profit-accrual.index'),
        ];
    }

    $tabs = $operationTabs + $reportTabs;

    if (! array_key_exists($active, $tabs)) {
        $active = array_key_first($tabs) ?? 'deposits';
    }
@endphp

<div class="admin-card admin-section-tabs" style="margin-bottom:16px;">
    <div style="margin-bottom:14px;">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.finance') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.finance_sub') }}</p>
    </div>
    <nav class="admin-section-tabs__stack" aria-label="{{ __('coin.admin.finance') }}">
        @if($operationTabs !== [])
            <div class="admin-section-tabs__group">
                <div class="admin-section-tabs__group-label">{{ __('coin.admin.finance_ops') }}</div>
                <div class="admin-section-tabs__row">
                    @foreach($operationTabs as $key => $tab)
                        <a
                            href="{{ $tab['url'] }}"
                            class="admin-section-tabs__tab{{ $active === $key ? ' is-active' : '' }}"
                            @if($active === $key) aria-current="page" @endif
                            {!! $tab['nav_attrs'] ?? '' !!}
                        >
                            <span>{{ $tab['label'] }}</span>
                            @if(($tab['badge'] ?? null) > 0)
                                <span class="admin-section-tabs__count admin-sidebar-badge {{ $tab['badge_class'] ?? '' }}" {!! $tab['badge_attrs'] ?? '' !!}>{{ $tab['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if($reportTabs !== [])
            <div class="admin-section-tabs__group">
                <div class="admin-section-tabs__group-label">{{ __('coin.admin.finance_reports') }}</div>
                <div class="admin-section-tabs__row">
                    @foreach($reportTabs as $key => $tab)
                        <a
                            href="{{ $tab['url'] }}"
                            class="admin-section-tabs__tab{{ $active === $key ? ' is-active' : '' }}"
                            @if($active === $key) aria-current="page" @endif
                            {!! $tab['nav_attrs'] ?? '' !!}
                        >
                            <span>{{ $tab['label'] }}</span>
                            @if(($tab['badge'] ?? null) > 0)
                                <span class="admin-section-tabs__count admin-sidebar-badge {{ $tab['badge_class'] ?? '' }}" {!! $tab['badge_attrs'] ?? '' !!}>{{ $tab['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </nav>
</div>
