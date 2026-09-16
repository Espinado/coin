<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ReferralCommission;
use App\Models\ReferralProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralCommissionService
{
    public function __construct(
        private PlatformSettingsService $settings,
        private WalletService $wallets,
        private ReferralService $referrals,
        private UserNotificationService $notifications,
    ) {}

    public function onContractPurchased(Contract $contract): ?ReferralCommission
    {
        $contract->loadMissing('user');

        $buyer = $contract->user;

        if (! $buyer instanceof User || ! $buyer->referred_by_user_id) {
            return null;
        }

        if (ReferralCommission::query()->where('contract_id', $contract->id)->exists()) {
            return ReferralCommission::query()->where('contract_id', $contract->id)->first();
        }

        return DB::transaction(function () use ($contract, $buyer) {
            $referrer = User::query()->find($buyer->referred_by_user_id);

            if (! $referrer instanceof User) {
                return null;
            }

            $purchaseAmount = (float) ($contract->principal_amount ?? 0);

            if ($purchaseAmount <= 0) {
                return null;
            }

            $profile = $this->referrals->ensureReferralProfile($referrer);
            $percent = (float) ($profile->level1_percent ?: $this->settings->getFloat('referral_level1_percent'));
            $commissionAmount = round($purchaseAmount * ($percent / 100), 2);

            if ($commissionAmount <= 0) {
                return null;
            }

            $wallet = $this->wallets->ensureWallet($referrer);
            $currency = $contract->currency ?: $this->wallets->currencyFor($wallet);

            $wallet->increment('available', $commissionAmount);
            $wallet->increment('balance', $commissionAmount);

            $commission = ReferralCommission::query()->create([
                'referrer_user_id' => $referrer->id,
                'referral_user_id' => $buyer->id,
                'contract_id' => $contract->id,
                'purchase_amount' => $purchaseAmount,
                'commission_percent' => $percent,
                'commission_amount' => $commissionAmount,
                'currency' => $currency,
            ]);

            ReferralProfile::query()
                ->whereKey($profile->id)
                ->increment('total_rewards', $commissionAmount);

            $this->wallets->record(
                $referrer,
                'Referral credit',
                $buyer->accountLabel(),
                $commissionAmount,
                $currency,
                'positive',
                'COMPLETED',
                $commission,
            );

            $this->notifications->notifyReferralCommission($referrer, $buyer, $commissionAmount, $currency);

            return $commission;
        });
    }

    public function onContractUpgradeTopUp(Contract $contract, float $topUpAmount): ?ReferralCommission
    {
        if ($topUpAmount <= 0.009) {
            return null;
        }

        $contract->loadMissing('user');
        $buyer = $contract->user;

        if (! $buyer instanceof User || ! $buyer->referred_by_user_id) {
            return null;
        }

        return DB::transaction(function () use ($contract, $buyer, $topUpAmount) {
            $referrer = User::query()->find($buyer->referred_by_user_id);

            if (! $referrer instanceof User) {
                return null;
            }

            $profile = $this->referrals->ensureReferralProfile($referrer);
            $percent = (float) ($profile->level1_percent ?: $this->settings->getFloat('referral_level1_percent'));
            $commissionAmount = round($topUpAmount * ($percent / 100), 2);

            if ($commissionAmount <= 0) {
                return null;
            }

            $wallet = $this->wallets->ensureWallet($referrer);
            $currency = $contract->currency ?: $this->wallets->currencyFor($wallet);

            $wallet->increment('available', $commissionAmount);
            $wallet->increment('balance', $commissionAmount);

            $commission = ReferralCommission::query()->where('contract_id', $contract->id)->first();

            if ($commission) {
                $commission->update([
                    'purchase_amount' => round((float) $commission->purchase_amount + $topUpAmount, 2),
                    'commission_amount' => round((float) $commission->commission_amount + $commissionAmount, 2),
                ]);
            } else {
                $commission = ReferralCommission::query()->create([
                    'referrer_user_id' => $referrer->id,
                    'referral_user_id' => $buyer->id,
                    'contract_id' => $contract->id,
                    'purchase_amount' => $topUpAmount,
                    'commission_percent' => $percent,
                    'commission_amount' => $commissionAmount,
                    'currency' => $currency,
                ]);
            }

            ReferralProfile::query()
                ->whereKey($profile->id)
                ->increment('total_rewards', $commissionAmount);

            $this->wallets->record(
                $referrer,
                'Referral credit',
                $buyer->accountLabel(),
                $commissionAmount,
                $currency,
                'positive',
                'COMPLETED',
                $commission,
            );

            $this->notifications->notifyReferralCommission($referrer, $buyer, $commissionAmount, $currency);

            return $commission;
        });
    }
}
