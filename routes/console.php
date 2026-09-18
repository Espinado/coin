<?php

use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('coin:accrue-daily-profits')
    ->dailyAt(app(PlatformSettingsService::class)->profitAccrualTime())
    ->timezone(app(PlatformSettingsService::class)->profitAccrualTimezone())
    ->withoutOverlapping();
