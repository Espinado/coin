<?php

use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$profitAccrualTime = (string) config('coin.profit_accrual.schedule_time', '09:00');
$profitAccrualTimezone = (string) config('coin.profit_accrual.schedule_timezone', 'Europe/Riga');

if (Schema::hasTable('platform_settings')) {
    $settings = app(PlatformSettingsService::class);
    $profitAccrualTime = $settings->profitAccrualTime();
    $profitAccrualTimezone = $settings->profitAccrualTimezone();
}

Schedule::command('coin:accrue-daily-profits')
    ->dailyAt($profitAccrualTime)
    ->timezone($profitAccrualTimezone)
    ->withoutOverlapping();

if (config('coin.exchange_rates.coinmarketcap.enabled')) {
    Schedule::command('coin:refresh-btc-rate')
        ->hourly()
        ->withoutOverlapping();
}
