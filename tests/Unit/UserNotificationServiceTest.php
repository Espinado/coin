<?php

namespace Tests\Unit;

use App\Mail\UserEventNotificationMail;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_commission_email_is_sent_even_when_referral_activity_toggle_is_off(): void
    {
        Mail::fake();

        $referrer = User::factory()->create([
            'notify_referral_activity' => false,
        ]);

        $referral = User::factory()->create([
            'referred_by_user_id' => $referrer->id,
        ]);

        app(UserNotificationService::class)->notifyReferralCommission(
            $referrer,
            $referral,
            120.0,
            'USDT',
            'purchase',
        );

        Mail::assertSent(UserEventNotificationMail::class, function (UserEventNotificationMail $mail) use ($referrer): bool {
            return $mail->hasTo($referrer->email);
        });
    }
}
