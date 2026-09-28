@php
    use App\Support\PlatformBrand;
    use App\Support\SeoVisibility;

    $pageTitle = $title ?? PlatformBrand::name();
    $metaDescription = $description ?? __('coin.seo.meta_description', ['brand' => PlatformBrand::name()]);
    $canonicalUrl = SeoVisibility::canonicalUrl($canonical ?? null);
    $robots = SeoVisibility::robotsMeta(isset($noindex) ? (bool) $noindex : null);
    $resolvedOgTitle = $ogTitle ?? $pageTitle;
    $resolvedOgDescription = $ogDescription ?? $metaDescription;
    $resolvedOgImage = $ogImage ?? PlatformBrand::ogImageUrl();
    $resolvedOgType = $ogType ?? 'website';
    $siteName = PlatformBrand::name();
    $locale = str_replace('_', '-', app()->getLocale());
    $schemas = isset($jsonLd) ? (array) $jsonLd : [];
@endphp

<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:type" content="{{ $resolvedOgType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $resolvedOgTitle }}">
<meta property="og:description" content="{{ $resolvedOgDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $resolvedOgImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="{{ $locale }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $resolvedOgTitle }}">
<meta name="twitter:description" content="{{ $resolvedOgDescription }}">
<meta name="twitter:image" content="{{ $resolvedOgImage }}">

@foreach($schemas as $schema)
    @if(is_array($schema) && $schema !== [])
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
@endforeach
