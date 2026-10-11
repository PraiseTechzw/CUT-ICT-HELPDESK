@extends('layouts.app')
@section('title', 'Campuses')
@section('content')
<div class="location-admin-page">
    <header class="location-admin-header"><div><p class="ticket-page-kicker">LOCATION MANAGEMENT</p><h1>Campuses</h1><p>Manage the campus locations available to the ICT Helpdesk.</p></div>@can('campuses.manage')<a class="ticket-primary-button" href="{{ route('admin.campuses.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Add Campus</a>@endcan</header>
    <section class="ticket-page-panel location-list-panel" aria-labelledby="campuses-heading"><header class="location-list-heading"><div><h2 id="campuses-heading">Campus Directory</h2><p>{{ number_format($campuses->total()) }} {{ $campuses->total() === 1 ? 'campus' : 'campuses' }} configured</p></div><span class="location-count-badge"><svg data-lucide="map-pin" aria-hidden="true"></svg>{{ number_format($campuses->total()) }}</span></header>
    <div class="ticket-table-wrap"><table class="ticket-data-table location-data-table"><thead><tr><th scope="col">Campus</th><th scope="col">Code</th><th scope="col">Description</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
    @forelse($campuses as $campus)<tr><td><span class="location-name-cell"><span class="location-row-icon is-blue"><svg data-lucide="building-2" aria-hidden="true"></svg></span><span><strong>{{ $campus->name }}</strong><small>Campus location</small></span></span></td><td><code class="location-code">{{ $campus->code }}</code></td><td>{{ $campus->description ?: '—' }}</td><td><span class="ticket-state-pill {{ $campus->is_active ? 'is-active' : 'is-inactive' }}">{{ $campus->is_active ? 'Active' : 'Inactive' }}</span></td><td>@can('campuses.manage')<a class="location-view-action" href="{{ route('admin.campuses.edit', $campus) }}">Edit</a>@endcan</td></tr>
    @empty<tr><td colspan="5" class="ticket-table-empty">No campuses configured yet. Add a campus to start building the location hierarchy.</td></tr>@endforelse
    </tbody></table></div><footer class="location-table-footer"><span>Showing {{ $campuses->firstItem() ?? 0 }}–{{ $campuses->lastItem() ?? 0 }} of {{ $campuses->total() }}</span>{{ $campuses->links() }}</footer></section>
</div>
@endsection
