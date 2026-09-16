<?php

namespace App\Services;

use App\Events\PlanChangeRequestUpdated;
use App\Models\Admin;
use App\Models\Contract;
use App\Models\Plan;
use App\Models\PlanChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlanChangeRequestService
{
    public function __construct(
        private PlanPurchaseService $purchases,
        private UserNotificationService $notifications,
    ) {}

    public function createRequest(User $user, Contract $contract, Plan $newPlan): PlanChangeRequest
    {
        $this->assertCanRequest($user, $contract, $newPlan);

        $topUp = $this->purchases->topUpRequired($contract, $newPlan);

        if ($topUp > 0.009) {
            $wallet = $user->wallet ?? throw new RuntimeException(__('coin.messages.plan_change_insufficient_balance', [
                'amount' => number_format($topUp, 2, '.', ' ').' '.($contract->currency ?? config('coin.wallet.base_currency', 'USDT')),
            ]));

            if ((float) $wallet->available < $topUp) {
                throw new RuntimeException(__('coin.messages.plan_change_insufficient_balance', [
                    'amount' => number_format($topUp, 2, '.', ' ').' '.($contract->currency ?? config('coin.wallet.base_currency', 'USDT')),
                ]));
            }
        }

        return DB::transaction(function () use ($user, $contract, $newPlan, $topUp) {
            if ($topUp > 0.009) {
                $wallet = $user->wallet ?? throw new RuntimeException('User has no wallet.');
                $wallet->decrement('available', $topUp);
                $wallet->increment('pending', $topUp);
            }

            $request = PlanChangeRequest::query()->create([
                'user_id' => $user->id,
                'contract_id' => $contract->id,
                'from_plan_id' => $contract->plan_id,
                'to_plan_id' => $newPlan->id,
                'reference' => $this->generateReference(),
                'top_up_amount' => $topUp,
                'principal_after' => round((float) $contract->principal_amount + $topUp, 2),
                'status' => PlanChangeRequest::STATUS_PENDING,
            ]);

            $request = $request->fresh(['user', 'contract', 'fromPlan', 'toPlan']);

            $this->broadcastPlanChangeRequestUpdated($request);

            return $request;
        });
    }

    public function approve(PlanChangeRequest $request, Admin $admin, ?string $note = null): PlanChangeRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException(__('coin.admin.plan_change_already_processed'));
        }

        return DB::transaction(function () use ($request, $admin, $note) {
            $request->refresh();
            $request->load(['user', 'contract.plan', 'toPlan']);

            $contract = $request->contract;
            $newPlan = $request->toPlan;

            if (! $contract instanceof Contract || ! $contract->isActive()) {
                throw new RuntimeException(__('coin.messages.plan_change_inactive'));
            }

            if (! $newPlan instanceof Plan || ! $newPlan->is_active) {
                throw new RuntimeException(__('coin.messages.plan_change_unavailable'));
            }

            $this->purchases->changePlan(
                $request->user,
                $contract,
                $newPlan,
                topUpHeld: (float) $request->top_up_amount > 0.009,
            );

            $request->update([
                'status' => PlanChangeRequest::STATUS_APPROVED,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $request->admin_note,
                'processed_at' => now(),
            ]);

            $request = $request->fresh(['user', 'contract.plan', 'fromPlan', 'toPlan', 'processedByAdmin']);

            $this->broadcastPlanChangeRequestUpdated($request);
            $this->notifyPlanChangeApproved($request);

            return $request;
        });
    }

    public function reject(PlanChangeRequest $request, Admin $admin, ?string $note = null): PlanChangeRequest
    {
        if (! $request->isPending()) {
            throw new RuntimeException(__('coin.admin.plan_change_already_processed'));
        }

        return DB::transaction(function () use ($request, $admin, $note) {
            $request->refresh();
            $topUp = (float) $request->top_up_amount;

            if ($topUp > 0.009) {
                $wallet = $request->user->wallet ?? throw new RuntimeException('User has no wallet.');
                $wallet->decrement('pending', $topUp);
                $wallet->increment('available', $topUp);
            }

            $request->update([
                'status' => PlanChangeRequest::STATUS_REJECTED,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $request->admin_note,
                'processed_at' => now(),
            ]);

            $request = $request->fresh(['user', 'contract', 'fromPlan', 'toPlan', 'processedByAdmin']);

            $this->broadcastPlanChangeRequestUpdated($request);

            return $request;
        });
    }

    private function broadcastPlanChangeRequestUpdated(PlanChangeRequest $request): void
    {
        $requestId = $request->id;

        DB::afterCommit(function () use ($requestId): void {
            $fresh = PlanChangeRequest::query()
                ->with(['user', 'contract.plan', 'fromPlan', 'toPlan', 'processedByAdmin'])
                ->find($requestId);

            if ($fresh instanceof PlanChangeRequest) {
                PlanChangeRequestUpdated::dispatch($fresh);
            }
        });
    }

    private function notifyPlanChangeApproved(PlanChangeRequest $request): void
    {
        if ($request->status !== PlanChangeRequest::STATUS_APPROVED) {
            return;
        }

        $requestId = $request->id;

        DB::afterCommit(function () use ($requestId): void {
            $fresh = PlanChangeRequest::query()
                ->with(['user', 'contract', 'fromPlan', 'toPlan'])
                ->find($requestId);

            if (! $fresh instanceof PlanChangeRequest || $fresh->status !== PlanChangeRequest::STATUS_APPROVED) {
                return;
            }

            $user = $fresh->user;

            if ($user instanceof User) {
                $this->notifications->notifyPlanChangeApproved($user, $fresh);
            }
        });
    }

    private function assertCanRequest(User $user, Contract $contract, Plan $newPlan): void
    {
        if (! $contract->isActive()) {
            throw new RuntimeException(__('coin.messages.plan_change_inactive'));
        }

        if ((int) $contract->user_id !== (int) $user->id) {
            throw new RuntimeException(__('coin.messages.plan_change_forbidden'));
        }

        if ((int) $contract->plan_id === (int) $newPlan->id) {
            throw new RuntimeException(__('coin.messages.plan_change_same_plan'));
        }

        if (! $newPlan->is_active) {
            throw new RuntimeException(__('coin.messages.plan_change_unavailable'));
        }

        if ($newPlan->isEnterprise() && $newPlan->min_deposit === null) {
            throw new RuntimeException(__('coin.invest.contact_sales'));
        }

        if (PlanChangeRequest::pendingForContract($contract->id)) {
            throw new RuntimeException(__('coin.messages.plan_change_pending_exists'));
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = 'PCR-'.Str::upper(Str::random(8));
        } while (PlanChangeRequest::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
