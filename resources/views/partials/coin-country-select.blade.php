@php
    use App\Support\PhoneCountries;

    $inputName = $inputName ?? 'country_code';
    $wireModel = $wireModel ?? null;
    $selectedIso = old($inputName, $selectedIso ?? PhoneCountries::DEFAULT_ISO);
    $selected = PhoneCountries::find($selectedIso) ?? PhoneCountries::find(PhoneCountries::DEFAULT_ISO);
    $countryBorder = $countryBorder ?? 'rgba(150,235,250,0.18)';
@endphp

<div
    class="coin-phone-country coin-country-select"
    data-phone-country
    style="--coin-phone-border: {{ $countryBorder }};"
>
    <input
        type="hidden"
        name="{{ $inputName }}"
        value="{{ $selected['iso'] }}"
        data-phone-country-input
        @if($wireModel) wire:model="{{ $wireModel }}" @endif
    >
    <button
        type="button"
        class="coin-phone-country__trigger"
        data-phone-country-trigger
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-label="{{ __('coin.auth.country') }}"
    >
        <img
            class="coin-phone-country__flag"
            src="{{ PhoneCountries::flagUrl($selected['iso'], 40) }}"
            alt=""
            width="22"
            height="16"
            loading="lazy"
            decoding="async"
            data-phone-country-flag
        >
        <span class="coin-phone-country__name-label" data-phone-country-name>{{ $selected['name'] }}</span>
        <span class="coin-phone-country__chevron" aria-hidden="true"></span>
    </button>

    <div class="coin-phone-country__menu" data-phone-country-menu hidden role="listbox" aria-label="{{ __('coin.auth.country') }}">
        <div class="coin-phone-country__search-wrap">
            <input
                type="search"
                class="coin-phone-country__search"
                data-phone-country-search
                placeholder="{{ __('coin.auth.country_search_placeholder') }}"
                autocomplete="off"
                spellcheck="false"
                aria-label="{{ __('coin.auth.country_search_placeholder') }}"
            >
        </div>
        <div class="coin-phone-country__empty" data-phone-country-empty hidden>{{ __('coin.auth.country_search_empty') }}</div>
        @foreach(PhoneCountries::all() as $country)
            <button
                type="button"
                class="coin-phone-country__option{{ $country['iso'] === $selected['iso'] ? ' is-selected' : '' }}"
                role="option"
                data-phone-country-option
                data-iso="{{ $country['iso'] }}"
                data-dial="{{ $country['dial'] }}"
                data-name="{{ $country['name'] }}"
                data-flag="{{ PhoneCountries::flagUrl($country['iso'], 40) }}"
                aria-selected="{{ $country['iso'] === $selected['iso'] ? 'true' : 'false' }}"
            >
                <img class="coin-phone-country__flag" src="{{ PhoneCountries::flagUrl($country['iso'], 40) }}" alt="" width="22" height="16" loading="lazy" decoding="async">
                <span class="coin-phone-country__name">{{ $country['name'] }}</span>
            </button>
        @endforeach
    </div>
</div>
