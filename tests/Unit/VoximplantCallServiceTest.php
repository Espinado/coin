<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Voximplant\VoximplantApiClient;
use App\Services\Voximplant\VoximplantCallService;
use Tests\TestCase;

class VoximplantCallServiceTest extends TestCase
{
    public function test_normalizes_phone_with_country_code(): void
    {
        $service = new VoximplantCallService(new VoximplantApiClient());

        $user = new User([
            'phone' => '29123456',
            'country_code' => 'LV',
        ]);

        $this->assertSame('+37129123456', $service->normalizeDestination($user));
    }

    public function test_keeps_e164_phone(): void
    {
        $service = new VoximplantCallService(new VoximplantApiClient());

        $user = new User([
            'phone' => '+37129123456',
            'country_code' => 'LV',
        ]);

        $this->assertSame('+37129123456', $service->normalizeDestination($user));
    }
}
