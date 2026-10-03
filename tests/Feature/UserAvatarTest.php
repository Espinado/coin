<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Admin;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserAvatarTest extends TestCase
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

    public function test_user_can_upload_and_view_avatar(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileAvatar', UploadedFile::fake()->image('me.jpg', 240, 240))
            ->call('saveAvatar')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->hasAvatar());
        Storage::disk('local')->assertExists($user->avatar_path);

        $this->actingAs($user)
            ->get('http://coin.test/avatar')
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_user_can_remove_avatar(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileAvatar', UploadedFile::fake()->image('me.png', 200, 200))
            ->call('saveAvatar')
            ->assertHasNoErrors();

        $path = $user->fresh()->avatar_path;

        Livewire::actingAs($user->fresh())
            ->test(Dashboard::class)
            ->call('removeAvatar')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertFalse($user->hasAvatar());
        Storage::disk('local')->assertMissing($path);
    }

    public function test_admin_can_view_user_avatar_on_card(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileAvatar', UploadedFile::fake()->image('face.jpg', 180, 180))
            ->call('saveAvatar')
            ->assertHasNoErrors();

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/users/'.$user->id)
            ->assertOk()
            ->assertSee('/users/'.$user->id.'/avatar', false);

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/users/'.$user->id.'/avatar')
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_user_cannot_upload_non_image_avatar(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileAvatar', UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'))
            ->call('saveAvatar')
            ->assertHasErrors(['profileAvatar']);

        $this->assertFalse($user->fresh()->hasAvatar());
    }
}
