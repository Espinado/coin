<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Mailbox;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMailboxTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'coin.mailboxes.driver' => 'array',
            'coin.mailboxes.domain' => 'cudaflops.com',
            'coin.mailboxes.mail_host' => 'mail.cudaflops.com',
            'coin.mailboxes.webmail_url' => 'https://webmail.example.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->admin = Admin::query()->firstOrFail();
    }

    public function test_mailboxes_index_shows_server_settings_and_create_form(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/settings/mailboxes')
            ->assertOk()
            ->assertSee(__('coin.admin.settings_tab_mailboxes'), false)
            ->assertSee('mail.cudaflops.com', false)
            ->assertSee(__('coin.admin.mailboxes.create_title'), false);
    }

    public function test_admin_can_create_mailbox_with_array_driver(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('http://admin.coin.test/settings/mailboxes', [
                'local_part' => 'support',
                'password' => 'SecretPass1!',
                'quota_mb' => 512,
            ])
            ->assertRedirect(route('admin.settings.mailboxes.show', ['localPart' => 'support'], absolute: false));

        $this->assertDatabaseHas('mailboxes', [
            'email' => 'support@cudaflops.com',
            'local_part' => 'support',
            'quota_mb' => 512,
        ]);

        $box = Mailbox::query()->where('email', 'support@cudaflops.com')->firstOrFail();
        $this->assertSame('SecretPass1!', $box->password);
    }

    public function test_admin_can_view_and_change_mailbox_password(): void
    {
        Mailbox::query()->create([
            'email' => 'info@cudaflops.com',
            'local_part' => 'info',
            'password' => 'OldSecret1!',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/settings/mailboxes/info')
            ->assertOk()
            ->assertSee('info@cudaflops.com', false)
            ->assertSee('OldSecret1!', false);

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/settings/mailboxes/info/password', [
                'password' => 'NewSecret2!',
            ])
            ->assertRedirect(route('admin.settings.mailboxes.show', ['localPart' => 'info'], absolute: false));

        $this->assertSame('NewSecret2!', Mailbox::query()->where('email', 'info@cudaflops.com')->firstOrFail()->password);
    }

    public function test_settings_platform_tab_links_to_mailboxes(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/settings')
            ->assertOk()
            ->assertSee(route('admin.settings.mailboxes.index', absolute: false), false);
    }

    public function test_null_driver_shows_disabled_message(): void
    {
        config(['coin.mailboxes.driver' => 'null']);

        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/settings/mailboxes')
            ->assertOk()
            ->assertSee(__('coin.admin.mailboxes.disabled'), false);
    }
}
