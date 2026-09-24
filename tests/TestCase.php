<?php

namespace Tests;

use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        if (! in_array(RefreshDatabase::class, class_uses_recursive(static::class), true)) {
            return;
        }

        app(PlatformSettingsService::class)->setMany([
            'payment_gate_enabled' => true,
        ]);
    }
}
