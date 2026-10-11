<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Console\Commands\CreateAdministrator;
use App\Models\AuditEvent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_requester_cannot_view_or_manage_user_accounts(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($requester)->get('/admin/users')->assertForbidden();
        $this->actingAs($requester)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($requester)->post('/admin/users', [])->assertForbidden();
    }

    public function test_administrator_can_create_account_with_least_privilege_role_and_audit_event(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        $this->actingAs($administrator)->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('User Information')
            ->assertSee('Initial Access')
            ->assertSee('Requester')
            ->assertSee('Initial Password');

        $response = $this->post('/admin/users', [
            'name' => 'Synthetic Requester',
            'email' => '  New.User@Example.Test  ',
            'password' => 'correct horse battery staple',
            'password_confirmation' => 'correct horse battery staple',
        ]);

        $user = User::query()->where('email', 'new.user@example.test')->firstOrFail();
        $response->assertRedirect(route('admin.users.show', $user));
        $this->assertTrue(Hash::check('correct horse battery staple', $user->password));
        $this->assertSame([Roles::REQUESTER], $user->roles()->pluck('slug')->all());
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.created',
            'actor_id' => $administrator->id,
            'subject_id' => (string) $user->id,
        ]);
        $this->assertStringNotContainsString('correct horse battery staple', (string) AuditEvent::query()->first()?->toJson());
    }

    public function test_profile_update_ignores_protected_status_and_role_fields(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $target = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($administrator)->put(route('admin.users.update', $target), [
            'name' => 'Updated Name',
            'email' => 'updated@example.test',
            'is_active' => false,
            'role_ids' => [Role::query()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->value('id')],
        ])->assertRedirect(route('admin.users.show', $target));

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated Name', 'is_active' => true]);
        $this->assertSame([Roles::REQUESTER], $target->fresh()->roles()->pluck('slug')->all());
    }

    public function test_only_authorized_administrator_can_assign_a_privileged_role(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);
        $target = User::factory()->create();
        $adminRole = Role::query()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->firstOrFail();

        $this->actingAs($requester)->put(route('admin.users.roles.update', $target), [
            'role_ids_present' => '1',
            'role_ids' => [$adminRole->id],
        ])->assertForbidden();

        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $this->actingAs($administrator)->put(route('admin.users.roles.update', $target), [
            'role_ids_present' => '1',
            'role_ids' => [$adminRole->id],
        ])->assertRedirect(route('admin.users.show', $target));

        $this->assertTrue($target->fresh()->hasRole(Roles::SYSTEM_ADMINISTRATOR));
    }

    public function test_administrator_cannot_change_their_own_roles_or_deactivate_their_own_account(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $requesterRole = Role::query()->where('slug', Roles::REQUESTER)->firstOrFail();

        $this->actingAs($administrator)->put(route('admin.users.roles.update', $administrator), [
            'role_ids_present' => '1',
            'role_ids' => [$requesterRole->id],
        ])->assertSessionHasErrors('role_ids');
        $this->assertTrue($administrator->fresh()->hasRole(Roles::SYSTEM_ADMINISTRATOR));

        $this->actingAs($administrator)->patch(route('admin.users.status', $administrator), [
            'is_active' => false,
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($administrator->fresh()->is_active);
    }

    public function test_administrator_can_deactivate_and_reactivate_another_account(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $target = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($administrator)->patch(route('admin.users.status', $target), [
            'is_active' => false,
        ])->assertRedirect();
        $this->assertFalse($target->fresh()->is_active);

        $this->patch(route('admin.users.status', $target), [
            'is_active' => true,
        ])->assertRedirect();
        $this->assertTrue($target->fresh()->is_active);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.deactivated', 'subject_id' => (string) $target->id,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.activated', 'subject_id' => (string) $target->id,
        ]);
    }

    public function test_administrator_can_revoke_every_role_from_another_account(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $target = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($administrator)->put(route('admin.users.roles.update', $target), [
            'role_ids_present' => '1',
        ])->assertRedirect(route('admin.users.show', $target));

        $this->assertSame([], $target->fresh()->roles()->pluck('slug')->all());
    }

    public function test_last_active_administrator_cannot_be_deactivated_or_stripped_of_admin_role(): void
    {
        $target = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $operator = User::factory()->create();
        $operatorRole = Role::query()->create([
            'name' => 'Synthetic Account Operator',
            'slug' => 'synthetic-account-operator',
        ]);
        $operatorRole->permissions()->attach(Permission::query()
            ->whereIn('slug', [Permissions::USERS_DEACTIVATE, Permissions::USERS_ASSIGN_ROLES])
            ->pluck('id'));
        $operator->roles()->attach($operatorRole);

        $this->actingAs($operator)->patch(route('admin.users.status', $target), [
            'is_active' => false,
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($target->fresh()->is_active);

        $this->put(route('admin.users.roles.update', $target), [
            'role_ids_present' => '1',
        ])->assertSessionHasErrors('role_ids');
        $this->assertTrue($target->fresh()->hasRole(Roles::SYSTEM_ADMINISTRATOR));
    }

    public function test_administrator_can_search_paginated_accounts(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        User::factory()->create(['name' => 'Needle Person', 'email' => 'needle@example.test']);
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.test']);

        $this->actingAs($administrator)->get('/admin/users?search=Needle')
            ->assertOk()
            ->assertSee('Needle Person')
            ->assertDontSee('Other Person');
    }

    public function test_administrator_can_filter_users_by_role_and_account_status(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $activeRequester = $this->userWithRole(Roles::REQUESTER);
        $inactiveRequester = $this->userWithRole(Roles::REQUESTER);
        $inactiveRequester->update(['is_active' => false]);
        $technician = $this->userWithRole(Roles::TECHNICIAN);

        $this->actingAs($administrator)->get(route('admin.users.index', [
            'role' => Roles::REQUESTER,
            'status' => 'active',
        ]))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee($activeRequester->email)
            ->assertDontSee($inactiveRequester->email)
            ->assertDontSee($technician->email);

        $this->get(route('admin.users.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee($inactiveRequester->email)
            ->assertDontSee($activeRequester->email);
    }

    public function test_administrator_bootstrap_command_is_registered_and_requires_role_catalogue(): void
    {
        $this->assertTrue(class_exists(CreateAdministrator::class));
        $this->artisan('app:create-administrator')
            ->expectsQuestion('Administrator name', 'Bootstrap Administrator')
            ->expectsQuestion('Administrator email', 'bootstrap@example.test')
            ->expectsQuestion('Password (minimum 12 characters)', 'correct horse battery staple')
            ->expectsQuestion('Confirm password', 'correct horse battery staple')
            ->expectsOutputToContain('Administrator account created')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'bootstrap@example.test']);
        $this->assertTrue(User::query()->where('email', 'bootstrap@example.test')->firstOrFail()->hasRole(Roles::SYSTEM_ADMINISTRATOR));
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
