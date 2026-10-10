<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $permissionIds = [];
            foreach (Permissions::definitions() as $slug => $name) {
                $permission = Permission::updateOrCreate(['slug' => $slug], [
                    'name' => $name,
                    'description' => null,
                ]);
                $permissionIds[$slug] = $permission->id;
            }

            $locationView = [
                Permissions::CAMPUSES_VIEW,
                Permissions::ZONES_VIEW,
                Permissions::BUILDINGS_VIEW,
                Permissions::ROOMS_VIEW,
            ];

            $roleDefinitions = [
                Roles::REQUESTER => ['Requester', 'Proposed role for service requesters.', $locationView],
                Roles::HELPDESK_OFFICER => ['Helpdesk Officer', 'Proposed role for authorized walk-in intake.', $locationView],
                Roles::TECHNICIAN => ['Technician', 'Proposed role for ICT technical staff.', $locationView],
                Roles::REGIONAL_SUPERVISOR => ['Regional Supervisor', 'Proposed role for operational supervisors.', $locationView],
                Roles::ICT_MANAGER => ['ICT Manager', 'Proposed central oversight role.', [...$locationView, Permissions::USERS_VIEW, Permissions::AUDIT_VIEW]],
                Roles::SYSTEM_ADMINISTRATOR => ['System Administrator', 'Proposed technical administration role.', array_keys(Permissions::definitions())],
                Roles::AUDITOR => ['Auditor', 'Proposed read-only audit role.', [Permissions::AUDIT_VIEW]],
            ];

            foreach ($roleDefinitions as $slug => [$name, $description, $grants]) {
                $role = Role::updateOrCreate(['slug' => $slug], [
                    'name' => $name,
                    'description' => $description,
                ]);
                $role->permissions()->sync(array_map(fn (string $permission): int => $permissionIds[$permission], $grants));
            }
        });
    }
}
