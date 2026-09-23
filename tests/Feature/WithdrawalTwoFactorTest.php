<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Mail\WithdrawalVerificationMail;
use App\Models\User;
use App\Models\Withdrawal;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class WithdrawalTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'coin.payments.driver' => 'mock',
        ]);

        $this->seed(PlanSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_withdrawal_requires_password_before_email_code(): void
    {
        Mail::fake();

        $user = $this->userReadyForWithdrawal();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('withdrawAmount', '100')
            ->set('withdrawCurrency', 'USDT')
            ->call('openPayoutPaymentModal')
            ->call('beginPayoutVerification')
            ->assertHasErrors(['payoutPassword']);

        Mail::assertNothingSent();
        $this->assertSame(0, Withdrawal::query()->count());
    }

    public function test_withdrawal_rejects_wrong_password(): void
    {
        Mail::fake();

        $user = $this->userReadyForWithdrawal('SecretPass1!');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('withdrawAmount', '100')
            ->set('withdrawCurrency', 'USDT')
            ->call('openPayoutPaymentModal')
            ->set('payoutPassword', 'WrongPass1!')
            ->call('beginPayoutVerification')
            ->assertHasErrors(['payoutPassword' => __('coin.auth.login_password_invalid')]);

        Mail::assertNothingSent();
        $this->assertSame(0, Withdrawal::query()->count());
    }

    public function test_withdrawal_sends_email_code_after_password_and_creates_request_after_code(): void
    {
        Mail::fake();

        $user = $this->userReadyForWithdrawal('SecretPass1!');
        $code = null;

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('withdrawAmount', '100')
            ->set('withdrawCurrency', 'USDT')
            ->call('openPayoutPaymentModal')
            ->set('payoutPassword', 'SecretPass1!')
            ->call('beginPayoutVerification')
            ->assertHasNoErrors()
            ->assertSet('paymentModalStep', 'payout_verify');

        Mail::assertSent(WithdrawalVerificationMail::class, function (WithdrawalVerificationMail $mail) use (&$code, $user) {
            $code = $mail->code;

            return $mail->hasTo($user->email);
        });

        $component
            ->set('payoutVerificationCode', $code)
            ->call('confirmPayoutPayment')
            ->assertHasNoErrors()
            ->assertSet('paymentModalStep', 'payout_gateway');

        $withdrawal = Withdrawal::query()->sole();

        $this->assertSame($user->id, $withdrawal->user_id);
        $this->assertSame('100.00', number_format((float) $withdrawal->amount, 2, '.', ''));
    }

    public function test_withdrawal_rejects_invalid_email_code(): void
    {
        Mail::fake();

        $user = $this->userReadyForWithdrawal('SecretPass1!');

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('withdrawAmount', '100')
            ->set('withdrawCurrency', 'USDT')
            ->call('openPayoutPaymentModal')
            ->set('payoutPassword', 'SecretPass1!')
            ->call('beginPayoutVerification')
            ->assertSet('paymentModalStep', 'payout_verify');

        $component
            ->set('payoutVerificationCode', '000000')
            ->call('confirmPayoutPayment')
            ->assertHasErrors(['payoutVerificationCode']);

        $this->assertSame(0, Withdrawal::query()->count());
    }

    public function test_withdrawal_cannot_be_confirmed_without_verification_step(): void
    {
        $user = $this->userReadyForWithdrawal('SecretPass1!');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('paymentModal', 'payout')
            ->set('paymentModalStep', 'review')
            ->set('withdrawAmount', '100')
            ->set('withdrawCurrency', 'USDT')
            ->call('confirmPayoutPayment')
            ->assertSet('paymentModalStep', 'review');

        $this->assertSame(0, Withdrawal::query()->count());
    }

    private function userReadyForWithdrawal(string $password = 'password'): User
    {
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $user->wallet->update([
            'available' => 500,
            'balance' => 500,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        return $user->fresh();
    }
}
