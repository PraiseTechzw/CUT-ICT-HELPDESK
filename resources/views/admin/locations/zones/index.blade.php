@extends('layouts.app')
@section('title', 'Operational zones')
@section('content')
<div class="location-admin-page">
    <header class="location-admin-header"><div><p class="ticket-page-kicker">LOCATION MANAGEMENT</p><h1>Operational zones</h1><p>Organize support areas within each campus location.</p></div>@can('zones.manage')<a class="ticket-primary-button" href="{{ route('admin.zones.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Add Zone</a>@endcan</header>
    <section class="ticket-page-panel location-list-panel" aria-labelledby="zones-heading"><header class="location-list-heading"><div><h2 id="zones-heading">Zone Directory</h2><p>{{ number_format($zones->total()) }} {{ $zones->total() === 1 ? 'zone' : 'zones' }} configured</p></div><span class="location-count-badge is-green"><svg data-lucide="map" aria-hidden="true"></svg>{{ number_format($zones->total()) }}</span></header>
    <div class="ticket-table-wrap"><table class="ticket-data-table location-data-table"><thead><tr><th scope="col">Zone</th><th scope="col">Campus</th><th scope="col">Code</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
    @forelse($zones as $zone)<tr><td><span class="location-name-cell"><span class="location-row-icon is-green"><svg data-lucide="map" aria-hidden="true"></svg></span><span><strong>{{ $zone->name }}</strong><small>{{ $zone->description ?: 'Operational support area' }}</small></span></span></td><td>{{ $zone->campus?->name ?? '—' }}</td><td><code class="location-code">{{ $zone->code }}</code></td><td><span class="ticket-state-pill {{ $zone->is_active ? 'is-active' : 'is-inactive' }}">{{ $zone->is_active ? 'Active' : 'Inactive' }}</span></td><td>@can('zones.manage')<a class="location-view-action" href="{{ route('admin.zones.edit', $zone) }}">Edit</a>@endcan</td></tr>
    @empty<tr><td colspan="5" class="ticket-table-empty">No operational zones configured yet.</td></tr>@endforelse
    </tbody></table></div><footer class="location-table-footer"><span>Showing {{ $zones->firstItem() ?? 0 }}–{{ $zones->lastItem() ?? 0 }} of {{ $zones->total() }}</span>{{ $zones->links() }}</footer></section>
</div>
@endsection
