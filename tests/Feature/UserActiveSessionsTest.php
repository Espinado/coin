<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\User;
use App\Services\UserActiveSessionService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class UserActiveSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'session.driver' => 'database',
        ]);

        $this->seed(PlanSeeder::class);
    }

    public function test_user_can_view_active_sessions_summary_and_list(): void
    {
        $user = User::factory()->create();

        $this->seedSession($user->id, 'other-session', '127.0.0.2', 'Mozilla/5.0 Chrome');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('openSessionsModal')
            ->assertSet('sessionsModalOpen', true)
            ->assertSee('Chrome')
            ->assertSee('Текущая');
    }

    public function test_user_can_revoke_other_sessions_with_password(): void
    {
        $user = User::factory()->create([
            'password' => 'SecretPass1!',
        ]);

        $this->seedSession($user->id, 'other-session', '127.0.0.2', 'Mozilla/5.0 Firefox');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('openSessionsModal')
            ->set('sessionsRevokePassword', 'SecretPass1!')
            ->call('revokeOtherSessions')
            ->assertHasNoErrors();

        $this->assertSame(1, app(UserActiveSessionService::class)->countForUser($user->id));
        $this->assertDatabaseMissing('sessions', ['id' => 'other-session']);
    }

    public function test_revoke_other_sessions_requires_password(): void
    {
        $user = User::factory()->create([
            'password' => 'SecretPass1!',
        ]);

        $this->seedSession($user->id, 'other-session', '127.0.0.2', 'Mozilla/5.0 Firefox');

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('openSessionsModal')
            ->call('revokeOtherSessions')
            ->assertHasErrors(['sessionsRevokePassword']);
    }

    private function seedSession(int $userId, string $id, string $ip, string $userAgent): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->getTimestamp(),
        ]);
    }
}
