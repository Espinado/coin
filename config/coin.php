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

    'display_timezone' => env('COIN_DISPLAY_TIMEZONE', 'Europe/Riga'),

    'user_domain' => env('COIN_USER_DOMAIN', 'coin.test'),

    'admin_domain' => env('COIN_ADMIN_DOMAIN', 'admin.coin.test'),

    /*
    |--------------------------------------------------------------------------
    | Public SEO / AI discoverability
    |--------------------------------------------------------------------------
    |
    | indexable_hosts: only these hosts may emit index,follow when APP_ENV=production.
    | Empty list = never index (safe default until a production domain is configured).
    |
    */
    'seo' => [
        'indexable_hosts' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('COIN_SEO_INDEXABLE_HOSTS', '')),
        ))),
    ],

    // Comma-separated proxy IPs (or *). Empty = do not trust X-Forwarded-For.
    'trusted_proxies' => array_values(array_filter(array_map(
        trim(...),
        explode(',', (string) env('COIN_TRUSTED_PROXIES', '')),
    ))),

    'brand' => [
        'name' => env('COIN_BRAND_NAME', 'CloudFlops'),
        'admin_name' => env('COIN_ADMIN_BRAND_NAME', 'CloudFlops Admin'),
        'legal_name' => env('COIN_LEGAL_NAME', 'CloudFlops LLC'),
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

        // Force live CCAPI checks even when APP_ENV=local (e.g. public staging domain).
        'require_live' => (bool) env('COIN_REQUIRE_LIVE_PAYMENTS', false),

        // Escape hatch: allow mock on a non-local hosts entry when APP_ENV is not production|staging.
        'allow_mock' => (bool) env('COIN_ALLOW_MOCK_PAYMENTS', false),

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
            'forward_btc' => env('CCAPI_FORWARD_BTC_ADDRESS'),
            'forward_btc_from' => env('CCAPI_FORWARD_BTC_FROM'),
            'ipn_url' => env('CCAPI_IPN_URL', env('APP_URL').'/webhooks/ccapi'),
            'min_confirmations' => (int) env('CCAPI_MIN_CONFIRMATIONS', 1),
            // IPN amount must match deposit.amount within this tolerance (0 = exact).
            'amount_tolerance' => (float) env('CCAPI_AMOUNT_TOLERANCE', 0),
            // Small BTC wallet rounding fallback; deposit UI still shows the exact locked amount.
            'amount_tolerance_btc' => (float) env('CCAPI_AMOUNT_TOLERANCE_BTC', 0.000005),
            // Comma-separated CCAPI IPN source IPs (production default applied in middleware when empty).
            'webhook_ips' => array_values(array_filter(array_map(
                trim(...),
                explode(',', (string) env('CCAPI_WEBHOOK_IPS', '')),
            ))),
            // Poll CCAPI .status for withdrawals stuck in processing without IPN.
            'poll_stuck_withdrawals' => (bool) env('CCAPI_POLL_STUCK_WITHDRAWALS', true),
            // Log/dashboard alert when processing payout exceeds this age without final IPN.
            'withdrawal_poll_stale_hours' => max(1, (int) env('CCAPI_WITHDRAWAL_POLL_STALE_HOURS', 24)),
            'withdrawal_poll_error_reject_count' => max(1, (int) env('CCAPI_WITHDRAWAL_POLL_ERROR_REJECT_COUNT', 30)),
            // Reject expired pending deposits and log stale ones awaiting IPN.
            'poll_stuck_deposits' => (bool) env('CCAPI_POLL_STUCK_DEPOSITS', true),
            'deposit_stale_alert_minutes' => max(1, (int) env('CCAPI_DEPOSIT_STALE_ALERT_MINUTES', 30)),
            'security_monitor' => [
                'lookback_minutes' => max(1, (int) env('CCAPI_SECURITY_MONITOR_LOOKBACK_MINUTES', 60)),
                'invalid_signature_threshold' => max(1, (int) env('CCAPI_INVALID_SIGNATURE_ALERT_THRESHOLD', 5)),
            ],
        ],
    ],

    'profit_accrual' => [
        'schedule_time' => env('COIN_PROFIT_ACCRUAL_TIME', '09:00'),
        'schedule_timezone' => env('COIN_PROFIT_ACCRUAL_TZ', 'Europe/Riga'),
    ],

    'referrals' => [
        // Soft anti-abuse: skip commission when referrer/buyer share a payout address.
        'block_shared_payout_address' => (bool) env('COIN_REFERRAL_BLOCK_SHARED_PAYOUT', true),
        // Cap total referral commissions credited to one referrer per UTC day (0 = unlimited).
        'daily_commission_cap_usdt' => (float) env('COIN_REFERRAL_DAILY_CAP_USDT', 500),
    ],

    'admin' => [
        'invitation_ttl_hours' => (int) env('COIN_ADMIN_INVITATION_TTL_HOURS', 72),
        // Map login email => real mailbox for 2FA delivery (plus-aliases often fail on shared hosting).
        // Example: viewer.admin@arguss.lv:rvr@arguss.lv
        'two_factor_email_map' => (static function (): array {
            $raw = trim((string) env('COIN_ADMIN_2FA_EMAIL_MAP', ''));
            if ($raw === '') {
                return [];
            }

            $map = [];
            foreach (explode(',', $raw) as $pair) {
                $parts = array_map(trim(...), explode(':', $pair, 2));
                if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                    $map[strtolower($parts[0])] = strtolower($parts[1]);
                }
            }

            return $map;
        })(),
    ],

    'session' => [
        'idle_minutes' => max(1, (int) env('COIN_SESSION_IDLE_MINUTES', 15)),
    ],

    'turnstile' => [
        // Fail-closed when enabled: missing secret or API failure blocks guest ticket creation.
        'enabled' => (bool) env('TURNSTILE_ENABLED', false),
        'site_key' => (string) env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => (string) env('TURNSTILE_SECRET_KEY', ''),
        'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'timeout_seconds' => 5,
    ],

];
