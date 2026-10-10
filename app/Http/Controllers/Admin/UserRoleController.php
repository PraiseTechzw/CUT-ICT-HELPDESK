<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserRoleController extends Controller
{
    public function update(UpdateUserRolesRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['role_ids' => 'You cannot change your own roles.']);
        }

        $roleIds = array_map('intval', $request->validated('role_ids'));

        DB::transaction(function () use ($request, $user, $roleIds, $auditLogger): void {
            $hasAdministratorRole = $user->roles()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->exists();
            $administratorRole = Role::query()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->firstOrFail();
            $removingAdministrator = $hasAdministratorRole && ! in_array($administratorRole->id, $roleIds, true);

            if ($user->is_active && $removingAdministrator) {
                $activeAdministrators = User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn (Builder $query) => $query->where('slug', Roles::SYSTEM_ADMINISTRATOR))
                    ->lockForUpdate()
                    ->count();
                if ($activeAdministrators <= 1) {
                    throw ValidationException::withMessages(['role_ids' => 'The last active system administrator must retain that role.']);
                }
            }

            $user->roles()->sync($roleIds);
            $auditLogger->record($request->user(), 'user.roles_updated', $user, [
                'role_ids' => $roleIds,
            ]);
        });

        return redirect()->route('admin.users.show', $user)->with('status', 'Account roles updated.');
    }
}
