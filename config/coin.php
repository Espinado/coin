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

    'brand' => [
        'name' => env('COIN_BRAND_NAME', 'CloudFlops'),
        'admin_name' => env('COIN_ADMIN_BRAND_NAME', 'CloudFlops Admin'),
        'legal_name' => env('COIN_LEGAL_NAME', 'CloudFlops SIA'),
        'logos' => [
            'horizontal' => 'cloudflops/logo-horizontal.png',
            'mark' => 'cloudflops/logo-mark.png',
            'stacked' => 'cloudflops/logo-stacked.png',
        ],
    ],

    'contact_email' => env('COIN_CONTACT_EMAIL', 'rvr@arguss.lv'),

    'wallet' => [
        'base_currency' => env('COIN_WALLET_BASE_CURRENCY', 'USDT'),
        'payout_network' => env('COIN_WALLET_PAYOUT_NETWORK', 'TRC-20'),
        'btc_payout_network' => env('COIN_WALLET_BTC_PAYOUT_NETWORK', 'Bitcoin'),
    ],

    'deposits' => [
        'auto_confirm_mock' => env('COIN_DEPOSITS_AUTO_CONFIRM_MOCK', false),
        'currencies' => ['USDT', 'BTC'],
    ],

    'withdrawals' => [
        'currencies' => ['USDT', 'BTC'],
    ],

    'exchange_rates' => [
        'coinmarketcap' => [
            'enabled' => filled(env('CMC_API_KEY')),
            'api_key' => env('CMC_API_KEY'),
            'base_url' => env('CMC_API_BASE_URL', 'https://pro-api.coinmarketcap.com'),
            'refresh_minutes' => max(60, (int) env('CMC_RATE_REFRESH_MINUTES', 60)),
        ],
    ],

    'payments' => [
        'driver' => env('COIN_PAYMENT_DRIVER', 'mock'),

        'mock' => [
            'auto_complete_payout' => env('COIN_MOCK_AUTO_COMPLETE_PAYOUT', true),
        ],

        'ccapi' => [
            'base_url' => env('CCAPI_BASE_URL', 'https://new.cryptocurrencyapi.net'),
            'api_key' => env('CCAPI_API_KEY', ''),
            'networks' => [
                'USDT' => ['network' => 'trx', 'token' => 'USDT'],
                'BTC' => ['network' => 'btc', 'token' => ''],
            ],
            'deposit_period_minutes' => (int) env('CCAPI_DEPOSIT_PERIOD_MINUTES', 60),
            'forward_to' => env('CCAPI_FORWARD_ADDRESS'),
            'forward_from' => env('CCAPI_FORWARD_FROM'),
            'ipn_url' => env('CCAPI_IPN_URL', env('APP_URL').'/webhooks/ccapi'),
            'min_confirmations' => (int) env('CCAPI_MIN_CONFIRMATIONS', 1),
            // IPN amount must match deposit.amount within this tolerance (0 = exact).
            'amount_tolerance' => (float) env('CCAPI_AMOUNT_TOLERANCE', 0),
            // Comma-separated CCAPI IPN source IPs (production default applied in middleware when empty).
            'webhook_ips' => array_values(array_filter(array_map(
                trim(...),
                explode(',', (string) env('CCAPI_WEBHOOK_IPS', '')),
            ))),
        ],
    ],

    'profit_accrual' => [
        'schedule_time' => env('COIN_PROFIT_ACCRUAL_TIME', '09:00'),
        'schedule_timezone' => env('COIN_PROFIT_ACCRUAL_TZ', 'Europe/Riga'),
    ],

    'admin' => [
        'invitation_ttl_hours' => (int) env('COIN_ADMIN_INVITATION_TTL_HOURS', 72),
    ],

    'session' => [
        'idle_minutes' => max(1, (int) env('COIN_SESSION_IDLE_MINUTES', 15)),
    ],

];
