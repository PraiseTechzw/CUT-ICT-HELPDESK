@extends('layouts.app')

@section('title', 'Roles and permissions')

@section('content')
<div class="ticket-workspace role-catalogue-page">
    <header class="ticket-page-header ticket-catalogue-header">
        <div><h1>Roles &amp; Permissions</h1><p>Manage system roles and their permissions.</p></div>
    </header>

    <section class="role-summary-grid" aria-label="Role catalogue summary">
        <article><span class="role-summary-icon is-blue"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><small>Roles</small><strong>{{ number_format($roles->count()) }}</strong></span></article>
        <article><span class="role-summary-icon is-green"><svg data-lucide="key-round" aria-hidden="true"></svg></span><span><small>Permissions</small><strong>{{ number_format($permissions->count()) }}</strong></span></article>
        <article><span class="role-summary-icon is-amber"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><small>Role Assignments</small><strong>{{ number_format($roles->sum('users_count')) }}</strong></span></article>
    </section>

    <section class="ticket-page-panel role-catalogue-panel" aria-label="Roles and permissions">
        <div class="role-catalogue-tabs" role="tablist" aria-label="Catalogue views">
            <button id="roles-tab" type="button" role="tab" aria-selected="true" aria-controls="roles-panel" data-role-tab="roles">Roles</button>
            <button id="permissions-tab" type="button" role="tab" aria-selected="false" aria-controls="permissions-panel" data-role-tab="permissions" tabindex="-1">Permissions</button>
        </div>

        <section id="roles-panel" class="role-catalogue-view" role="tabpanel" aria-labelledby="roles-tab" data-role-panel="roles">
            <div class="ticket-table-wrap"><table class="ticket-data-table role-catalogue-table">
                <caption class="sr-only">{{ $roles->count() }} system roles and their assigned users and permissions</caption>
                <thead><tr><th scope="col">#</th><th scope="col">Role Name</th><th scope="col">Description</th><th scope="col">Users</th><th scope="col">Status</th><th scope="col">Permissions</th></tr></thead>
                <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                        <td><strong>{{ $role->name }}</strong><small>{{ $role->slug }}</small></td>
                        <td>{{ $role->description ?: '—' }}</td>
                        <td>{{ number_format($role->users_count) }}</td>
                        <td><span class="ticket-state-pill is-active">Defined</span></td>
                        <td><details class="role-permission-details"><summary>{{ $role->permissions->count() }} grants</summary><ul>@forelse ($role->permissions->sortBy('slug') as $permission)<li><code>{{ $permission->slug }}</code><span>{{ $permission->name }}</span></li>@empty<li>No permissions assigned.</li>@endforelse</ul></details></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>

        <section id="permissions-panel" class="role-catalogue-view" role="tabpanel" aria-labelledby="permissions-tab" data-role-panel="permissions" hidden>
            <div class="ticket-table-wrap"><table class="ticket-data-table permission-catalogue-table">
                <caption class="sr-only">{{ $permissions->count() }} defined system permissions</caption>
                <thead><tr><th scope="col">#</th><th scope="col">Permission</th><th scope="col">Description</th><th scope="col">Granted To</th></tr></thead>
                <tbody>
                @foreach ($permissions as $permission)
                    <tr>
                        <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                        <td><strong>{{ $permission->name }}</strong><small>{{ $permission->slug }}</small></td>
                        <td>{{ $permission->description ?: '—' }}</td>
                        <td><span class="user-role-list">@forelse ($permission->roles->sortBy('name') as $role)<span class="user-role-pill">{{ $role->name }}</span>@empty<span class="user-role-none">No roles</span>@endforelse</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>

        <p class="role-catalogue-note"><svg data-lucide="info" aria-hidden="true"></svg>Role definitions and grants are maintained centrally and applied through the role seeder.</p>
    </section>
</div>
@endsection
