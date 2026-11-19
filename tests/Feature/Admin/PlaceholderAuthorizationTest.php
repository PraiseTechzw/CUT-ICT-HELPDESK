<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceholderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /** @return array<int, string> */
    private function adminPlaceholderRoutes(): array
    {
        return [
            '/admin/equipment',
            '/admin/departments',
            '/admin/maintenance',
            '/admin/asset-categories',
            '/admin/settings/system',
            '/admin/settings/email-templates',
        ];
    }

    public function test_requester_cannot_open_administrative_placeholder_portals(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);

        foreach ($this->adminPlaceholderRoutes() as $uri) {
            $this->actingAs($requester)->get($uri)->assertForbidden();
        }
    }

    public function test_system_administrator_can_open_administrative_placeholder_portals(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        foreach ($this->adminPlaceholderRoutes() as $uri) {
            $this->actingAs($administrator)->get($uri)->assertOk();
        }
    }

    public function test_requester_cannot_open_report_placeholders(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);

        foreach (['/reports/users', '/reports/assets', '/reports/system'] as $uri) {
            $this->actingAs($requester)->get($uri)->assertForbidden();
        }
    }

    public function test_helpdesk_officer_can_open_report_placeholders(): void
    {
        $officer = $this->userWithRole(Roles::HELPDESK_OFFICER);

        foreach (['/reports/users', '/reports/assets', '/reports/system'] as $uri) {
            $this->actingAs($officer)->get($uri)->assertOk();
        }
    }

    public function test_knowledge_base_placeholders_remain_available_to_all_active_accounts(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);

        foreach (['/knowledge-base/articles', '/knowledge-base/categories', '/knowledge-base/faqs'] as $uri) {
            $this->actingAs($requester)->get($uri)->assertOk();
        }
    }

    public function test_auditor_keeps_audit_access_but_not_administrative_placeholders(): void
    {
        $auditor = $this->userWithRole(Roles::AUDITOR);

        $this->actingAs($auditor)->get('/admin/audit')->assertOk();
        $this->actingAs($auditor)->get('/admin/departments')->assertForbidden();
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
