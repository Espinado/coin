@php
    $includeFaq = $includeFaq ?? true;
    $separator = $separator ?? '·';
    $class = trim(($class ?? '').' coin-legal-links');
    $style = $style ?? null;

    $items = [
        ['slug' => 'terms', 'label' => __('coin.legal.slugs.terms')],
        ['slug' => 'privacy', 'label' => __('coin.legal.slugs.privacy')],
        ['slug' => 'risks', 'label' => __('coin.legal.slugs.risks')],
    ];

    if ($includeFaq) {
        $items[] = ['slug' => 'faq', 'label' => __('coin.legal.slugs.faq')];
    }
@endphp

<nav class="{{ $class }}" @if($style) style="{{ $style }}" @endif aria-label="{{ __('coin.legal.nav_label') }}">
    @foreach($items as $index => $item)
        @if($index > 0)
            <span class="coin-legal-links__sep" aria-hidden="true">{{ $separator }}</span>
        @endif
        <a href="{{ route('legal.show', ['legalPage' => $item['slug']]) }}" class="coin-legal-links__item" target="_blank" rel="noopener noreferrer">{{ $item['label'] }}</a>
    @endforeach
</nav>
