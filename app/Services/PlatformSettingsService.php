<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class PlatformSettingsService
{
    private const CACHE_KEY = 'coin.platform_settings';

    /** @var array<string, string> */
    private const DEFAULTS = [
        'reward_rate' => '0.0042',
        'epochs_per_day' => '3',
        'token_symbol' => 'USDT',
        'min_deposit' => '10.00',
        'min_withdrawal' => '10.00',
        'network_fee' => '0.50',
        'withdrawal_processing_hours' => '24',
        'referral_level1_percent' => '20',
        'referral_level2_percent' => '0',
        'kyc_required_for_withdrawal' => '0',
        'maintenance_mode' => '0',
        'btc_per_usdt' => '2',
        'usdt_per_btc' => '',
        'btc_rate_updated_at' => '',
        'btc_rate_source' => 'manual',
        'profit_accrual_time' => '09:00',
    ];

    /** @var array<string, string> */
    private const LEGAL_DEFAULTS = [
        'company_name' => '',
        'company_legal_address' => '',
        'company_physical_address' => '',
        'company_registration_number' => '',
        'company_license_number' => '',
        'company_phone' => '',
        'company_email' => '',
    ];

    public function get(string $key, ?string $default = null): string
    {
        $settings = $this->all();

        return $settings[$key] ?? $default ?? self::DEFAULTS[$key] ?? '';
    }

    public function getFloat(string $key): float
    {
        return (float) $this->get($key);
    }

    public function getInt(string $key): int
    {
        return (int) $this->get($key);
    }

    public function getBool(string $key): bool
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOL);
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $stored = PlatformSetting::query()->pluck('value', 'key')->all();

            return array_merge(self::DEFAULTS, self::LEGAL_DEFAULTS, $stored);
        });
    }

    /** @param array<string, string|int|float|bool> $values */
    public function setMany(array $values): void
    {
        $this->persistKeys($values, self::DEFAULTS);
    }

    /** @param array<string, string|null> $values */
    public function setLegalMany(array $values): void
    {
        $this->persistKeys($values, self::LEGAL_DEFAULTS);
    }

    /** @return array<string, string> */
    public function legalInfo(): array
    {
        return array_intersect_key($this->all(), self::LEGAL_DEFAULTS);
    }

    /**
     * @param  array<string, string|int|float|bool|null>  $values
     * @param  array<string, string>  $allowed
     */
    private function persistKeys(array $values, array $allowed): void
    {
        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $allowed)) {
                continue;
            }

            PlatformSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '')],
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function rewardRate(): float
    {
        return $this->getFloat('reward_rate');
    }

    public function epochsPerDay(): int
    {
        return max(1, $this->getInt('epochs_per_day'));
    }

    public function tokenSymbol(): string
    {
        return $this->get('token_symbol');
    }

    public function minDeposit(): float
    {
        return $this->getFloat('min_deposit');
    }

    public function minWithdrawal(): float
    {
        return $this->getFloat('min_withdrawal');
    }

    public function btcPerUsdt(): float
    {
        $rate = $this->getFloat('btc_per_usdt');

        if ($rate <= 0) {
            $rate = $this->getFloat('btc_per_usd');
        }

        return $rate > 0 ? $rate : 2.0;
    }

    public function profitAccrualTime(): string
    {
        $time = trim($this->get('profit_accrual_time'));

        if ($time === '') {
            $time = (string) config('coin.profit_accrual.schedule_time', '09:00');
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return '09:00';
    }

    public function profitAccrualTimezone(): string
    {
        return (string) config('coin.profit_accrual.schedule_timezone', 'Europe/Riga');
    }

    /** @return array<string, array{label: string, type: string, default: string}> */
    public function definitions(): array
    {
        return [
            'reward_rate' => ['label' => 'Ставка награды (устар.)', 'type' => 'number', 'default' => self::DEFAULTS['reward_rate']],
            'epochs_per_day' => ['label' => 'Эпох в день (устар.)', 'type' => 'number', 'default' => self::DEFAULTS['epochs_per_day']],
            'token_symbol' => ['label' => __('coin.settings.token_symbol'), 'type' => 'text', 'default' => self::DEFAULTS['token_symbol']],
            'min_deposit' => ['label' => __('coin.settings.min_top_up'), 'type' => 'number', 'default' => self::DEFAULTS['min_deposit']],
            'min_withdrawal' => ['label' => __('coin.settings.min_payout'), 'type' => 'number', 'default' => self::DEFAULTS['min_withdrawal']],
            'network_fee' => ['label' => __('coin.settings.network_fee'), 'type' => 'number', 'default' => self::DEFAULTS['network_fee']],
            'withdrawal_processing_hours' => ['label' => __('coin.settings.payout_hours'), 'type' => 'number', 'default' => self::DEFAULTS['withdrawal_processing_hours']],
            'referral_level1_percent' => ['label' => __('coin.settings.referral_percent'), 'type' => 'number', 'default' => self::DEFAULTS['referral_level1_percent']],
            'referral_level2_percent' => ['label' => 'Реферальный % (уровень 2, не использ.)', 'type' => 'number', 'default' => self::DEFAULTS['referral_level2_percent']],
            'kyc_required_for_withdrawal' => ['label' => __('coin.settings.kyc_for_payout'), 'type' => 'boolean', 'default' => self::DEFAULTS['kyc_required_for_withdrawal']],
            'maintenance_mode' => ['label' => __('coin.settings.maintenance'), 'type' => 'boolean', 'default' => self::DEFAULTS['maintenance_mode']],
            'usdt_per_btc' => ['label' => __('coin.settings.usdt_per_btc'), 'type' => 'decimal', 'default' => self::DEFAULTS['usdt_per_btc']],
            'btc_per_usdt' => ['label' => __('coin.settings.btc_per_usdt'), 'type' => 'readonly_decimal', 'default' => self::DEFAULTS['btc_per_usdt'], 'readonly' => true],
            'profit_accrual_time' => ['label' => __('coin.settings.profit_accrual_time'), 'type' => 'time', 'default' => self::DEFAULTS['profit_accrual_time']],
        ];
    }

    /** @return array<string, array{label: string, type: string, default: string}> */
    public function adminDefinitions(): array
    {
        return array_filter(
            $this->definitions(),
            fn (string $key) => ! in_array($key, ['reward_rate', 'epochs_per_day'], true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    public static function normalizeDecimalInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return $normalized;
    }

    public static function formatBtcPerUsdtFromUsdtRate(float $usdtPerBtc): string
    {
        if ($usdtPerBtc <= 0) {
            return '';
        }

        return rtrim(rtrim(number_format(1 / $usdtPerBtc, 16, '.', ''), '0'), '.');
    }

    /** @return array<string, array{label: string, type: string, default: string}> */
    public function legalDefinitions(): array
    {
        return [
            'company_name' => ['label' => __('coin.admin.legal_info.company_name'), 'type' => 'text', 'default' => self::LEGAL_DEFAULTS['company_name']],
            'company_legal_address' => ['label' => __('coin.admin.legal_info.legal_address'), 'type' => 'textarea', 'default' => self::LEGAL_DEFAULTS['company_legal_address']],
            'company_physical_address' => ['label' => __('coin.admin.legal_info.physical_address'), 'type' => 'textarea', 'default' => self::LEGAL_DEFAULTS['company_physical_address']],
            'company_registration_number' => ['label' => __('coin.admin.legal_info.registration_number'), 'type' => 'text', 'default' => self::LEGAL_DEFAULTS['company_registration_number']],
            'company_license_number' => ['label' => __('coin.admin.legal_info.license_number'), 'type' => 'text', 'default' => self::LEGAL_DEFAULTS['company_license_number']],
            'company_phone' => ['label' => __('coin.admin.legal_info.phone'), 'type' => 'text', 'default' => self::LEGAL_DEFAULTS['company_phone']],
            'company_email' => ['label' => __('coin.admin.legal_info.email'), 'type' => 'email', 'default' => self::LEGAL_DEFAULTS['company_email']],
        ];
    }
}
