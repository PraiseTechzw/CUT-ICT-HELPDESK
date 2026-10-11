@extends('layouts.app')
@section('title', 'Teams')
@section('content')
<div class="admin-data-page">
    <header class="admin-data-header">
        <div><p class="ticket-page-kicker">TEAM OPERATIONS</p><h1>Teams</h1><p>Organize technicians into clear operational groups without changing ticket permissions or routing.</p></div>
        <a class="ticket-primary-button" href="{{ route('admin.teams.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Add team</a>
    </header>
    <section class="admin-kpi-grid" aria-label="Team summary">
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-blue"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><small>Total teams</small><strong>{{ $teams->total() }}</strong></span></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-green"><svg data-lucide="check-circle-2" aria-hidden="true"></svg></span><span><small>Active teams</small><strong>{{ $teams->getCollection()->where('is_active', true)->count() }}</strong></span></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-amber"><svg data-lucide="user-round-check" aria-hidden="true"></svg></span><span><small>Membership records</small><strong>{{ $teams->sum('memberships_count') }}</strong></span></article>
        <article class="admin-kpi-card"><span class="admin-kpi-icon is-purple"><svg data-lucide="calendar-range" aria-hidden="true"></svg></span><span><small>Deployments</small><strong>—</strong></span></article>
    </section>
    <section class="ticket-page-panel admin-data-panel" aria-labelledby="teams-heading">
        <header class="admin-data-panel-heading"><div><p class="ticket-page-kicker">TEAM OPERATIONS</p><h2 id="teams-heading">Team directory</h2><p>Review team ownership, membership coverage, and current status.</p></div><span class="admin-data-panel-icon"><svg data-lucide="users-round" aria-hidden="true"></svg></span></header>
        <div class="admin-data-toolbar"><label class="admin-data-search"><svg data-lucide="search" aria-hidden="true"></svg><span class="sr-only">Search teams</span><input type="search" placeholder="Search team name or slug..." disabled></label><div class="admin-data-toolbar-actions"><a class="admin-toolbar-link" href="{{ route('admin.team-memberships.index') }}"><svg data-lucide="user-round-check" aria-hidden="true"></svg>Memberships</a><a class="admin-toolbar-link" href="{{ route('admin.technician-deployments.index') }}"><svg data-lucide="calendar-range" aria-hidden="true"></svg>Deployments</a></div></div>
        <div class="admin-data-table-wrap"><table class="admin-data-table"><thead><tr><th scope="col">Team</th><th scope="col">Slug</th><th scope="col">Memberships</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead><tbody>
        @forelse($teams as $team)
            <tr><td><strong>{{ $team->name }}</strong><small>{{ $team->description ?: 'Operational team' }}</small></td><td><code class="admin-code">{{ $team->slug }}</code></td><td>{{ $team->memberships_count }}</td><td><span class="ticket-state-pill {{ $team->is_active ? 'is-active' : 'is-inactive' }}">{{ $team->is_active ? 'Active' : 'Inactive' }}</span></td><td><span class="admin-row-actions"><a href="{{ route('admin.team-memberships.index', ['team_id' => $team->id]) }}">View members</a><a href="{{ route('admin.teams.edit', $team) }}">Edit</a></span></td></tr>
        @empty
            <tr><td colspan="5"><div class="admin-data-empty"><span class="admin-data-empty-icon"><svg data-lucide="users-round" aria-hidden="true"></svg></span><h3>No teams configured yet</h3><p>Create the first team to start organizing technicians and ownership.</p><a class="ticket-secondary-button" href="{{ route('admin.teams.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Create first team</a></div></td></tr>
        @endforelse
        </tbody></table></div>
        <footer class="admin-data-footer"><span>Showing {{ $teams->firstItem() ?? 0 }}–{{ $teams->lastItem() ?? 0 }} of {{ $teams->total() }}</span>{{ $teams->links() }}</footer>
    </section>
</div>
@endsection
