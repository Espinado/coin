@php
    $active = $active ?? 'platform';
    if (! in_array($active, ['platform', 'mailboxes'], true)) {
        $active = 'platform';
    }

    $tabs = [
        'platform' => [
            'label' => __('coin.admin.settings_tab_platform'),
            'url' => route('admin.settings.edit'),
        ],
        'mailboxes' => [
            'label' => __('coin.admin.settings_tab_mailboxes'),
            'url' => route('admin.settings.mailboxes.index'),
        ],
    ];
@endphp

<div class="admin-card admin-section-tabs" style="margin-bottom:16px;">
    <div style="margin-bottom:14px;">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.settings') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.settings_sub') }}</p>
    </div>
    <nav class="admin-section-tabs__row" aria-label="{{ __('coin.admin.settings') }}">
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
