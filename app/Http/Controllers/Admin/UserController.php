<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize(Permissions::USERS_VIEW);
        $query = $request->query('search', '');
        $search = is_string($query) ? trim($query) : '';
        $roleFilter = $request->query('role', '');
        $roleFilter = is_string($roleFilter) ? $roleFilter : '';
        $statusFilter = $request->query('status', '');
        $statusFilter = is_string($statusFilter) ? $statusFilter : '';

        $users = User::query()
            ->with('roles')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($roleFilter !== '', fn (Builder $query) => $query->whereHas('roles', fn (Builder $roles) => $roles->where('slug', $roleFilter)))
            ->when(in_array($statusFilter, ['active', 'inactive'], true), fn (Builder $query) => $query->where('is_active', $statusFilter === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => User::query()->count(),
            'active' => User::query()->where('is_active', true)->count(),
            'inactive' => User::query()->where('is_active', false)->count(),
            'new_this_month' => User::query()->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'summary' => $summary,
        ]);
    }

    public function create(): View
    {
        Gate::authorize(Permissions::USERS_CREATE);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['email'] = Str::lower(trim($data['email']));
        $data['is_active'] = true;

        $user = DB::transaction(function () use ($data, $request, $auditLogger): User {
            $user = User::query()->create($data);
            $requester = Role::query()->where('slug', Roles::REQUESTER)->firstOrFail();
            $user->roles()->sync([$requester->id]);
            $auditLogger->record($request->user(), 'user.created', $user, [
                'fields' => ['name', 'email', 'roles'],
            ]);

            return $user;
        });

        return redirect()->route('admin.users.show', $user)->with('status', 'Account created with the least-privilege Requester role.');
    }

    public function show(User $user): View
    {
        Gate::authorize(Permissions::USERS_VIEW);
        $user->load('roles');
        $roles = Gate::allows(Permissions::USERS_ASSIGN_ROLES)
            ? Role::query()->orderBy('name')->get()
            : collect();

        return view('admin.users.show', compact('user', 'roles'));
    }

    public function edit(User $user): View
    {
        Gate::authorize(Permissions::USERS_EDIT);
        $user->load('roles');
        $roles = Gate::allows(Permissions::USERS_ASSIGN_ROLES)
            ? Role::query()->orderBy('name')->get()
            : collect();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['email'] = Str::lower(trim($data['email']));

        DB::transaction(function () use ($user, $data, $request, $auditLogger): void {
            $user->update($data);
            $auditLogger->record($request->user(), 'user.profile_updated', $user, [
                'fields' => array_keys($data),
            ]);
        });

        return redirect()->route('admin.users.show', $user)->with('status', 'Account profile updated.');
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $active = (bool) $request->validated('is_active');
        if (! $active && $request->user()->is($user)) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        DB::transaction(function () use ($request, $user, $active, $auditLogger): void {
            $wasActive = $user->is_active;
            if ($wasActive && ! $active && $user->hasRole(Roles::SYSTEM_ADMINISTRATOR)) {
                $activeAdministrators = User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn (Builder $query) => $query->where('slug', Roles::SYSTEM_ADMINISTRATOR))
                    ->lockForUpdate()
                    ->count();
                if ($activeAdministrators <= 1) {
                    throw ValidationException::withMessages(['is_active' => 'The last active system administrator cannot be deactivated.']);
                }
            }

            $user->update(['is_active' => $active]);
            $auditLogger->record($request->user(), $active ? 'user.activated' : 'user.deactivated', $user, [
                'fields' => ['is_active'],
            ]);
        });

        return back()->with('status', $active ? 'Account activated.' : 'Account deactivated.');
    }
}
