@props([
    'variant' => 'horizontal',
    'height' => 32,
    'fluid' => false,
])

<img
    {{ $attributes->merge(['class' => '']) }}
    src="{{ \App\Support\PlatformBrand::logo($variant) }}"
    alt="{{ \App\Support\PlatformBrand::name() }}"
    @if($fluid)
        style="max-width: 100%; height: auto; width: auto; display: block; object-fit: contain;"
    @else
        style="height: {{ $height }}px; width: auto; max-width: 100%; display: block; object-fit: contain;"
    @endif
/>
