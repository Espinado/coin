<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanOffer;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PrivateOfferService
{
    public const DEFAULT_TTL_HOURS = 48;

    public function __construct(
        private SupportTicketService $support,
    ) {}

    /**
     * @return array{daily: float, total: float, amount: float, days: int, apr: float}
     */
    public function quotePreview(float $amount, int $days, float $apr): array
    {
        $amount = max(0, round($amount, 2));
        $days = max(1, $days);
        $apr = max(0, round($apr, 2));
        $daily = $amount > 0 && $apr > 0
            ? round($amount * ($apr / 100) / 365, 2)
            : 0.0;
        $total = round($daily * $days, 2);

        return [
            'amount' => $amount,
            'days' => $days,
            'apr' => $apr,
            'daily' => $daily,
            'total' => $total,
        ];
    }

    public function create(
        Admin $admin,
        User $user,
        float $amount,
        int $durationDays,
        float $apr,
        CarbonInterface|string|null $expiresAt = null,
        ?int $ttlHours = null,
        string $name = '',
    ): PlanOffer {
        $amount = round($amount, 2);
        $durationDays = (int) $durationDays;
        $apr = round($apr, 2);
        $name = trim($name);

        if ($amount < 1) {
            throw new RuntimeException(__('coin.messages.private_offer_invalid_amount'));
        }

        if ($durationDays < 1) {
            throw new RuntimeException(__('coin.messages.private_offer_invalid_term'));
        }

        if ($apr <= 0) {
            throw new RuntimeException(__('coin.messages.private_offer_invalid_apr'));
        }

        if ($name === '') {
            $name = __('coin.invest.private_offer_name', ['user' => $user->accountLabel()]);
        }

        if (mb_strlen($name) > 120) {
            throw new RuntimeException(__('coin.messages.private_offer_invalid_name'));
        }

        $expires = $this->resolveExpiresAt($expiresAt, $ttlHours);

        if ($expires->lessThanOrEqualTo(now())) {
            throw new RuntimeException(__('coin.messages.private_offer_invalid_expires'));
        }

        $currency = (string) config('coin.wallet.base_currency', 'USDT');
        $quote = $this->quotePreview($amount, $durationDays, $apr);

        $offer = DB::transaction(function () use ($admin, $user, $amount, $durationDays, $apr, $expires, $currency, $quote, $name) {
            $slug = $this->generateSlug((int) $user->id);

            $plan = Plan::query()->create([
                'slug' => $slug,
                'name' => $name,
                'tier_label' => 'VIP',
                'price_label' => number_format($amount, 0, '.', ',').' '.$currency,
                'min_deposit' => $amount,
                'price_amount' => $amount,
                'annual_profit_percent' => $apr,
                'currency' => $currency,
                'tflops' => (int) max(1, round($amount)),
                'duration_days' => $durationDays,
                'infra' => 'Reserved racks',
                'reward_multiplier' => 1,
                'daily_estimate' => $quote['daily'],
                'max_tflops' => (int) max(1, round($amount)),
                'sort_order' => 0,
                'is_featured' => false,
                'is_active' => true,
                'capacity_percent' => 100,
                'visibility' => Plan::VISIBILITY_PRIVATE,
                'offer_status' => Plan::OFFER_STATUS_PENDING,
            ]);

            return PlanOffer::query()->create([
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'created_by' => $admin->id,
                'amount' => $amount,
                'duration_days' => $durationDays,
                'annual_profit_percent' => $apr,
                'currency' => $currency,
                'status' => PlanOffer::STATUS_PENDING,
                'expires_at' => $expires,
            ])->fresh(['plan', 'user', 'createdByAdmin']);
        });

        $this->postOfferSummaryToSupport($admin, $user, $offer);

        return $offer;
    }

    public function revoke(PlanOffer $offer, Admin $admin): PlanOffer
    {
        return DB::transaction(function () use ($offer) {
            $offer = PlanOffer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();

            if (! $offer->isPending()) {
                throw new RuntimeException(__('coin.admin.private_offer_already_processed'));
            }

            $offer->update(['status' => PlanOffer::STATUS_REVOKED]);
            $offer->plan?->update([
                'is_active' => false,
                'offer_status' => Plan::OFFER_STATUS_REVOKED,
            ]);

            return $offer->fresh(['plan', 'user']);
        });
    }

    public function expireIfNeeded(PlanOffer $offer): PlanOffer
    {
        if (! $offer->isPending() || ! $offer->isExpiredByTime()) {
            return $offer;
        }

        return DB::transaction(function () use ($offer) {
            $locked = PlanOffer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending() || ! $locked->isExpiredByTime()) {
                return $locked;
            }

            $locked->update(['status' => PlanOffer::STATUS_EXPIRED]);
            $locked->plan?->update([
                'is_active' => false,
                'offer_status' => Plan::OFFER_STATUS_EXPIRED,
            ]);

            return $locked->fresh(['plan', 'user']);
        });
    }

    public function markAccepted(PlanOffer $offer): PlanOffer
    {
        return DB::transaction(function () use ($offer) {
            $locked = PlanOffer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                throw new RuntimeException(__('coin.messages.private_offer_unavailable'));
            }

            if ($locked->isExpiredByTime()) {
                $this->expireIfNeeded($locked);
                throw new RuntimeException(__('coin.messages.private_offer_expired'));
            }

            $locked->update([
                'status' => PlanOffer::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);
            $locked->plan?->update([
                'is_active' => false,
                'offer_status' => Plan::OFFER_STATUS_ACCEPTED,
            ]);

            return $locked->fresh(['plan', 'user']);
        });
    }

    public function assertPurchasable(User $user, Plan $plan): PlanOffer
    {
        if (! $plan->isPrivate()) {
            throw new RuntimeException(__('coin.messages.private_offer_unavailable'));
        }

        $offer = $plan->offer ?? PlanOffer::query()->where('plan_id', $plan->id)->first();

        if (! $offer instanceof PlanOffer) {
            throw new RuntimeException(__('coin.messages.private_offer_unavailable'));
        }

        $offer = $this->expireIfNeeded($offer);

        if ((int) $offer->user_id !== (int) $user->id) {
            throw new RuntimeException(__('coin.messages.private_offer_forbidden'));
        }

        if (! $offer->isPending() || ! $plan->is_active) {
            throw new RuntimeException(__('coin.messages.private_offer_unavailable'));
        }

        if ($offer->isExpiredByTime()) {
            throw new RuntimeException(__('coin.messages.private_offer_expired'));
        }

        return $offer;
    }

    /** @return Collection<int, Plan> */
    public function activePlansForUser(User $user): Collection
    {
        $this->expireStaleForUser($user);

        return Plan::query()
            ->where('visibility', Plan::VISIBILITY_PRIVATE)
            ->where('is_active', true)
            ->where('offer_status', Plan::OFFER_STATUS_PENDING)
            ->whereHas('offer', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', PlanOffer::STATUS_PENDING)
                    ->where('expires_at', '>', now());
            })
            ->with(['offer'])
            ->orderByDesc('id')
            ->get();
    }

    /** @return Collection<int, PlanOffer> */
    public function pendingOffersForUser(User $user): Collection
    {
        $this->expireStaleForUser($user);

        return PlanOffer::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->where('status', PlanOffer::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->get();
    }

    /** @return Collection<int, PlanOffer> */
    public function offersHistoryForUser(User $user, int $limit = 50): Collection
    {
        $this->expireStaleForUser($user);

        return PlanOffer::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function expireStaleForUser(User $user): void
    {
        PlanOffer::query()
            ->where('user_id', $user->id)
            ->where('status', PlanOffer::STATUS_PENDING)
            ->where('expires_at', '<=', now())
            ->get()
            ->each(fn (PlanOffer $offer) => $this->expireIfNeeded($offer));
    }

    private function resolveExpiresAt(CarbonInterface|string|null $expiresAt, ?int $ttlHours): CarbonInterface
    {
        if ($expiresAt instanceof CarbonInterface) {
            return $expiresAt->copy();
        }

        if (is_string($expiresAt) && trim($expiresAt) !== '') {
            return \Illuminate\Support\Carbon::parse($expiresAt);
        }

        $hours = $ttlHours !== null && $ttlHours > 0 ? $ttlHours : self::DEFAULT_TTL_HOURS;

        return now()->addHours($hours);
    }

    private function generateSlug(int $userId): string
    {
        do {
            $slug = 'vip-'.$userId.'-'.Str::lower(Str::random(6));
        } while (Plan::query()->where('slug', $slug)->exists());

        return $slug;
    }

    private function postOfferSummaryToSupport(Admin $admin, User $user, PlanOffer $offer): void
    {
        $offer->loadMissing('plan');

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('category', SupportTicket::CATEGORY_ENTERPRISE)
            ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_PENDING])
            ->latest('id')
            ->first();

        if (! $ticket instanceof SupportTicket) {
            try {
                $ticket = $this->support->createForUser(
                    $user,
                    __('coin.ticket.enterprise_subject'),
                    SupportTicket::CATEGORY_ENTERPRISE,
                    __('coin.ticket.enterprise_body'),
                );
            } catch (\Throwable $exception) {
                report($exception);

                return;
            }
        }

        $body = __('coin.admin.private_offer_chat_summary', [
            'name' => $offer->plan?->displayName() ?? '—',
            'amount' => $offer->formattedAmount(),
            'days' => $offer->duration_days,
            'apr' => $offer->formattedApr(),
            'expires' => $offer->expires_at
                ? \App\Support\LocaleFormat::dateTimeLocal($offer->expires_at)
                : '—',
        ]);

        try {
            $this->support->addAdminMessage($ticket, $admin, $body, SupportTicket::STATUS_PENDING);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
