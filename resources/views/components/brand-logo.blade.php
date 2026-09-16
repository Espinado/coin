@props([
    'variant' => 'horizontal',
    'height' => 32,
])

<img
    {{ $attributes->merge(['class' => '']) }}
    src="{{ \App\Support\PlatformBrand::logo($variant) }}"
    alt="{{ \App\Support\PlatformBrand::name() }}"
    style="height: {{ $height }}px; width: auto; max-width: 100%; display: block;"
/>
