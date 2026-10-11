@extends('layouts.app')

@section('title', 'Account details')

@section('content')
<div class="ticket-workspace user-detail-page">
    <header class="user-detail-header">
        <div class="user-detail-identity">
            <a class="user-create-back" href="{{ route('admin.users.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>All Users</a>
            <div class="user-detail-title-row">
                <span class="user-detail-avatar" aria-hidden="true">{{ collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                <div><p class="ticket-page-kicker">ACCOUNT DETAILS</p><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div>
            </div>
        </div>
        <div class="user-detail-actions">
            <span class="user-account-state {{ $user->is_active ? 'is-active' : 'is-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
            @can('users.edit')<a class="ticket-secondary-button" href="{{ route('admin.users.edit', $user) }}"><svg data-lucide="pencil" aria-hidden="true"></svg>Edit Profile</a>@endcan
        </div>
    </header>

    <section class="user-summary-grid" aria-label="Account overview">
        <article class="user-summary-item"><span class="user-summary-icon is-blue"><svg data-lucide="calendar-days" aria-hidden="true"></svg></span><span><small>Created</small><strong class="user-summary-date">{{ $user->created_at?->format('d M Y') ?? '—' }}</strong></span></article>
        <article class="user-summary-item"><span class="user-summary-icon is-amber"><svg data-lucide="clock-3" aria-hidden="true"></svg></span><span><small>Last Updated</small><strong class="user-summary-date">{{ $user->updated_at?->format('d M Y') ?? '—' }}</strong></span></article>
        <article class="user-summary-item"><span class="user-summary-icon is-green"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><small>Assigned Roles</small><strong>{{ number_format($user->roles->count()) }}</strong></span></article>
        @can('user_groups.manage')<article class="user-summary-item"><span class="user-summary-icon is-blue"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><small>User Groups</small><strong>{{ number_format($user->userGroups->count()) }}</strong></span></article>@endcan
    </section>

    <div class="user-detail-grid">
        <section class="ticket-page-panel user-detail-panel" aria-labelledby="user-status-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">ACCOUNT</p><h2 id="user-status-heading">Account Status</h2></div><span class="user-account-state {{ $user->is_active ? 'is-active' : 'is-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></header>
            <dl class="user-detail-meta"><div><dt>Full Name</dt><dd>{{ $user->name }}</dd></div><div><dt>Email Address</dt><dd>{{ $user->email }}</dd></div><div><dt>Created</dt><dd>{{ $user->created_at?->toDayDateTimeString() }}</dd></div><div><dt>Last Updated</dt><dd>{{ $user->updated_at?->toDayDateTimeString() }}</dd></div></dl>
            @can('users.deactivate')
                @unless (auth()->user()->is($user))
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="user-detail-status-form">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}"><button class="{{ $user->is_active ? 'ticket-danger-button' : 'ticket-primary-button' }}" type="submit"><svg data-lucide="{{ $user->is_active ? 'user-round-x' : 'user-round-check' }}" aria-hidden="true"></svg>{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}</button><x-field-error field="is_active" /></form>
                @endunless
            @endcan
        </section>

        <section class="ticket-page-panel user-detail-panel" aria-labelledby="assigned-roles-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">ACCESS</p><h2 id="assigned-roles-heading">Assigned Roles</h2></div></header>
            <div class="user-detail-role-list">@forelse ($user->roles as $role)<article class="user-detail-role"><span class="user-detail-role-icon"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><strong>{{ $role->name }}</strong><small>{{ $role->description }}</small></span></article>@empty<p class="user-detail-empty">No roles assigned. This account has no application access.</p>@endforelse</div>
            @can('users.assign_roles')
                @unless (auth()->user()->is($user))
                    <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" class="user-detail-role-form">@csrf @method('PUT')<input type="hidden" name="role_ids_present" value="1"><fieldset><legend>Change roles</legend><p>Roles determine this account’s application permissions. Clear every role to revoke all application access.</p><div class="user-role-checkbox-list">@foreach ($roles as $role)<label><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $user->roles->modelKeys())))><span><strong>{{ $role->name }}</strong><small>{{ $role->description }}</small></span></label>@endforeach</div><x-field-error field="role_ids" /><button class="ticket-primary-button" type="submit"><svg data-lucide="save" aria-hidden="true"></svg>Save Roles</button></fieldset></form>
                @endunless
            @endcan
        </section>

        @can('user_groups.manage')
        <section class="ticket-page-panel user-detail-panel user-detail-groups" aria-labelledby="user-groups-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">ORGANIZATION</p><h2 id="user-groups-heading">User Groups</h2></div><span class="ticket-count-label">{{ $user->userGroups->count() }} assigned</span></header>
            <div class="user-detail-role-list">@forelse ($user->userGroups as $group)<article class="user-detail-role"><span class="user-detail-role-icon is-group"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><strong>{{ $group->name }}</strong><small>{{ $group->description ?: 'Organizational group' }}</small></span><span class="ticket-state-pill {{ $group->is_active ? 'is-active' : 'is-inactive' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></article>@empty<p class="user-detail-empty">This account is not assigned to any user groups.</p>@endforelse</div>
            <p class="user-group-detail-note">Group membership is organizational only and does not grant roles, permissions, or ticket access.</p>
        </section>
        @endcan
    </div>
</div>
@endsection
