<a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="coin-auth-brand" style="max-width: min(320px, 86vw);">
    <x-brand-logo variant="horizontal" fluid :max-height="52" class="coin-auth-brand__logo" />
</a>
