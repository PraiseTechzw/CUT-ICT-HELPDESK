<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Support\Permissions;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_system_administrator_can_create_groups_and_assign_any_user_without_changing_roles(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Group Administrator');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Group Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Group Technician');

        $this->actingAs($administrator)->get(route('admin.user-groups.index'))
            ->assertOk()
            ->assertSee('User Groups')
            ->assertSee('Organize users into groups')
            ->assertSee($requester->email)
            ->assertSee($technician->email);

        $this->post(route('admin.user-groups.store'), [
            'name' => 'Finance',
            'description' => 'Finance department accounts.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.user-groups.index'));

        $group = UserGroup::query()->where('slug', 'finance')->firstOrFail();
        $this->put(route('admin.user-groups.members.update', $group), [
            'user_ids' => [$requester->id, $technician->id],
        ])->assertRedirect(route('admin.user-groups.index'));

        $this->assertEqualsCanonicalizing([$requester->id, $technician->id], $group->users()->pluck('users.id')->all());
        $this->assertSame([Roles::REQUESTER], $requester->fresh()->roles()->pluck('slug')->all());
        $this->assertSame([Roles::TECHNICIAN], $technician->fresh()->roles()->pluck('slug')->all());
        $this->assertTrue($administrator->hasPermissionTo(Permissions::USER_GROUPS_MANAGE));
    }

    public function test_user_without_group_management_permission_cannot_open_or_change_groups(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'No Group Access Requester');
        $group = UserGroup::query()->create([
            'name' => 'Existing Group',
            'slug' => 'existing-group',
            'is_active' => true,
        ]);

        $this->actingAs($requester)->get(route('admin.user-groups.index'))->assertForbidden();
        $this->post(route('admin.user-groups.store'), [
            'name' => 'Unauthorized Group',
            'is_active' => '1',
        ])->assertForbidden();
        $this->put(route('admin.user-groups.members.update', $group), [
            'user_ids' => [$requester->id],
        ])->assertForbidden();
    }

    private function userWithRole(string $roleSlug, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

        return $user;
    }
}
