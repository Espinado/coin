<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\ReferralAccrual;
use App\Models\ReferralProfile;
use App\Models\RewardPeriodTotal;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoinDemoSeeder extends Seeder
{
    public function run(): void
    {
        $plans = $this->seedPlans();

        $user = User::query()->updateOrCreate(
            ['email' => 'test@test.lv'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test1234'),
                'email_verified_at' => now(),
                'account_slug' => '8f21',
                'epoch_label' => '20 914 · 02:14:38',
                'active_tflops' => 1200,
                'nodes_label' => '12 nodes · FRA-02, IAD-01',
                'expected_daily_reward' => 5.04,
                'avg_epoch_label' => '1.68',
                'availability_label' => '99.98%',
                'load_label' => '90.2%',
                'next_expiry_label' => 'Dec 4',
            ]
        );

        Wallet::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 1482.60,
                'available' => 1362.60,
                'pending' => 120.00,
                'usd_estimate_label' => '≈ $2,964 (placeholder)',
                'payout_address' => '0x7c4b…9a4f',
                'pending_note' => 'Credits in next epoch · 02:14:38',
                'network_label' => 'Network-placeholder',
                'min_withdrawal_label' => '10.00',
            ]
        );

        ReferralProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => 'COIN-4X9K2',
                'invited_count' => 28,
                'active_contracts' => 19,
                'total_rewards' => 112.40,
                'level1_percent' => 5,
                'level2_percent' => 2,
                'level1_users' => 18,
                'level2_users' => 10,
            ]
        );

        ReferralAccrual::query()->where('user_id', $user->id)->delete();

        foreach ([
            ['user·4a71', 'Level 1', 'Core', '+2.40', 1],
            ['user·9c02', 'Level 1', 'Cluster', '+8.10', 2],
            ['user·1f88', 'Level 2', 'Node', '+0.50', 3],
            ['user·6b30', 'Level 1', 'Core', '+2.40', 4],
            ['user·2e54', 'Level 2', 'Node', '+0.50', 5],
        ] as [$userLabel, $level, $planName, $amount, $order]) {
            ReferralAccrual::query()->create([
                'user_id' => $user->id,
                'user_label' => $userLabel,
                'level_label' => $level,
                'plan_name' => $planName,
                'amount_label' => $amount,
                'sort_order' => $order,
            ]);
        }

        foreach ([
            ['day', 'PER DAY', '5.04'],
            ['week', 'PER WEEK', '35.28'],
            ['month', 'PER MONTH', '151.20'],
        ] as [$key, $label, $total]) {
            RewardPeriodTotal::query()->updateOrCreate(
                ['user_id' => $user->id, 'period_key' => $key],
                ['period_label' => $label, 'total_label' => $total]
            );
        }

        $core = $plans['core'];
        $node = $plans['node'];

        Contract::query()->updateOrCreate(
            ['code' => 'CTR-20914-A'],
            [
                'user_id' => $user->id,
                'plan_id' => $core->id,
                'status' => 'active',
                'tflops' => 1200,
                'duration_days' => 180,
                'days_elapsed' => 83,
                'accrued_amount' => 1214.80,
                'progress_percent' => 46,
                'started_label' => '8 Jun 2026',
                'ends_label' => 'Dec 4 2026',
                'location_label' => 'FRA-02, IAD-01',
            ]
        );

        Contract::query()->updateOrCreate(
            ['code' => 'CTR-20802-B'],
            [
                'user_id' => $user->id,
                'plan_id' => $node->id,
                'status' => 'active',
                'tflops' => 250,
                'duration_days' => 90,
                'days_elapsed' => 65,
                'accrued_amount' => 155.40,
                'progress_percent' => 72,
                'started_label' => '2 Apr 2026',
                'ends_label' => '30 Sep 2026',
                'location_label' => 'SIN-03',
            ]
        );

        Contract::query()->updateOrCreate(
            ['code' => 'CTR-19640-C'],
            [
                'user_id' => $user->id,
                'plan_id' => $node->id,
                'status' => 'completed',
                'tflops' => 250,
                'duration_days' => 90,
                'days_elapsed' => 90,
                'accrued_amount' => 148.60,
                'progress_percent' => 100,
                'completed_summary' => 'CTR-19640-C · 250 TFLOPS · completed 12 Mar 2026 · accrued 148.60',
            ]
        );

        WalletTransaction::query()->where('user_id', $user->id)->delete();

        foreach ([
            ['09:12', 'Reward credit', 'Inference · FRA-02', '+1.71', 'positive', 'ACCRUED', 1],
            ['01:12', 'Reward credit', 'Training · IAD-01', '+1.68', 'positive', 'ACCRUED', 2],
            ['Sep 7', 'Referral credit', 'Level 1 · 2 contracts', '+4.20', 'positive', 'ACCRUED', 3],
            ['Sep 6', 'Withdrawal', '0x7c4b…9a4f', '−120.00', 'neutral', 'COMPLETED', 4],
            ['Sep 5', 'Plan purchase', 'Node · 250 TFLOPS', '−250.00', 'neutral', 'COMPLETED', 5],
            ['Sep 4', 'Deposit', '0x7c4b…9a4f', '+500.00', 'neutral', 'PENDING', 6],
        ] as [$time, $type, $source, $amount, $tone, $status, $order]) {
            WalletTransaction::query()->create([
                'user_id' => $user->id,
                'occurred_label' => $time,
                'type' => $type,
                'source' => $source,
                'amount_label' => $amount,
                'amount_tone' => $tone,
                'status_label' => $status,
                'sort_order' => $order,
            ]);
        }
    }

    /** @return array<string, Plan> */
    private function seedPlans(): array
    {
        $definitions = [
            'node' => [
                'slug' => 'node',
                'name' => 'Node',
                'tier_label' => 'START',
                'price_label' => '$250',
                'tflops' => 250,
                'duration_days' => 90,
                'infra' => 'Shared pool',
                'reward_multiplier' => 0.86,
                'daily_estimate' => 0.9,
                'max_tflops' => 600,
                'sort_order' => 1,
                'is_featured' => false,
                'capacity_percent' => 12,
            ],
            'core' => [
                'slug' => 'core',
                'name' => 'Core',
                'tier_label' => 'POPULAR',
                'price_label' => '$1,100',
                'tflops' => 1200,
                'duration_days' => 180,
                'infra' => 'Priority pool',
                'reward_multiplier' => 1.00,
                'daily_estimate' => 5.0,
                'max_tflops' => 2500,
                'sort_order' => 2,
                'is_featured' => true,
                'capacity_percent' => 34,
            ],
            'cluster' => [
                'slug' => 'cluster',
                'name' => 'Cluster',
                'tier_label' => 'PRO',
                'price_label' => '$3,400',
                'tflops' => 4000,
                'duration_days' => 365,
                'infra' => 'Dedicated pods',
                'reward_multiplier' => 1.12,
                'daily_estimate' => 18.8,
                'max_tflops' => 6000,
                'sort_order' => 3,
                'is_featured' => false,
                'capacity_percent' => 62,
            ],
            'enterprise' => [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'tier_label' => 'CUSTOM',
                'price_label' => 'Custom',
                'tflops' => 10000,
                'duration_days' => null,
                'infra' => 'Reserved racks',
                'reward_multiplier' => 1.20,
                'daily_estimate' => null,
                'max_tflops' => null,
                'sort_order' => 4,
                'is_featured' => false,
                'capacity_percent' => 100,
            ],
        ];

        $plans = [];

        foreach ($definitions as $key => $data) {
            $plans[$key] = Plan::query()->updateOrCreate(['slug' => $data['slug']], $data);
        }

        return $plans;
    }
}
