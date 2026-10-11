@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="user-admin-page">
    <header class="user-admin-header">
        <div><p class="ticket-page-kicker">ACCOUNT MANAGEMENT</p><h1>All Users</h1><p>Manage system users, roles, and account status.</p></div>
        @can('users.create')<a class="ticket-primary-button" href="{{ route('admin.users.create') }}"><svg data-lucide="user-round-plus" aria-hidden="true"></svg>Add User</a>@endcan
    </header>
    <section class="user-summary-grid" aria-label="Account summary">
        <article class="user-summary-item"><span class="user-summary-icon is-blue"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><small>Total Users</small><strong>{{ number_format($summary['total']) }}</strong></span></article>
        <article class="user-summary-item"><span class="user-summary-icon is-green"><svg data-lucide="circle-check" aria-hidden="true"></svg></span><span><small>Active</small><strong>{{ number_format($summary['active']) }}</strong></span></article>
        <article class="user-summary-item"><span class="user-summary-icon is-red"><svg data-lucide="circle-alert" aria-hidden="true"></svg></span><span><small>Inactive</small><strong>{{ number_format($summary['inactive']) }}</strong></span></article>
        <article class="user-summary-item"><span class="user-summary-icon is-amber"><svg data-lucide="calendar-days" aria-hidden="true"></svg></span><span><small>New This Month</small><strong>{{ number_format($summary['new_this_month']) }}</strong></span></article>
    </section>

    <section class="ticket-page-panel user-list-panel" aria-labelledby="users-list-heading">
    <header class="user-list-heading"><div><h2 id="users-list-heading">User Directory</h2><p>{{ number_format($users->total()) }} accounts</p></div></header>
    <form method="GET" action="{{ route('admin.users.index') }}" class="user-filter-bar">
        <label class="user-search-field"><svg data-lucide="search" aria-hidden="true"></svg><input id="search" name="search" value="{{ $search }}" maxlength="120" placeholder="Search name or email..." aria-label="Search accounts"></label>
        <label class="sr-only" for="filter-role">Role</label><select id="filter-role" name="role"><option value="">All Roles</option>@foreach ($roles as $role)<option value="{{ $role->slug }}" @selected($roleFilter === $role->slug)>{{ $role->name }}</option>@endforeach</select>
        <label class="sr-only" for="filter-account-status">Status</label><select id="filter-account-status" name="status"><option value="">All Statuses</option><option value="active" @selected($statusFilter === 'active')>Active</option><option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option></select>
        <button class="ticket-secondary-button" type="submit"><svg data-lucide="list-filter" aria-hidden="true"></svg>Filters</button>
        @if ($search !== '' || $roleFilter !== '' || $statusFilter !== '')<a class="ticket-clear-filters" href="{{ route('admin.users.index') }}">Clear</a>@endif
    </form>

    <div class="ticket-table-wrap"><table class="ticket-data-table user-data-table">
            <thead><tr><th scope="col">#</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Created</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="user-row-number">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                    <td><span class="user-name-cell"><span class="user-list-avatar" aria-hidden="true">{{ collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><strong>{{ $user->name }}</strong></span></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="user-role-list">@forelse ($user->roles as $role)<span class="user-role-pill">{{ $role->name }}</span>@empty<span class="user-role-none">No role</span>@endforelse</span></td>
                    <td class="user-created-date">{{ $user->created_at?->format('d M Y · H:i') ?? '—' }}</td>
                    <td><span class="user-account-state {{ $user->is_active ? 'is-active' : 'is-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><a class="user-view-action" href="{{ route('admin.users.show', $user) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="ticket-table-empty">@if($search !== '' || $roleFilter !== '' || $statusFilter !== '')No users match these filters.@else No user accounts found.@endif</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <footer class="user-table-footer"><span>Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</span>{{ $users->links() }}</footer>
    </section>
</div>
@endsection
