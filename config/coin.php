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
    ],

];
