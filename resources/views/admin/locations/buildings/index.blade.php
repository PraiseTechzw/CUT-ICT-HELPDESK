@extends('layouts.app')
@section('title', 'Buildings')
@section('content')
<div class="location-admin-page">
    <header class="location-admin-header"><div><p class="ticket-page-kicker">LOCATION MANAGEMENT</p><h1>Buildings</h1><p>Maintain campus buildings and their optional operational zones.</p></div>@can('buildings.manage')<a class="ticket-primary-button" href="{{ route('admin.buildings.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Add Building</a>@endcan</header>
    <section class="ticket-page-panel location-list-panel" aria-labelledby="buildings-heading"><header class="location-list-heading"><div><h2 id="buildings-heading">Building Directory</h2><p>{{ number_format($buildings->total()) }} {{ $buildings->total() === 1 ? 'building' : 'buildings' }} configured</p></div><span class="location-count-badge is-amber"><svg data-lucide="building-2" aria-hidden="true"></svg>{{ number_format($buildings->total()) }}</span></header>
    <div class="ticket-table-wrap"><table class="ticket-data-table location-data-table"><thead><tr><th scope="col">Building</th><th scope="col">Campus</th><th scope="col">Zone</th><th scope="col">Code</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
    @forelse($buildings as $building)<tr><td><span class="location-name-cell"><span class="location-row-icon is-amber"><svg data-lucide="building-2" aria-hidden="true"></svg></span><span><strong>{{ $building->name }}</strong><small>{{ $building->description ?: 'Campus building' }}</small></span></span></td><td>{{ $building->campus?->name ?? '—' }}</td><td>{{ $building->zone?->name ?? 'Unassigned' }}</td><td><code class="location-code">{{ $building->code }}</code></td><td><span class="ticket-state-pill {{ $building->is_active ? 'is-active' : 'is-inactive' }}">{{ $building->is_active ? 'Active' : 'Inactive' }}</span></td><td>@can('buildings.manage')<a class="location-view-action" href="{{ route('admin.buildings.edit', $building) }}">Edit</a>@endcan</td></tr>
    @empty<tr><td colspan="6" class="ticket-table-empty">No buildings configured yet.</td></tr>@endforelse
    </tbody></table></div><footer class="location-table-footer"><span>Showing {{ $buildings->firstItem() ?? 0 }}–{{ $buildings->lastItem() ?? 0 }} of {{ $buildings->total() }}</span>{{ $buildings->links() }}</footer></section>
</div>
@endsection
