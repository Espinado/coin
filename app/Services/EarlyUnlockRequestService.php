<?php

namespace App\Services;

use App\Events\EarlyUnlockRequestUpdated;
use App\Models\Admin;
use App\Models\Contract;
use App\Models\EarlyUnlockRequest;
use App\Models\PlanChangeRequest;
use App\Models\PlatformCommission;
use App\Models\User;
use App\Models\Wallet;
use App\Support\MoneyFormat;
use App\Support\PlatformTerms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class EarlyUnlockRequestService
{
    public function __construct(
        private PlatformSettingsService $settings,
        private WalletService $wallets,
        private UserNotificationService $notifications,
        private PlanPurchaseService $purchases,
    ) {}

    /**
     * @return array{principal: float, fee_percent: float, fee_min: float, fee_amount: float, credit_amount: float}
     */
    public function quote(Contract $contract): array
    {
        $principal = round((float) ($contract->principal_amount ?? 0), 2);
        $feePercent = $this->settings->earlyUnlockFeePercent();
        $feeMin = $this->settings->earlyUnlockFeeMin();
        $feeAmount = $this->settings->calculateEarlyUnlockFeeAmount($principal);
        $creditAmount = round(max(0, $principal - $feeAmount), 2);

        return [
            'principal' => $principal,
            'fee_percent' => $feePercent,
            'fee_min' => $feeMin,
            'fee_amount' => $feeAmount,
            'credit_amount' => $creditAmount,
        ];
    }

    public function createRequest(User $user, Contract $contract): EarlyUnlockRequest
    {
        $this->assertCanRequest($user, $contract);

        $quote = $this->quote($contract);

        if ($quote['credit_amount'] <= 0) {
            throw new RuntimeException(__('coin.messages.early_unlock_credit_too_low'));
        }

        return DB::transaction(function () use ($user, $contract, $quote) {
            Contract::query()->whereKey($contract->id)->lockForUpdate()->firstOrFail();

            if (EarlyUnlockRequest::pendingForContract($contract->id)) {
                throw new RuntimeException(__('coin.messages.early_unlock_pending_exists'));
            }

            if (PlanChangeRequest::pendingForContract($contract->id)) {
                throw new RuntimeException(__('coin.messages.early_unlock_plan_change_pending'));
            }

            $currency = $contract->currency ?: $this->wallets->currencyFor($this->wallets->ensureWallet($user));

            return EarlyUnlockRequest::query()->create([
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'reference' => $this->generateReference(),
                'principal_amount' => $quote['principal'],
                'fee_percent' => $quote['fee_percent'],
                'fee_min' => $quote['fee_min'],
                'fee_amount' => $quote['fee_amount'],
                'credit_amount' => $quote['credit_amount'],
                'currency' => $currency,
                'status' => EarlyUnlockRequest::STATUS_PENDING,
            ])->fresh(['user', 'contract.plan']);
        });
    }

    public function approve(EarlyUnlockRequest $request, Admin $admin, ?string $note = null): EarlyUnlockRequest
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            $request = $this->lockPendingRequest($request);
            $request->load(['user', 'contract.plan']);

            $contract = $request->contract;
            $user = $request->user;

            if (! $contract instanceof Contract || ! $contract->isActive()) {
                throw new RuntimeException(__('coin.messages.early_unlock_inactive'));
            }

            if (! $user instanceof User) {
                throw new RuntimeException(__('coin.messages.early_unlock_forbidden'));
            }

            $principal = round((float) $request->principal_amount, 2);
            $feeAmount = round((float) $request->fee_amount, 2);
            $creditAmount = round((float) $request->credit_amount, 2);
            $currency = $request->currency ?: $this->wallets->currencyFor($this->wallets->ensureWallet($user));

            if ($creditAmount <= 0 || abs(($feeAmount + $creditAmount) - $principal) > 0.009) {
                throw new RuntimeException(__('coin.messages.early_unlock_invalid_amounts'));
            }

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet instanceof Wallet) {
                throw new RuntimeException('User has no wallet.');
            }

            $releaseLocked = min($principal, (float) $wallet->locked_balance);
            $wallet->decrement('locked_balance', $releaseLocked);
            $wallet->increment('available', $creditAmount);

            if ($feeAmount > 0.009) {
                $wallet->decrement('balance', min($feeAmount, (float) $wallet->balance));
            }

            $this->wallets->record(
                $user,
                PlatformTerms::TX_EARLY_UNLOCK,
                $contract->code,
                $creditAmount,
                $currency,
                'positive',
                'COMPLETED',
                $contract,
            );

            if ($feeAmount > 0.009) {
                $this->wallets->record(
                    $user,
                    PlatformTerms::TX_PLATFORM_FEE,
                    $request->reference,
                    -$feeAmount,
                    $currency,
                    'negative',
                    'COMPLETED',
                    $request,
                );

                PlatformCommission::query()->updateOrCreate(
                    [
                        'kind' => PlatformCommission::KIND_EARLY_UNLOCK,
                        'reference' => $request->reference,
                    ],
                    [
                        'user_id' => $user->id,
                        'amount' => $feeAmount,
                        'currency' => $currency,
                        'source_type' => $request->getMorphClass(),
                        'source_id' => $request->id,
                        'processed_at' => now(),
                    ],
                );
            }

            $contract->update([
                'status' => Contract::STATUS_EARLY_CLOSED,
                'progress_percent' => 100,
                'completed_summary' => __('coin.invest.early_closed_summary', [
                    'credit' => MoneyFormat::amount($creditAmount, $currency),
                    'fee' => MoneyFormat::amount($feeAmount, $currency),
                ]),
            ]);

            $request->update([
                'status' => EarlyUnlockRequest::STATUS_APPROVED,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $request->admin_note,
                'processed_at' => now(),
            ]);

            $this->purchases->refreshExpectedDailyProfit($user);

            $request = $request->fresh(['user.wallet', 'contract.plan', 'processedByAdmin']);

            DB::afterCommit(function () use ($request): void {
                event(new EarlyUnlockRequestUpdated($request));

                $user = $request->user;
                if ($user instanceof User) {
                    $this->notifications->notifyEarlyUnlockApproved($user, $request);
                }
            });

            return $request;
        });
    }

    public function reject(EarlyUnlockRequest $request, Admin $admin, ?string $note = null): EarlyUnlockRequest
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            $request = $this->lockPendingRequest($request);

            $request->update([
                'status' => EarlyUnlockRequest::STATUS_REJECTED,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $request->admin_note,
                'processed_at' => now(),
            ]);

            $request = $request->fresh(['user', 'contract.plan', 'processedByAdmin']);

            DB::afterCommit(function () use ($request): void {
                event(new EarlyUnlockRequestUpdated($request));
            });

            return $request;
        });
    }

    private function lockPendingRequest(EarlyUnlockRequest $request): EarlyUnlockRequest
    {
        $locked = EarlyUnlockRequest::query()
            ->whereKey($request->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! $locked->isPending()) {
            throw new RuntimeException(__('coin.admin.early_unlock_already_processed'));
        }

        return $locked;
    }

    private function assertCanRequest(User $user, Contract $contract): void
    {
        if (! $contract->isActive()) {
            throw new RuntimeException(__('coin.messages.early_unlock_inactive'));
        }

        if ((int) $contract->user_id !== (int) $user->id) {
            throw new RuntimeException(__('coin.messages.early_unlock_forbidden'));
        }

        if (EarlyUnlockRequest::pendingForContract($contract->id)) {
            throw new RuntimeException(__('coin.messages.early_unlock_pending_exists'));
        }

        if (PlanChangeRequest::pendingForContract($contract->id)) {
            throw new RuntimeException(__('coin.messages.early_unlock_plan_change_pending'));
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = 'EUR-'.Str::upper(Str::random(8));
        } while (EarlyUnlockRequest::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
