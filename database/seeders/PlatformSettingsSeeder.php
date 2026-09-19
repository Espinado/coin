<?php

namespace Database\Seeders;

use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(PlatformSettingsService::class)->setMany([
            'reward_rate' => '0.0042',
            'epochs_per_day' => '3',
            'token_symbol' => 'USDT',
            'min_deposit' => '10.00',
            'min_withdrawal' => '10.00',
            'network_fee' => '0.50',
            'withdrawal_processing_hours' => '24',
            'referral_level1_percent' => '20',
            'referral_level2_percent' => '0',
            'kyc_required_for_withdrawal' => '1',
            'maintenance_mode' => '0',
            'btc_per_usdt' => '2',
        ]);
    }
}
