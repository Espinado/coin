<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\KycDocument;
use App\Models\SupportMessageAttachment;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SupportChatAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        Storage::fake('local');
    }

    public function test_authenticated_user_can_attach_jpg_in_support_reply(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'KYC documents',
            SupportTicket::CATEGORY_KYC,
            'Please review my ID photos.',
        );

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('selectTicket', $ticket->id)
            ->set('replyBody', 'Here is my passport.')
            ->set('replyAttachments', [UploadedFile::fake()->image('passport.jpg', 400, 300)])
            ->call('sendTicketReply')
            ->assertHasNoErrors();

        $attachment = SupportMessageAttachment::query()->first();

        $this->assertNotNull($attachment);
        $this->assertNull($attachment->kyc_document_id);
        Storage::disk('local')->assertExists($attachment->path);

        $this->actingAs($user)
            ->get('http://coin.test/support/attachments/'.$attachment->id)
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_admin_can_save_chat_jpg_to_user_kyc_card(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->firstOrFail();

        $ticket = app(SupportTicketService::class)->createForUser(
            $user,
            'KYC',
            SupportTicket::CATEGORY_KYC,
            'Documents attached.',
            [UploadedFile::fake()->image('id.jpg', 320, 240)],
        );

        $attachment = SupportMessageAttachment::query()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/support/'.$ticket->id)
            ->assertOk()
            ->assertSee(__('coin.admin.support_save_to_kyc'), false)
            ->assertSee('support/attachments/'.$attachment->id, false);

        $this->actingAs($admin, 'admin')
            ->post('http://admin.coin.test/support/attachments/'.$attachment->id.'/save-kyc')
            ->assertRedirect(route('admin.support.show', $ticket));

        $attachment->refresh();
        $user->refresh();

        $this->assertNotNull($attachment->kyc_document_id);
        $this->assertSame(1, KycDocument::query()->where('user_id', $user->id)->count());
        $this->assertSame(User::KYC_PENDING, $user->kyc_status);
        Storage::disk('local')->assertExists(KycDocument::query()->firstOrFail()->path);
    }

    public function test_user_cannot_view_another_users_attachment(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        app(SupportTicketService::class)->createForUser(
            $owner,
            'Private',
            SupportTicket::CATEGORY_ACCOUNT,
            'Photo',
            [UploadedFile::fake()->image('secret.jpg')],
        );

        $attachment = SupportMessageAttachment::query()->firstOrFail();

        $this->actingAs($other)
            ->get('http://coin.test/support/attachments/'.$attachment->id)
            ->assertForbidden();
    }
}
