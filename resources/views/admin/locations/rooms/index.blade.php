@extends('layouts.app')
@section('title', 'Rooms')
@section('content')
<div class="location-admin-page">
    <header class="location-admin-header"><div><p class="ticket-page-kicker">LOCATION MANAGEMENT</p><h1>Rooms</h1><p>Manage the selectable rooms used when submitting a support ticket.</p></div>@can('rooms.manage')<a class="ticket-primary-button" href="{{ route('admin.rooms.create') }}"><svg data-lucide="plus" aria-hidden="true"></svg>Add Room</a>@endcan</header>
    <section class="ticket-page-panel location-list-panel" aria-labelledby="rooms-heading"><header class="location-list-heading"><div><h2 id="rooms-heading">Room Directory</h2><p>{{ number_format($rooms->total()) }} {{ $rooms->total() === 1 ? 'room' : 'rooms' }} configured</p></div><span class="location-count-badge is-purple"><svg data-lucide="door-open" aria-hidden="true"></svg>{{ number_format($rooms->total()) }}</span></header>
    <div class="ticket-table-wrap"><table class="ticket-data-table location-data-table"><thead><tr><th scope="col">Room</th><th scope="col">Building</th><th scope="col">Campus</th><th scope="col">Code</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead><tbody>
    @forelse($rooms as $room)<tr><td><span class="location-name-cell"><span class="location-row-icon is-purple"><svg data-lucide="door-open" aria-hidden="true"></svg></span><span><strong>{{ $room->name }}</strong><small>{{ $room->description ?: 'Ticket location' }}</small></span></span></td><td>{{ $room->building?->name ?? '—' }}</td><td>{{ $room->building?->campus?->name ?? '—' }}</td><td><code class="location-code">{{ $room->code }}</code></td><td><span class="ticket-state-pill {{ $room->is_active ? 'is-active' : 'is-inactive' }}">{{ $room->is_active ? 'Active' : 'Inactive' }}</span></td><td>@can('rooms.manage')<a class="location-view-action" href="{{ route('admin.rooms.edit', $room) }}">Edit</a>@endcan</td></tr>
    @empty<tr><td colspan="6" class="ticket-table-empty">No rooms configured yet.</td></tr>@endforelse
    </tbody></table></div><footer class="location-table-footer"><span>Showing {{ $rooms->firstItem() ?? 0 }}–{{ $rooms->lastItem() ?? 0 }} of {{ $rooms->total() }}</span>{{ $rooms->links() }}</footer></section>
</div>
@endsection
