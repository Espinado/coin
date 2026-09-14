<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Deposit;
use App\Models\Epoch;
use App\Models\EpochReward;
use App\Models\ReferralAccrual;
use App\Models\ReferralCommission;
use App\Models\ReferralProfile;
use App\Models\RewardPeriodTotal;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoinDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->purgeFinancialData();

        $settings = app(PlatformSettingsService::class);

        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@test.lv',
            'password' => Hash::make('test1234'),
            'email_verified_at' => now(),
            'account_slug' => '8f21',
            'epoch_label' => 'Daily accrual · 00:05 UTC',
            'active_tflops' => 0,
            'nodes_label' => '—',
            'expected_daily_reward' => 0,
            'avg_epoch_label' => '0.00',
            'availability_label' => '—',
            'load_label' => '—',
            'next_expiry_label' => '—',
            'kyc_status' => User::KYC_APPROVED,
            'is_blocked' => false,
            'phone' => '+371 2000 0001',
            'telegram' => '@testuser',
            'country_code' => 'LV',
            'last_login_at' => now(),
        ]);

        Wallet::query()->create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'balance' => 0,
            'available' => 0,
            'locked_balance' => 0,
            'pending' => 0,
            'usd_estimate_label' => '≈ $0',
            'payout_address' => '0x7c4b912a9f8833e2d1b0c8a4f',
            'pending_note' => null,
            'network_label' => 'TRC-20',
            'min_withdrawal_label' => number_format($settings->minWithdrawal(), 2, '.', ''),
        ]);

        ReferralProfile::query()->create([
            'user_id' => $user->id,
            'code' => 'COIN-4X9K2',
            'invited_count' => 0,
            'active_contracts' => 0,
            'total_rewards' => 0,
            'level1_percent' => $settings->getInt('referral_level1_percent'),
            'level2_percent' => $settings->getInt('referral_level2_percent'),
            'level1_users' => 0,
            'level2_users' => 0,
        ]);
    }

    private function purgeFinancialData(): void
    {
        SupportTicketMessage::query()->delete();
        SupportTicket::query()->delete();
        EpochReward::query()->delete();
        Epoch::query()->delete();
        WalletTransaction::query()->delete();
        Withdrawal::query()->delete();
        Deposit::query()->delete();
        ReferralCommission::query()->delete();
        ReferralAccrual::query()->delete();
        RewardPeriodTotal::query()->delete();
        Contract::query()->delete();
        ReferralProfile::query()->delete();
        Wallet::query()->delete();
        User::query()->delete();
    }
}
