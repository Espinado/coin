@php
    use App\Models\LegalPage;

    $preferredOrder = [
        LegalPage::SLUG_TERMS,
        LegalPage::SLUG_PRIVACY,
        LegalPage::SLUG_RISKS,
        LegalPage::SLUG_FAQ,
        LegalPage::SLUG_ABOUT,
    ];

    $legalNavPages = LegalPage::query()
        ->published()
        ->get()
        ->sortBy(static function (LegalPage $page) use ($preferredOrder): int {
            $index = array_search($page->slug, $preferredOrder, true);

            return $index === false ? 1000 + (int) $page->sort_order : $index;
        })
        ->values();

    $legalSectionActive = (int) $section === 9;
@endphp

@if($legalNavPages->isNotEmpty())
    <details class="coin-nav-legal {{ $legalSectionActive ? 'is-active' : '' }}" @if($legalSectionActive) open @endif>
        <summary class="coin-nav-item coin-nav-legal__summary">
            <span class="coin-nav-dot" aria-hidden="true"></span>
            <span class="coin-nav-item__label">{{ __('coin.nav.legal_information') }}</span>
            <span class="coin-nav-legal__chevron" aria-hidden="true"></span>
        </summary>
        <div class="coin-nav-legal__list">
            @foreach($legalNavPages as $legalPage)
                <button
                    type="button"
                    wire:click="openLegalPage({{ json_encode($legalPage->slug) }})"
                    class="coin-nav-legal__link {{ $legalSectionActive && $legalSlug === $legalPage->slug ? 'coin-nav-legal__link--active' : '' }}"
                >{{ $legalPage->slugLabel() }}</button>
            @endforeach
        </div>
    </details>
@endif
