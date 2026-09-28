<?php

namespace Tests\Unit;

use App\Support\PhoneCountries;
use Tests\TestCase;

class PhoneCountriesTest extends TestCase
{
    public function test_compose_builds_international_number(): void
    {
        $this->assertSame('+37126161034', PhoneCountries::compose('LV', '26161034'));
        $this->assertSame('+37126161034', PhoneCountries::compose('lv', '026161034'));
        $this->assertSame('+79001234567', PhoneCountries::compose('RU', '9001234567'));
    }

    public function test_option_label_includes_name_and_dial(): void
    {
        $latvia = collect(PhoneCountries::all())->firstWhere('iso', 'LV');

        $this->assertNotNull($latvia);
        $label = PhoneCountries::optionLabel($latvia);

        $this->assertStringContainsString('Latvia', $label);
        $this->assertStringContainsString('+371', $label);
    }

    public function test_flag_url_uses_flagcdn(): void
    {
        $this->assertSame('https://flagcdn.com/w40/lv.png', PhoneCountries::flagUrl('LV'));
    }

    public function test_national_includes_country_code_detection(): void
    {
        $this->assertTrue(PhoneCountries::nationalIncludesCountryCode('LV', '+37126161034'));
        $this->assertTrue(PhoneCountries::nationalIncludesCountryCode('LV', '37126161034'));
        $this->assertTrue(PhoneCountries::nationalIncludesCountryCode('LV', '0037126161034'));
        $this->assertTrue(PhoneCountries::nationalIncludesCountryCode('RU', '79001234567'));
        $this->assertFalse(PhoneCountries::nationalIncludesCountryCode('LV', '26161034'));
        $this->assertFalse(PhoneCountries::nationalIncludesCountryCode('RU', '9001234567'));
    }
}
