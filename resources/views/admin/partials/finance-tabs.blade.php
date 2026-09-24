@php
    $active = $active ?? 'deposits';
    if (! in_array($active, ['deposits', 'withdrawals', 'profit-accrual', 'commissions'], true)) {
        $active = 'deposits';
    }

    $tabs = [
        'deposits' => [
            'label' => __('coin.admin.top_ups'),
            'url' => route('admin.deposits.index'),
            'badge' => ($pendingDepositsCount ?? 0) > 0 ? (int) $pendingDepositsCount : null,
            'badge_class' => 'admin-sidebar-badge--amber',
        ],
        'withdrawals' => [
            'label' => __('coin.admin.payouts'),
            'url' => route('admin.withdrawals.index'),
            'badge' => ($pendingWithdrawalsCount ?? 0) > 0 ? (int) $pendingWithdrawalsCount : null,
            'badge_class' => 'admin-sidebar-badge--red',
            'nav_attrs' => 'data-admin-withdrawals-nav',
            'badge_attrs' => 'data-admin-withdrawals-nav-badge',
        ],
        'profit-accrual' => [
            'label' => __('coin.admin.profit_accrual'),
            'url' => route('admin.profit-accrual.index'),
        ],
        'commissions' => [
            'label' => __('coin.admin.commissions'),
            'url' => route('admin.commissions.index'),
        ],
    ];
@endphp

<div class="admin-card admin-section-tabs" style="margin-bottom:16px;">
    <div style="margin-bottom:14px;">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.finance') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.finance_sub') }}</p>
    </div>
    <nav class="admin-section-tabs__row" aria-label="{{ __('coin.admin.finance') }}">
        @foreach($tabs as $key => $tab)
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
    </nav>
</div>
