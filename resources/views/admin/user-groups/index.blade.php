@extends('layouts.app')

@section('title', 'User Groups')

@section('content')
<div class="ticket-workspace user-groups-page">
    <header class="ticket-page-header ticket-catalogue-header">
        <div><h1>User Groups</h1><p>Organize users into groups for easier management and access control.</p></div>
        <details class="catalogue-add-menu" id="create-user-group" @if ($errors->any() && old('_method') !== 'PATCH') open @endif>
            <summary class="ticket-primary-button"><svg data-lucide="plus" aria-hidden="true"></svg><span>Create Group</span></summary>
            <form class="catalogue-add-popover" method="POST" action="{{ route('admin.user-groups.store') }}">
                @csrf
                <h2>Create user group</h2>
                <label for="group-name">Group name<input id="group-name" name="name" value="{{ old('name') }}" maxlength="100" required placeholder="For example, Finance"></label>
                <x-field-error field="name" />
                <label for="group-description">Description<input id="group-description" name="description" value="{{ old('description') }}" maxlength="500" placeholder="Describe this group"></label>
                <x-field-error field="description" />
                <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')> Active</label>
                <button class="ticket-primary-button" type="submit">Save Group</button>
            </form>
        </details>
    </header>

    <section class="ticket-page-panel" aria-labelledby="user-groups-heading">
        <div class="ticket-table-wrap"><table class="ticket-data-table catalogue-data-table user-groups-table">
            <caption class="sr-only" id="user-groups-heading">{{ $groups->count() }} configured user groups</caption>
            <thead><tr><th scope="col">#</th><th scope="col">Group Name</th><th scope="col">Description</th><th scope="col">Members</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                    <td><strong>{{ $group->name }}</strong><small>{{ $group->slug }}</small></td>
                    <td>{{ $group->description ?: '—' }}</td>
                    <td>{{ number_format($group->users_count) }}</td>
                    <td><span class="ticket-state-pill {{ $group->is_active ? 'is-active' : 'is-inactive' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="user-group-actions">
                        <details class="catalogue-edit"><summary aria-label="Edit {{ $group->name }}"><svg data-lucide="pencil" aria-hidden="true"></svg><span>Edit</span></summary>
                            <form class="catalogue-edit-form" method="POST" action="{{ route('admin.user-groups.update', $group) }}">
                                @csrf @method('PATCH')
                                <label>Name<input name="name" value="{{ $group->name }}" required maxlength="100"></label>
                                <label>Description<input name="description" value="{{ $group->description }}" maxlength="500"></label>
                                <label>Slug<input name="slug" value="{{ $group->slug }}" required maxlength="100"></label>
                                <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked($group->is_active)> Active</label>
                                <button class="ticket-primary-button" type="submit">Save changes</button>
                            </form>
                        </details>
                        <details class="catalogue-edit user-group-members-edit"><summary aria-label="Manage members for {{ $group->name }}"><svg data-lucide="users-round" aria-hidden="true"></svg><span>Members</span></summary>
                            <form class="catalogue-edit-form user-group-members-form" method="POST" action="{{ route('admin.user-groups.members.update', $group) }}">
                                @csrf @method('PUT')
                                <strong>Group members</strong>
                                <label class="user-group-member-search"><svg data-lucide="search" aria-hidden="true"></svg><input type="search" placeholder="Search users..." aria-label="Search users for {{ $group->name }}"></label>
                                <div class="user-group-member-list">
                                    @foreach ($availableUsers as $user)
                                        <label class="user-group-member-option" data-user-name="{{ strtolower($user->name) }}" data-user-email="{{ strtolower($user->email) }}">
                                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" @checked($group->users->contains($user->id))>
                                            <span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
                                        </label>
                                    @endforeach
                                    <p class="user-group-member-empty" hidden>No users match this search.</p>
                                </div>
                                <button class="ticket-primary-button" type="submit">Save Members</button>
                            </form>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td class="ticket-table-empty" colspan="6">No user groups have been configured.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <p class="role-catalogue-note"><svg data-lucide="info" aria-hidden="true"></svg>User groups are organizational labels only. Group membership does not grant roles, permissions, or ticket access.</p>
    </section>
</div>
@endsection
