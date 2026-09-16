<?php

namespace App\Services;

use App\Mail\UserEventNotificationMail;
use App\Models\Contract;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class UserNotificationService
{
    public const TYPE_PROFIT_CREDIT = 'profit_credit';

    public const TYPE_CONTRACT_EXPIRY = 'contract_expiry';

    public const TYPE_MATURITY_ALERT = 'maturity_alert';

    public const TYPE_REFERRAL_ACTIVITY = 'referral_activity';

    public const TYPE_REFERRAL_COMMISSION = 'referral_commission';

    public const TYPE_PAYOUT_COMPLETED = 'payout_completed';

    public const TYPE_PLAN_CHANGE_APPROVED = 'plan_change_approved';

    public function send(User $user, string $type, string $subject, string $intro, array $lines = [], ?string $footer = null): void
    {
        if (! $user->wantsNotification($type)) {
            return;
        }

        try {
            Mail::to($user->email)->send(new UserEventNotificationMail(
                user: $user,
                subjectLine: $subject,
                intro: $intro,
                lines: $lines,
                footer: $footer,
            ));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function notifyDailyProfit(User $user, Contract $contract, float $amount, string $currency): void
    {
        $this->send(
            $user,
            self::TYPE_PROFIT_CREDIT,
            __('coin.notifications.mail.profit_subject'),
            __('coin.notifications.mail.profit_intro', ['name' => $user->name]),
            [
                __('coin.notifications.mail.profit_plan', ['plan' => $contract->plan?->displayName() ?? $contract->code]),
                __('coin.notifications.mail.profit_amount', [
                    'amount' => number_format($amount, 2, '.', ','),
                    'currency' => $currency,
                ]),
            ],
        );
    }

    public function notifyContractExpiryReminder(User $user, Contract $contract, int $daysLeft): void
    {
        $this->send(
            $user,
            self::TYPE_CONTRACT_EXPIRY,
            __('coin.notifications.mail.expiry_subject'),
            __('coin.notifications.mail.expiry_intro', ['name' => $user->name]),
            [
                __('coin.notifications.mail.expiry_plan', ['plan' => $contract->plan?->displayName() ?? $contract->code]),
                $daysLeft === 0
                    ? __('coin.notifications.mail.expiry_today')
                    : __('coin.notifications.mail.expiry_days', ['days' => $daysLeft]),
                __('coin.notifications.mail.expiry_date', ['date' => $contract->formattedEndsAt()]),
            ],
        );
    }

    public function notifyContractMatured(User $user, Contract $contract, float $principal, string $currency): void
    {
        $this->send(
            $user,
            self::TYPE_MATURITY_ALERT,
            __('coin.notifications.mail.maturity_subject'),
            __('coin.notifications.mail.maturity_intro', ['name' => $user->name]),
            [
                __('coin.notifications.mail.maturity_plan', ['plan' => $contract->plan?->displayName() ?? $contract->code]),
                __('coin.notifications.mail.maturity_principal', [
                    'amount' => number_format($principal, 2, '.', ','),
                    'currency' => $currency,
                ]),
                __('coin.notifications.mail.maturity_profit', [
                    'amount' => number_format((float) $contract->accrued_amount, 2, '.', ','),
                    'currency' => $currency,
                ]),
            ],
        );
    }

    public function notifyWithdrawalPaid(User $user, Withdrawal $withdrawal, float $netAmount, string $currency): void
    {
        $lines = [
            __('coin.notifications.mail.payout_reference', ['reference' => $withdrawal->reference]),
            __('coin.notifications.mail.payout_amount', [
                'amount' => $withdrawal->formattedAmount(),
                'currency' => $currency,
            ]),
            __('coin.notifications.mail.payout_net', [
                'amount' => number_format($netAmount, 2, '.', ','),
                'currency' => $currency,
            ]),
        ];

        if ($withdrawal->payout_address) {
            $lines[] = __('coin.notifications.mail.payout_address', ['address' => $withdrawal->payout_address]);
        }

        if ($withdrawal->network_label) {
            $lines[] = __('coin.notifications.mail.payout_network', ['network' => $withdrawal->network_label]);
        }

        $this->send(
            $user,
            self::TYPE_PAYOUT_COMPLETED,
            __('coin.notifications.mail.payout_subject'),
            __('coin.notifications.mail.payout_intro', ['name' => $user->name]),
            $lines,
            __('coin.notifications.mail.payout_footer'),
        );
    }

    public function notifyPlanChangeApproved(User $user, PlanChangeRequest $request): void
    {
        $request->loadMissing(['contract', 'fromPlan', 'toPlan']);

        $lines = [
            __('coin.notifications.mail.plan_change_reference', ['reference' => $request->reference]),
            __('coin.notifications.mail.plan_change_contract', ['contract' => $request->contract?->code ?? '—']),
            __('coin.notifications.mail.plan_change_from', ['plan' => $request->fromPlan?->displayName() ?? '—']),
            __('coin.notifications.mail.plan_change_to', ['plan' => $request->toPlan?->displayName() ?? '—']),
            __('coin.notifications.mail.plan_change_principal', ['amount' => $request->formattedPrincipalAfter()]),
        ];

        if ((float) $request->top_up_amount > 0.009) {
            $lines[] = __('coin.notifications.mail.plan_change_top_up', ['amount' => $request->formattedTopUp()]);
        }

        $this->send(
            $user,
            self::TYPE_PLAN_CHANGE_APPROVED,
            __('coin.notifications.mail.plan_change_subject'),
            __('coin.notifications.mail.plan_change_intro', ['name' => $user->name]),
            $lines,
            __('coin.notifications.mail.plan_change_footer'),
        );
    }

    public function notifyReferralCommission(User $referrer, User $referral, float $commission, string $currency): void
    {
        $this->send(
            $referrer,
            self::TYPE_REFERRAL_COMMISSION,
            __('coin.notifications.mail.referral_subject'),
            __('coin.notifications.mail.referral_intro', ['name' => $referrer->name]),
            [
                __('coin.notifications.mail.referral_user', ['user' => $referral->accountLabel()]),
                __('coin.notifications.mail.referral_amount', [
                    'amount' => number_format($commission, 2, '.', ','),
                    'currency' => $currency,
                ]),
            ],
        );
    }

    public function maybeSendContractExpiryReminder(Contract $contract): void
    {
        $contract->loadMissing('user', 'plan');

        $user = $contract->user;

        if (! $user instanceof User || ! $contract->ends_at || ! $contract->isActive()) {
            return;
        }

        $daysLeft = (int) now()->startOfDay()->diffInDays($contract->ends_at->copy()->startOfDay(), false);

        if (! in_array($daysLeft, [7, 3, 1, 0], true)) {
            return;
        }

        $cacheKey = sprintf('contract-expiry-notice:%d:%d:%s', $contract->id, $daysLeft, now()->toDateString());

        if (Cache::has($cacheKey)) {
            return;
        }

        $this->notifyContractExpiryReminder($user, $contract, $daysLeft);
        Cache::put($cacheKey, true, now()->addDay());
    }
}
