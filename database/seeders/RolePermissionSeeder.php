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

            $systemAdministratorGrants = [
                Permissions::USERS_VIEW,
                Permissions::USERS_CREATE,
                Permissions::USERS_EDIT,
                Permissions::USERS_DEACTIVATE,
                Permissions::USERS_ASSIGN_ROLES,
                Permissions::CAMPUSES_VIEW,
                Permissions::CAMPUSES_MANAGE,
                Permissions::ZONES_VIEW,
                Permissions::ZONES_MANAGE,
                Permissions::BUILDINGS_VIEW,
                Permissions::BUILDINGS_MANAGE,
                Permissions::ROOMS_VIEW,
                Permissions::ROOMS_MANAGE,
                Permissions::AUDIT_VIEW,
                Permissions::TICKETS_CATALOGUES_MANAGE,
                Permissions::TEAMS_MANAGE,
            ];

            $roleDefinitions = [
                Roles::REQUESTER => ['Requester', 'Proposed role for service requesters.', [
                    ...$locationView,
                    Permissions::TICKETS_CREATE,
                    Permissions::TICKETS_VIEW_OWN,
                    Permissions::TICKETS_REPLY_OWN,
                    Permissions::TICKETS_REOPEN_OWN,
                ]],
                Roles::HELPDESK_OFFICER => ['Helpdesk Officer', 'Proposed role for authorized walk-in intake.', [
                    ...$locationView,
                    Permissions::TICKETS_VIEW_ALL,
                    Permissions::TICKETS_VIEW_INTERNAL,
                    Permissions::TICKETS_TRIAGE,
                    Permissions::TICKETS_ASSIGN,
                    Permissions::TICKETS_COMMENT_PUBLIC,
                    Permissions::TICKETS_COMMENT_INTERNAL,
                ]],
                Roles::TECHNICIAN => ['Technician', 'Proposed role for ICT technical staff.', [
                    ...$locationView,
                    Permissions::TICKETS_VIEW_ASSIGNED,
                    Permissions::TICKETS_VIEW_INTERNAL,
                    Permissions::TICKETS_WORK_ASSIGNED,
                    Permissions::TICKETS_COMMENT_PUBLIC,
                    Permissions::TICKETS_COMMENT_INTERNAL,
                ]],
                Roles::REGIONAL_SUPERVISOR => ['Regional Supervisor', 'Proposed role for operational supervisors.', $locationView],
                Roles::ICT_MANAGER => ['ICT Manager', 'Proposed central oversight role.', [...$locationView, Permissions::USERS_VIEW, Permissions::AUDIT_VIEW]],
                Roles::SYSTEM_ADMINISTRATOR => ['System Administrator', 'Proposed technical administration role; ticket content requires explicit operational permissions.', $systemAdministratorGrants],
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
