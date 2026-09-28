<?php

namespace App\Support;

final class PhoneCountries
{
    public const DEFAULT_ISO = 'LV';

    /**
     * @return list<array{iso: string, name: string, dial: string}>
     */
    public static function all(): array
    {
        return [
            ['iso' => 'AT', 'name' => 'Austria', 'dial' => '+43'],
            ['iso' => 'AZ', 'name' => 'Azerbaijan', 'dial' => '+994'],
            ['iso' => 'BY', 'name' => 'Belarus', 'dial' => '+375'],
            ['iso' => 'BE', 'name' => 'Belgium', 'dial' => '+32'],
            ['iso' => 'BG', 'name' => 'Bulgaria', 'dial' => '+359'],
            ['iso' => 'CA', 'name' => 'Canada', 'dial' => '+1'],
            ['iso' => 'HR', 'name' => 'Croatia', 'dial' => '+385'],
            ['iso' => 'CY', 'name' => 'Cyprus', 'dial' => '+357'],
            ['iso' => 'CZ', 'name' => 'Czechia', 'dial' => '+420'],
            ['iso' => 'DK', 'name' => 'Denmark', 'dial' => '+45'],
            ['iso' => 'EE', 'name' => 'Estonia', 'dial' => '+372'],
            ['iso' => 'FI', 'name' => 'Finland', 'dial' => '+358'],
            ['iso' => 'FR', 'name' => 'France', 'dial' => '+33'],
            ['iso' => 'GE', 'name' => 'Georgia', 'dial' => '+995'],
            ['iso' => 'DE', 'name' => 'Germany', 'dial' => '+49'],
            ['iso' => 'GR', 'name' => 'Greece', 'dial' => '+30'],
            ['iso' => 'HU', 'name' => 'Hungary', 'dial' => '+36'],
            ['iso' => 'IE', 'name' => 'Ireland', 'dial' => '+353'],
            ['iso' => 'IL', 'name' => 'Israel', 'dial' => '+972'],
            ['iso' => 'IT', 'name' => 'Italy', 'dial' => '+39'],
            ['iso' => 'KZ', 'name' => 'Kazakhstan', 'dial' => '+7'],
            ['iso' => 'LV', 'name' => 'Latvia', 'dial' => '+371'],
            ['iso' => 'LT', 'name' => 'Lithuania', 'dial' => '+370'],
            ['iso' => 'LU', 'name' => 'Luxembourg', 'dial' => '+352'],
            ['iso' => 'MD', 'name' => 'Moldova', 'dial' => '+373'],
            ['iso' => 'NL', 'name' => 'Netherlands', 'dial' => '+31'],
            ['iso' => 'NO', 'name' => 'Norway', 'dial' => '+47'],
            ['iso' => 'PL', 'name' => 'Poland', 'dial' => '+48'],
            ['iso' => 'PT', 'name' => 'Portugal', 'dial' => '+351'],
            ['iso' => 'RO', 'name' => 'Romania', 'dial' => '+40'],
            ['iso' => 'RU', 'name' => 'Russia', 'dial' => '+7'],
            ['iso' => 'SK', 'name' => 'Slovakia', 'dial' => '+421'],
            ['iso' => 'SI', 'name' => 'Slovenia', 'dial' => '+386'],
            ['iso' => 'ES', 'name' => 'Spain', 'dial' => '+34'],
            ['iso' => 'SE', 'name' => 'Sweden', 'dial' => '+46'],
            ['iso' => 'CH', 'name' => 'Switzerland', 'dial' => '+41'],
            ['iso' => 'TR', 'name' => 'Turkey', 'dial' => '+90'],
            ['iso' => 'UA', 'name' => 'Ukraine', 'dial' => '+380'],
            ['iso' => 'AE', 'name' => 'United Arab Emirates', 'dial' => '+971'],
            ['iso' => 'GB', 'name' => 'United Kingdom', 'dial' => '+44'],
            ['iso' => 'US', 'name' => 'United States', 'dial' => '+1'],
            ['iso' => 'UZ', 'name' => 'Uzbekistan', 'dial' => '+998'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function isos(): array
    {
        return array_column(self::all(), 'iso');
    }

    public static function isValidIso(?string $iso): bool
    {
        if ($iso === null || $iso === '') {
            return false;
        }

        return in_array(strtoupper($iso), self::isos(), true);
    }

    public static function dialFor(?string $iso): ?string
    {
        if (! self::isValidIso($iso)) {
            return null;
        }

        $iso = strtoupper((string) $iso);

        foreach (self::all() as $country) {
            if ($country['iso'] === $iso) {
                return $country['dial'];
            }
        }

        return null;
    }

    public static function flag(string $iso): string
    {
        $iso = strtoupper($iso);

        if (strlen($iso) !== 2) {
            return '';
        }

        $flag = '';

        foreach (str_split($iso) as $char) {
            $flag .= mb_chr(0x1F1E6 + ord($char) - ord('A'));
        }

        return $flag;
    }

    public static function optionLabel(array $country): string
    {
        return trim(self::flag($country['iso']).' '.$country['name'].' ('.$country['dial'].')');
    }

    public static function compose(?string $iso, ?string $national): ?string
    {
        $dial = self::dialFor($iso);
        $digits = preg_replace('/\D+/', '', (string) $national) ?? '';

        if ($dial === null || $digits === '') {
            return null;
        }

        // Drop a single leading trunk zero (e.g. 026161034 → 26161034).
        $digits = ltrim($digits, '0') ?: $digits;

        return $dial.$digits;
    }
}
