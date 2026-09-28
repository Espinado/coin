@php
    use App\Support\PlatformBrand;
    use App\Support\SeoSchema;

    $brand = PlatformBrand::name();
    $legal = PlatformBrand::legalName();
    $contactEmail = trim($companyLegal['company_email'] ?? '') ?: config('coin.contact_email');
    $title = __('coin.about.title');
@endphp
<x-coin-legal-layout
    :title="$title"
    :description="__('coin.about.meta_description', ['brand' => $brand])"
    :canonical="route('about')"
    :json-ld="SeoSchema::about()"
>
    <div class="coin-legal-kicker">{{ __('coin.about.kicker') }}</div>
    <h1 class="coin-legal-title">{{ __('coin.about.heading', ['brand' => $brand]) }}</h1>

    <div class="coin-legal-body" style="white-space: normal;">
        <p>{{ __('coin.about.intro', ['brand' => $brand]) }}</p>
        <p>{{ __('coin.about.body') }}</p>

        <h2 style="margin: 36px 0 12px; font-size: 22px; color: #f0fbff;">{{ __('coin.about.how_title') }}</h2>
        <ol style="margin: 0; padding-left: 1.25em; display: grid; gap: 10px;">
            <li>{{ __('coin.about.how_1') }}</li>
            <li>{{ __('coin.about.how_2') }}</li>
            <li>{{ __('coin.about.how_3') }}</li>
            <li>{{ __('coin.about.how_4') }}</li>
            <li>{{ __('coin.about.how_5') }}</li>
            <li>{{ __('coin.about.how_6') }}</li>
        </ol>

        <h2 style="margin: 36px 0 12px; font-size: 22px; color: #f0fbff;">{{ __('coin.about.not_title', ['brand' => $brand]) }}</h2>
        <p>{{ __('coin.about.not_body', ['brand' => $brand]) }}</p>

        <h2 style="margin: 36px 0 12px; font-size: 22px; color: #f0fbff;">{{ __('coin.about.risks_title') }}</h2>
        <p>
            {{ __('coin.about.risks_body') }}
            <a href="{{ route('legal.show', ['legalPage' => 'risks']) }}" target="_blank" rel="noopener noreferrer">{{ __('coin.legal.slugs.risks') }}</a>
            ·
            <a href="{{ route('legal.show', ['legalPage' => 'terms']) }}" target="_blank" rel="noopener noreferrer">{{ __('coin.legal.slugs.terms') }}</a>
        </p>

        <h2 style="margin: 36px 0 12px; font-size: 22px; color: #f0fbff;">{{ __('coin.about.company_title') }}</h2>
        <p>{{ __('coin.about.company_operator', ['legal' => $legal]) }}</p>
        <p>
            {{ __('coin.about.company_support') }}
            @if(filled($contactEmail))
                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
            @endif
        </p>
        <p>
            <a href="{{ route('home') }}">{{ parse_url(route('home'), PHP_URL_HOST) }}</a>
        </p>
    </div>
</x-coin-legal-layout>
