<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveUserGroupRequest;
use App\Http\Requests\Admin\UpdateUserGroupMembersRequest;
use App\Models\User;
use App\Models\UserGroup;
use App\Support\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserGroupController extends Controller
{
    public function index(): View
    {
        Gate::authorize(Permissions::USER_GROUPS_MANAGE);
        $groups = UserGroup::query()->with('users:id,name,email')->withCount('users')->orderBy('name')->get();
        $availableUsers = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.user-groups.index', compact('groups', 'availableUsers'));
    }

    public function store(SaveUserGroupRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $group = DB::transaction(function () use ($data, $request, $auditLogger): UserGroup {
            $group = UserGroup::query()->create($data);
            $auditLogger->record($request->user(), 'user_group.created', $group, [
                'fields' => ['name', 'slug', 'description', 'is_active'],
            ]);

            return $group;
        });

        return redirect()->route('admin.user-groups.index')->with('status', 'User group created.');
    }

    public function update(SaveUserGroupRequest $request, UserGroup $userGroup, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($data, $request, $userGroup, $auditLogger): void {
            $userGroup->update($data);
            $auditLogger->record($request->user(), 'user_group.updated', $userGroup, [
                'fields' => array_keys($data),
            ]);
        });

        return redirect()->route('admin.user-groups.index')->with('status', 'User group updated.');
    }

    public function updateMembers(UpdateUserGroupMembersRequest $request, UserGroup $userGroup, AuditLogger $auditLogger): RedirectResponse
    {
        $userIds = $request->validated('user_ids', []);

        DB::transaction(function () use ($userIds, $request, $userGroup, $auditLogger): void {
            $userGroup->users()->sync($userIds);
            $auditLogger->record($request->user(), 'user_group.members_updated', $userGroup, [
                'member_count' => count($userIds),
            ]);
        });

        return redirect()->route('admin.user-groups.index')->with('status', 'Group members updated.');
    }
}
