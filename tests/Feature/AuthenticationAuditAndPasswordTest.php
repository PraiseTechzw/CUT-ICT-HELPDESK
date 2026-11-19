<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAuditAndPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_successful_login_records_audit_event_with_source_metadata(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-login@example.test',
            'password' => Hash::make('correct horse battery staple'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct horse battery staple',
        ])->assertRedirect('/dashboard');

        $event = AuditEvent::query()->where('action', 'auth.login')->firstOrFail();
        $this->assertSame($user->id, $event->actor_id);
        $this->assertNotNull($event->ip_address);
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_records_audit_event_without_credentials(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'no-such-user@example.test',
            'password' => 'definitely-not-the-password',
        ])->assertRedirect('/login');

        $event = AuditEvent::query()->where('action', 'auth.login.failed')->firstOrFail();
        $this->assertNull($event->actor_id);
        $this->assertNull($event->subject_id);
        $this->assertStringNotContainsString('definitely-not-the-password', json_encode($event->metadata, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('no-such-user@example.test', json_encode($event->metadata, JSON_THROW_ON_ERROR));
    }

    public function test_logout_records_audit_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/logout')->assertRedirect();

        $event = AuditEvent::query()->where('action', 'auth.logout')->firstOrFail();
        $this->assertSame($user->id, $event->actor_id);
    }

    public function test_user_can_change_their_password_and_the_change_is_audited(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original password phrase')]);

        $this->actingAs($user)->patch(route('account.password.update'), [
            'current_password' => 'original password phrase',
            'password' => 'a completely new passphrase',
            'password_confirmation' => 'a completely new passphrase',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('a completely new passphrase', $user->fresh()->password));
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.password_changed',
            'actor_id' => $user->id,
        ]);

        $metadata = json_encode(AuditEvent::query()->where('action', 'user.password_changed')->firstOrFail()->metadata, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('a completely new passphrase', $metadata);
        $this->assertStringNotContainsString('original password phrase', $metadata);
    }

    public function test_wrong_current_password_is_rejected_and_password_unchanged(): void
    {
        $user = User::factory()->create(['password' => Hash::make('original password phrase')]);

        $this->actingAs($user)->patch(route('account.password.update'), [
            'current_password' => 'not my password',
            'password' => 'a completely new passphrase',
            'password_confirmation' => 'a completely new passphrase',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('original password phrase', $user->fresh()->password));
        $this->assertDatabaseMissing('audit_events', ['action' => 'user.password_changed']);
    }

    public function test_administrator_can_reset_another_accounts_password(): void
    {
        $admin = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $target = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'administrator supplied passphrase',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('administrator supplied passphrase', $target->fresh()->password));
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.password_reset_by_admin',
            'actor_id' => $admin->id,
            'subject_id' => (string) $target->id,
        ]);
    }

    public function test_requester_cannot_change_another_accounts_password(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);
        $target = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($requester)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'administrator supplied passphrase',
        ])->assertForbidden();

        $this->assertFalse(Hash::check('administrator supplied passphrase', $target->fresh()->password));
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
