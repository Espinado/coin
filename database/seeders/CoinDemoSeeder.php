<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Contract;
use App\Models\Epoch;
use App\Models\Plan;
use App\Models\ReferralAccrual;
use App\Models\ReferralProfile;
use App\Models\RewardPeriodTotal;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\EpochService;
use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoinDemoSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(PlatformSettingsService::class);
        $plans = PlanSeeder::plansBySlug();
        $admin = Admin::query()->first();

        $primary = $this->seedPrimaryUser($plans, $settings, false);
        $this->seedSecondaryUsers($plans, $settings);
        $this->seedEpochHistory($admin);
        $this->finalizePrimaryUserWallet($primary, $settings);
        $this->seedSupportTickets($admin, $primary);
        $this->seedWithdrawals($primary, $admin);
    }

    /** @param array<string, Plan> $plans */
    private function seedPrimaryUser(array $plans, PlatformSettingsService $settings, bool $withTransactions = true): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@test.lv'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test1234'),
                'email_verified_at' => now(),
                'account_slug' => '8f21',
                'epoch_label' => '20 914 · 02:14:38',
                'active_tflops' => 1450,
                'nodes_label' => '12 nodes · FRA-02, IAD-01',
                'expected_daily_reward' => 5.04,
                'avg_epoch_label' => '1.68',
                'availability_label' => '99.98%',
                'load_label' => '90.2%',
                'next_expiry_label' => 'Dec 4',
                'kyc_status' => User::KYC_APPROVED,
                'is_blocked' => false,
            ]
        );

        Wallet::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 1482.60,
                'available' => 1362.60,
                'pending' => 120.00,
                'usd_estimate_label' => '≈ $2,964',
                'payout_address' => '0x7c4b912a9f8833e2d1b0c8a4f',
                'pending_note' => 'Credits in next epoch · 02:14:38',
                'network_label' => 'ERC-20',
                'min_withdrawal_label' => number_format($settings->minWithdrawal(), 2, '.', ''),
            ]
        );

        ReferralProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => 'COIN-4X9K2',
                'invited_count' => 28,
                'active_contracts' => 19,
                'total_rewards' => 112.40,
                'level1_percent' => $settings->getInt('referral_level1_percent'),
                'level2_percent' => $settings->getInt('referral_level2_percent'),
                'level1_users' => 18,
                'level2_users' => 10,
            ]
        );

        ReferralAccrual::query()->where('user_id', $user->id)->delete();
        foreach ([
            ['user·4a71', 'Level 1', 'Core', '+2.40 COIN', 1],
            ['user·9c02', 'Level 1', 'Cluster', '+8.10 COIN', 2],
            ['user·1f88', 'Level 2', 'Node', '+0.50 COIN', 3],
            ['user·6b30', 'Level 1', 'Core', '+2.40 COIN', 4],
            ['user·2e54', 'Level 2', 'Node', '+0.50 COIN', 5],
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

        if ($withTransactions) {
            $this->seedPrimaryWalletTransactions($user);
        }

        return $user;
    }

    private function finalizePrimaryUserWallet(User $primary, PlatformSettingsService $settings): void
    {
        Wallet::query()->where('user_id', $primary->id)->update([
            'balance' => 1482.60,
            'available' => 1362.60,
            'pending' => 120.00,
            'usd_estimate_label' => '≈ $2,964',
            'pending_note' => 'Credits in next epoch · 02:14:38',
            'min_withdrawal_label' => number_format($settings->minWithdrawal(), 2, '.', ''),
        ]);

        $primary->update([
            'epoch_label' => '20 914 · 02:14:38',
            'expected_daily_reward' => 5.04,
            'avg_epoch_label' => '1.68',
        ]);

        $this->seedPrimaryWalletTransactions($primary);
    }

    private function seedPrimaryWalletTransactions(User $user): void
    {
        WalletTransaction::query()->where('user_id', $user->id)->delete();

        foreach ([
            ['09:12', 'Reward credit', 'Epoch 20914', '+1.71 COIN', 'positive', 'COMPLETED', 1],
            ['01:12', 'Reward credit', 'Epoch 20913', '+1.68 COIN', 'positive', 'COMPLETED', 2],
            ['Sep 7', 'Referral credit', 'Level 1 · 2 contracts', '+4.20 COIN', 'positive', 'COMPLETED', 3],
            ['Sep 6', 'Withdrawal', 'WD-PAID901', '−119.50 COIN', 'neutral', 'COMPLETED', 4],
            ['Sep 5', 'Plan purchase', 'Node · 250 TFLOPS', '−250.00 COIN', 'neutral', 'COMPLETED', 5],
            ['Sep 4', 'Deposit', '0x7c4b912a…', '+500.00 COIN', 'positive', 'PENDING', 6],
            ['Sep 3', 'Reward credit', 'Epoch 20912', '+1.65 COIN', 'positive', 'COMPLETED', 7],
            ['Sep 2', 'Reward credit', 'Epoch 20911', '+1.62 COIN', 'positive', 'COMPLETED', 8],
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

    /** @param array<string, Plan> $plans */
    private function seedSecondaryUsers(array $plans, PlatformSettingsService $settings): void
    {
        $users = [
            [
                'email' => 'maria@coin.local',
                'name' => 'Maria Ozola',
                'slug' => 'a4c2',
                'kyc' => User::KYC_PENDING,
                'blocked' => false,
                'tflops' => 250,
                'plan' => 'node',
                'contract_code' => 'CTR-30101-M',
            ],
            [
                'email' => 'blocked@coin.local',
                'name' => 'Blocked Account',
                'slug' => 'b001',
                'kyc' => User::KYC_REJECTED,
                'blocked' => true,
                'tflops' => 0,
                'plan' => null,
                'contract_code' => null,
            ],
            [
                'email' => 'investor@coin.local',
                'name' => 'Investor One',
                'slug' => 'c7d9',
                'kyc' => User::KYC_APPROVED,
                'blocked' => false,
                'tflops' => 4000,
                'plan' => 'cluster',
                'contract_code' => 'CTR-40001-I',
            ],
            [
                'email' => 'referral@coin.local',
                'name' => 'Referral Leaf',
                'slug' => 'd2e8',
                'kyc' => User::KYC_NONE,
                'blocked' => false,
                'tflops' => 250,
                'plan' => 'node',
                'contract_code' => 'CTR-50001-R',
            ],
        ];

        foreach ($users as $index => $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make('test1234'),
                    'email_verified_at' => now()->subDays(20 - $index),
                    'account_slug' => $definition['slug'],
                    'epoch_label' => '20 914 · 01:48:12',
                    'active_tflops' => $definition['tflops'],
                    'nodes_label' => $definition['tflops'] > 0 ? '4 nodes · FRA-02' : '—',
                    'expected_daily_reward' => $definition['tflops'] > 0 ? round($definition['tflops'] * 0.0042 * 3, 2) : 0,
                    'avg_epoch_label' => $definition['tflops'] > 0 ? '0.35' : '0.00',
                    'availability_label' => '99.90%',
                    'load_label' => '84.0%',
                    'next_expiry_label' => 'Nov 18',
                    'kyc_status' => $definition['kyc'],
                    'is_blocked' => $definition['blocked'],
                ]
            );

            Wallet::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 200 + ($index * 150),
                    'available' => 180 + ($index * 140),
                    'pending' => 20,
                    'usd_estimate_label' => '≈ $'.number_format((400 + $index * 300), 0),
                    'payout_address' => '0x'.substr(md5($definition['email']), 0, 8).'…'.substr(md5($definition['slug']), 0, 4),
                    'pending_note' => 'Awaiting next epoch',
                    'network_label' => 'ERC-20',
                    'min_withdrawal_label' => number_format($settings->minWithdrawal(), 2, '.', ''),
                ]
            );

            if ($definition['plan']) {
                Contract::query()->updateOrCreate(
                    ['code' => $definition['contract_code']],
                    [
                        'user_id' => $user->id,
                        'plan_id' => $plans[$definition['plan']]->id,
                        'status' => 'active',
                        'tflops' => $plans[$definition['plan']]->tflops,
                        'duration_days' => $plans[$definition['plan']]->duration_days ?? 180,
                        'days_elapsed' => 40 + $index,
                        'accrued_amount' => 80 + ($index * 25),
                        'progress_percent' => min(95, 30 + ($index * 15)),
                        'started_label' => '15 Jul 2026',
                        'ends_label' => 'Jan 2027',
                        'location_label' => 'FRA-02',
                    ]
                );
            }
        }
    }

    private function seedSupportTickets(?Admin $admin, User $primary): void
    {
        SupportTicketMessage::query()->delete();
        SupportTicket::query()->delete();

        $maria = User::query()->where('email', 'maria@coin.local')->first();

        $tickets = [
            [
                'user_id' => $primary->id,
                'reference' => 'TKT-DEMO01',
                'subject' => 'Withdrawal pending longer than expected',
                'category' => SupportTicket::CATEGORY_WITHDRAWAL,
                'status' => SupportTicket::STATUS_PENDING,
                'messages' => [
                    ['user', 'I requested a withdrawal 2 days ago and it is still pending settlement. Can you check status?'],
                    ['admin', 'Thanks for reaching out. Your payout is queued for the next settlement window.'],
                ],
            ],
            [
                'user_id' => $maria?->id ?? $primary->id,
                'reference' => 'TKT-OPEN02',
                'subject' => 'Contract activation delay',
                'category' => SupportTicket::CATEGORY_CONTRACT,
                'status' => SupportTicket::STATUS_OPEN,
                'messages' => [
                    ['user', 'My Node contract shows active but rewards have not started yet.'],
                ],
            ],
            [
                'user_id' => $primary->id,
                'reference' => 'TKT-CLOSED03',
                'subject' => 'KYC document resubmission',
                'category' => SupportTicket::CATEGORY_KYC,
                'status' => SupportTicket::STATUS_CLOSED,
                'messages' => [
                    ['user', 'I uploaded a new passport scan for verification.'],
                    ['admin', 'Documents approved. You can request withdrawals now.'],
                ],
            ],
        ];

        foreach ($tickets as $data) {
            $ticket = SupportTicket::query()->create([
                'user_id' => $data['user_id'],
                'assigned_admin_id' => $admin?->id,
                'reference' => $data['reference'],
                'subject' => $data['subject'],
                'category' => $data['category'],
                'status' => $data['status'],
                'last_reply_at' => now(),
            ]);

            foreach ($data['messages'] as [$author, $body]) {
                SupportTicketMessage::query()->create([
                    'support_ticket_id' => $ticket->id,
                    'author_type' => $author === 'admin' ? SupportTicketMessage::AUTHOR_ADMIN : SupportTicketMessage::AUTHOR_USER,
                    'author_id' => $author === 'admin' ? ($admin?->id ?? 1) : $data['user_id'],
                    'body' => $body,
                ]);
            }
        }
    }

    private function seedWithdrawals(User $primary, ?Admin $admin): void
    {
        Withdrawal::query()->delete();

        $rows = [
            ['WD-DEMO120', $primary->id, 120.00, Withdrawal::STATUS_PENDING, null],
            ['WD-PAID901', $primary->id, 119.50, Withdrawal::STATUS_PAID, now()->subDays(2)],
            ['WD-APPR330', User::query()->where('email', 'referral@coin.local')->value('id'), 88.00, Withdrawal::STATUS_APPROVED, null],
            ['WD-PROC220', User::query()->where('email', 'investor@coin.local')->value('id'), 450.00, Withdrawal::STATUS_PROCESSING, null],
            ['WD-REJ014', User::query()->where('email', 'maria@coin.local')->value('id'), 55.00, Withdrawal::STATUS_REJECTED, now()->subDay()],
        ];

        foreach ($rows as [$ref, $userId, $amount, $status, $processedAt]) {
            if (! $userId) {
                continue;
            }

            Withdrawal::query()->create([
                'reference' => $ref,
                'user_id' => $userId,
                'amount' => $amount,
                'payout_address' => '0x7c4b912a9f8833e2d1b0c8a4f',
                'network_label' => 'ERC-20',
                'status' => $status,
                'processed_by' => in_array($status, [Withdrawal::STATUS_PAID, Withdrawal::STATUS_REJECTED], true) ? $admin?->id : null,
                'admin_note' => $status === Withdrawal::STATUS_REJECTED ? 'KYC pending — payout rejected.' : null,
                'processed_at' => $processedAt,
            ]);
        }
    }

    private function seedEpochHistory(?Admin $admin): void
    {
        Epoch::query()->delete();

        $service = app(EpochService::class);

        for ($i = 0; $i < 5; $i++) {
            $service->runSettlement($admin);
        }

        $epochs = Epoch::query()->orderBy('id')->get();
        foreach ($epochs as $index => $epoch) {
            $epoch->update(['number' => 20910 + $index]);
        }
    }

}
