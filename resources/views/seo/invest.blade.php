@php
    /** @var \App\Models\LegalPage $page */
    $brand = \App\Support\PlatformBrand::name();
    $metaDescription = __('coin.seo_hubs.invest.meta_description', ['brand' => $brand]);
@endphp
<x-coin-legal-layout
    :title="$page->title"
    :description="$metaDescription"
    :canonical="route('seo.invest')"
    :json-ld="$jsonLd ?? []"
    :legal-nav="$legalNav ?? []"
    :current-page="$page"
>
    <div class="coin-legal-kicker">{{ mb_strtoupper($page->slugLabel()) }}</div>
    <h1 class="coin-legal-title">{{ $page->title }}</h1>

    <div class="coin-legal-body">{{ $page->body }}</div>

    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:28px;">
        <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 20px;border-radius:12px;background:oklch(0.78 0.12 195);color:#04101c;font-weight:600;font-size:14px;">
            {{ __('coin.seo_hubs.invest.cta_start') }}
        </a>
        <a href="{{ route('home') }}#plans" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 20px;border-radius:12px;border:1px solid rgba(150,235,250,0.22);color:#e6f4fa;font-size:14px;">
            {{ __('coin.seo_hubs.invest.cta_plans') }}
        </a>
    </div>
</x-coin-legal-layout>
