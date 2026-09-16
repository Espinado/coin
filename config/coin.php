<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Domains
    |--------------------------------------------------------------------------
    |
    | User-facing routes are only registered on user_domain.
    | Admin routes are only registered on admin_domain.
    | Keep SESSION_DOMAIN null so cookies do not leak between subdomains.
    |
    */

    'user_domain' => env('COIN_USER_DOMAIN', 'coin.test'),

    'admin_domain' => env('COIN_ADMIN_DOMAIN', 'admin.coin.test'),

    'deposits' => [
        'auto_confirm_mock' => env('COIN_DEPOSITS_AUTO_CONFIRM_MOCK', true),
        'currencies' => ['USDT', 'EUR', 'USD'],
    ],

    'profit_accrual' => [
        'schedule_time' => env('COIN_PROFIT_ACCRUAL_TIME', '09:00'),
        'schedule_timezone' => env('COIN_PROFIT_ACCRUAL_TZ', 'Europe/Riga'),
    ],

    'admin' => [
        'invitation_ttl_hours' => (int) env('COIN_ADMIN_INVITATION_TTL_HOURS', 72),
    ],

];
