<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::USERS_ASSIGN_ROLES);
        $roles = Role::query()->with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::query()->with('roles')->orderBy('slug')->get();

        return view('admin.users.roles', compact('roles', 'permissions'));
    }
}
