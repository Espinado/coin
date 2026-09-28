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
@endphp

@if($legalNavPages->isNotEmpty())
    <details class="coin-nav-legal">
        <summary class="coin-nav-item coin-nav-legal__summary">
            <span class="coin-nav-dot" aria-hidden="true"></span>
            <span class="coin-nav-item__label">{{ __('coin.nav.legal_information') }}</span>
            <span class="coin-nav-legal__chevron" aria-hidden="true"></span>
        </summary>
        <div class="coin-nav-legal__list">
            @foreach($legalNavPages as $legalPage)
                <a
                    href="{{ route('legal.show', $legalPage) }}"
                    class="coin-nav-legal__link"
                    target="_blank"
                    rel="noopener noreferrer"
                    wire:click="closeMenu"
                >{{ $legalPage->slugLabel() }}</a>
            @endforeach
        </div>
    </details>
@endif
