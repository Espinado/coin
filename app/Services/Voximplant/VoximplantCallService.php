<?php

namespace App\Services\Voximplant;

use App\Models\User;

class VoximplantCallService
{
    public function __construct(
        private readonly VoximplantApiClient $client,
    ) {}

    public function isReady(): bool
    {
        return config('voximplant.enabled')
            && $this->client->isConfigured()
            && filled(config('voximplant.rule_id'))
            && filled(config('voximplant.sdk_user'))
            && filled(config('voximplant.sdk_password'));
    }

    public function sdkUsername(): string
    {
        $user = (string) config('voximplant.sdk_user');
        $application = (string) config('voximplant.application_name');
        $account = (string) config('voximplant.account_name');

        return "{$user}@{$application}.{$account}.voximplant.com";
    }

    public function oneTimeLoginHash(string $loginKey): string
    {
        $user = (string) config('voximplant.sdk_user');
        $password = (string) config('voximplant.sdk_password');

        return md5($loginKey.'|'.md5("{$user}:voximplant.com:{$password}"));
    }

    /**
     * @return array<string, mixed>
     */
    public function startOutboundCall(User $user): array
    {
        $destination = $this->normalizeDestination($user);

        if ($destination === null) {
            throw new VoximplantException(__('coin.voximplant.no_phone'));
        }

        return $this->client->call('StartScenarios', [
            'rule_id' => (int) config('voximplant.rule_id'),
            'application_id' => config('voximplant.application_id'),
            'script_custom_data' => json_encode([
                'destination' => $destination,
                'caller_id' => (string) config('voximplant.caller_id', ''),
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    public function normalizeDestination(User $user): ?string
    {
        $phone = trim((string) $user->phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '+')) {
            $digits = preg_replace('/\D+/', '', $phone) ?? '';

            return $digits !== '' ? '+'.$digits : null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $country = strtoupper(trim((string) $user->country_code));

        if ($country !== '' && ! str_starts_with($digits, $this->countryDialCode($country))) {
            return '+'.$this->countryDialCode($country).ltrim($digits, '0');
        }

        return '+'.$digits;
    }

    private function countryDialCode(string $countryCode): string
    {
        return match ($countryCode) {
            'LV' => '371',
            'RU' => '7',
            'EE' => '372',
            'LT' => '370',
            'UA' => '380',
            'BY' => '375',
            'KZ' => '7',
            default => '',
        };
    }
}
