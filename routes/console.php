<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('coin:accrue-daily-profits')
    ->dailyAt(config('coin.profit_accrual.schedule_time', '09:00'))
    ->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))
    ->withoutOverlapping();
