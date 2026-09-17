@props([
    'variant' => 'horizontal',
    'height' => 32,
    'fluid' => false,
    'maxHeight' => 52,
])

<img
    {{ $attributes->merge(['class' => '']) }}
    src="{{ \App\Support\PlatformBrand::logo($variant) }}"
    alt="{{ \App\Support\PlatformBrand::name() }}"
    @if($fluid)
        style="max-width: 100%; height: auto; width: auto; max-height: {{ (int) $maxHeight }}px; display: block; object-fit: contain;"
    @else
        style="height: {{ (int) $height }}px; width: auto; max-width: 100%; max-height: {{ (int) $maxHeight }}px; display: block; object-fit: contain;"
    @endif
/>
