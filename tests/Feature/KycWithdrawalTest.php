<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\KycDocument;
use App\Models\User;
use App\Services\KycDocumentService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KycWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(AdminSeeder::class);
        Storage::fake('local');
    }

    public function test_new_user_has_unapproved_kyc_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertSame(User::KYC_NONE, $user->kyc_status);
        $this->assertFalse($user->isKycApproved());
    }

    public function test_withdrawal_requires_approved_kyc(): void
    {
        $user = User::factory()->create();
        app(WalletService::class)->ensureWallet($user)->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);
        $user->refresh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('coin.wallet.kyc_required'));

        app(WithdrawalService::class)->createForUser($user, 50, 'USDT');
    }

    public function test_dashboard_blocks_payout_modal_without_kyc(): void
    {
        $user = User::factory()->create();
        app(WalletService::class)->ensureWallet($user)->update([
            'available' => 500,
            'balance' => 500,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        Livewire::actingAs($user->fresh())
            ->test(Dashboard::class)
            ->set('withdrawAmount', '50')
            ->set('withdrawCurrency', 'USDT')
            ->call('openPayoutPaymentModal')
            ->assertHasErrors(['withdrawAmount'])
            ->assertSet('paymentModal', null);
    }

    public function test_admin_can_upload_jpg_preview_and_approve_kyc(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();

        $photo = UploadedFile::fake()->image('passport.jpg', 400, 300);

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/users/'.$user->id.'/kyc-documents', [
                'kyc_photos' => [$photo],
            ])
            ->assertRedirect(route('admin.users.show', $user));

        $user->refresh();
        $document = KycDocument::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($document);
        $this->assertSame(User::KYC_PENDING, $user->kyc_status);
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/users/'.$user->id.'/kyc-documents/'.$document->id)
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/users/'.$user->id.'/kyc/approve')
            ->assertRedirect(route('admin.users.show', $user));

        $this->assertTrue($user->fresh()->isKycApproved());

        app(WalletService::class)->ensureWallet($user)->update([
            'available' => 200,
            'balance' => 200,
            'payout_address' => PayoutAddressTest::VALID_TRON_ADDRESS,
            'network_label' => 'TRC-20',
        ]);

        $withdrawal = app(WithdrawalService::class)->createForUser($user->fresh(), 50, 'USDT');
        $this->assertSame(50.0, (float) $withdrawal->amount);
    }

    public function test_admin_rejects_non_jpg_kyc_upload(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/users/'.$user->id.'/kyc-documents', [
                'kyc_photos' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
            ])
            ->assertSessionHasErrors(['kyc_photos.0']);

        $this->assertSame(0, KycDocument::query()->count());
    }

    public function test_admin_can_delete_kyc_photo(): void
    {
        $admin = Admin::query()->firstOrFail();
        $user = User::factory()->create();

        $documents = app(KycDocumentService::class)->storeJpgs(
            $user,
            [UploadedFile::fake()->image('id.jpg')],
            $admin,
        );

        $this->actingAs($admin, 'admin')
            ->delete('http://admin.coin.test/users/'.$user->id.'/kyc-documents/'.$documents[0]->id)
            ->assertRedirect(route('admin.users.show', $user));

        $this->assertSame(0, KycDocument::query()->count());
    }
}
