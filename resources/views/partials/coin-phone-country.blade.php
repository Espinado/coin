@php
    use App\Support\PhoneCountries;

    $selectedIso = old('phone_country', PhoneCountries::DEFAULT_ISO);
    $selected = PhoneCountries::find($selectedIso) ?? PhoneCountries::find(PhoneCountries::DEFAULT_ISO);
    $phoneBorder = $phoneBorder ?? 'rgba(150,235,250,0.18)';
@endphp

<div
    class="coin-phone-country"
    data-phone-country
    style="--coin-phone-border: {{ $phoneBorder }};"
>
    <input type="hidden" name="phone_country" value="{{ $selected['iso'] }}" data-phone-country-input>
    <button
        type="button"
        class="coin-phone-country__trigger"
        data-phone-country-trigger
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-label="{{ __('coin.auth.phone_country') }}"
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
        <span class="coin-phone-country__dial" data-phone-country-dial>{{ $selected['dial'] }}</span>
        <span class="coin-phone-country__chevron" aria-hidden="true"></span>
    </button>

    <div class="coin-phone-country__menu" data-phone-country-menu hidden role="listbox" aria-label="{{ __('coin.auth.phone_country') }}">
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
                <span class="coin-phone-country__code">{{ $country['dial'] }}</span>
            </button>
        @endforeach
    </div>
</div>
