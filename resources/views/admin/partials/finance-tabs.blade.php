@php
    $active = $active ?? 'commissions';
    if (! in_array($active, ['commissions', 'profit-accrual'], true)) {
        $active = 'commissions';
    }

    $tabs = [];

    if ($canManageWithdrawals ?? false) {
        $tabs['commissions'] = [
            'label' => __('coin.admin.commissions'),
            'url' => route('admin.commissions.index'),
        ];
    }

    if ($canManagePlans ?? false) {
        $tabs['profit-accrual'] = [
            'label' => __('coin.admin.profit_accrual'),
            'url' => route('admin.profit-accrual.index'),
        ];
    }

    if ($tabs !== [] && ! array_key_exists($active, $tabs)) {
        $active = array_key_first($tabs);
    }
@endphp

@if($tabs !== [])
<div class="admin-card admin-section-tabs" style="margin-bottom:16px;">
    <div style="margin-bottom:14px;">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.nav_finance') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.finance_reports_sub') }}</p>
    </div>
    <nav class="admin-section-tabs__row" aria-label="{{ __('coin.admin.nav_finance') }}">
        @foreach($tabs as $key => $tab)
            <a
                href="{{ $tab['url'] }}"
                class="admin-section-tabs__tab{{ $active === $key ? ' is-active' : '' }}"
                @if($active === $key) aria-current="page" @endif
            >
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
@endif
