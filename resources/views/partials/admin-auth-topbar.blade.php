<header class="admin-topbar">
    <a href="{{ route('admin.login') }}" class="admin-topbar-brand">
        <x-brand-logo variant="horizontal" fluid :max-height="44" class="admin-topbar-brand__logo" />
        <span class="admin-badge">{{ __('coin.admin.staff_only') }}</span>
    </a>
</header>
