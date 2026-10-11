@extends('layouts.app')

@section('title', $scope === 'mine' ? 'My Tickets' : ($scope === 'overdue' ? 'Overdue Tickets' : 'All Tickets'))

@section('content')
@php
    $listAction = $scope === 'mine' ? route('tickets.mine') : ($scope === 'overdue' ? route('tickets.overdue') : route('tickets.index'));
@endphp
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div>
            <p class="ticket-page-kicker">SUPPORT OPERATIONS</p>
            <h1>{{ $scope === 'mine' ? 'My Tickets' : ($scope === 'overdue' ? 'Overdue Tickets' : 'All Tickets') }}</h1>
            <p>{{ $scope === 'overdue' ? 'Open tickets past their assigned due date.' : ($scope === 'mine' ? 'Tickets you requested or are assigned to you.' : 'Track and manage support requests across the helpdesk.') }}</p>
        </div>
        @if ($canCreateTicket)<a class="ticket-primary-button" href="{{ route('tickets.create') }}"><svg data-lucide="ticket-plus" aria-hidden="true"></svg>Create Ticket</a>@endif
    </header>

    <nav class="ticket-page-tabs" aria-label="Ticket queues">
        @if (auth()->user()->can('tickets.view_all'))<a class="{{ $scope === 'all' ? 'is-active' : '' }}" href="{{ route('tickets.index') }}">All Tickets</a>@endif
        @if (auth()->user()->can('tickets.view_own') || auth()->user()->can('tickets.view_assigned'))<a class="{{ $scope === 'mine' ? 'is-active' : '' }}" href="{{ route('tickets.mine') }}">My Tickets</a>@endif
        <a class="{{ $scope === 'overdue' ? 'is-active' : '' }}" href="{{ route('tickets.overdue') }}">Overdue Tickets</a>
    </nav>

    <section class="ticket-summary-grid" aria-label="Ticket queue summary">
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-blue"><svg data-lucide="tickets" aria-hidden="true"></svg></span><span><small>Total tickets</small><strong>{{ number_format($summary['total']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-red"><svg data-lucide="circle-alert" aria-hidden="true"></svg></span><span><small>Open / assigned</small><strong>{{ number_format($summary['open']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-amber"><svg data-lucide="clock-3" aria-hidden="true"></svg></span><span><small>In progress</small><strong>{{ number_format($summary['in_progress']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-green"><svg data-lucide="alarm-clock" aria-hidden="true"></svg></span><span><small>Overdue</small><strong>{{ number_format($summary['overdue']) }}</strong></span></article>
    </section>

    <section class="ticket-page-panel" aria-labelledby="ticket-queue-heading">
        <header class="ticket-panel-heading ticket-queue-heading"><div><h2 id="ticket-queue-heading">{{ $scope === 'overdue' ? 'Overdue Queue' : ($scope === 'mine' ? 'My Queue' : 'Ticket Queue') }}</h2><p>{{ $tickets->total() }} tickets</p></div></header>
        <form class="ticket-filter-bar" method="GET" action="{{ $listAction }}">
            @if ($scope === 'mine')<input type="hidden" name="scope" value="mine">@endif
            <label class="ticket-search-field"><svg data-lucide="search" aria-hidden="true"></svg><input type="search" name="search" value="{{ $search }}" placeholder="Search ticket ID or subject" aria-label="Search tickets"></label>
            <label class="sr-only" for="filter-status">Status</label><select id="filter-status" name="status"><option value="">All statuses</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select>
            <label class="sr-only" for="filter-category">Category</label><select id="filter-category" name="category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected($filters['category'] === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <label class="sr-only" for="filter-priority">Priority</label><select id="filter-priority" name="priority"><option value="">All priorities</option>@foreach ($priorities as $priority)<option value="{{ $priority->id }}" @selected($filters['priority'] === (string) $priority->id)>{{ $priority->name }}</option>@endforeach</select>
            <button class="ticket-secondary-button" type="submit"><svg data-lucide="list-filter" aria-hidden="true"></svg>Filter</button>
            @if ($search !== '' || $filters['status'] !== '' || $filters['category'] !== '' || $filters['priority'] !== '')<a class="ticket-clear-filters" href="{{ $listAction }}">Clear</a>@endif
        </form>

        <div class="ticket-table-wrap">
            <table class="ticket-data-table ticket-queue-table">
                <thead><tr>
                    <th scope="col">Ticket</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Location</th>
                    @if ($canViewStaffDetails)<th scope="col">Requester</th><th scope="col">Technician</th>@endif
                    <th scope="col">Created</th><th scope="col"><span class="sr-only">Actions</span></th>
                </tr></thead>
                <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td><a class="ticket-list-subject" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-list-id">TKT-{{ $ticket->id }}</span><strong>{{ $ticket->subject }}</strong></a></td>
                        <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                        <td>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else—@endif</td>
                        <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                        <td>@if($ticket->room){{ $ticket->room->building?->campus?->name }} · {{ $ticket->room->building?->name }} · {{ $ticket->room->name }}@else—@endif</td>
                        @if ($canViewStaffDetails)<td>{{ $ticket->requester?->name ?? 'Unavailable' }}</td><td>{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</td>@endif
                        <td class="ticket-date-cell">{{ $ticket->created_at?->format('d M Y · H:i') }}@if($ticket->due_at && !in_array($ticket->status, [\App\Support\TicketStatus::RESOLVED, \App\Support\TicketStatus::CLOSED], true) && $ticket->due_at->isPast())<small class="ticket-overdue-note">Overdue {{ $ticket->due_at->diffForHumans() }}</small>@endif</td>
                        <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td class="ticket-table-empty" colspan="{{ $canViewStaffDetails ? 9 : 7 }}">@if($scope === 'overdue') No overdue tickets. Tickets become overdue after a due date is assigned. @elseif($search !== '') No tickets match “{{ $search }}”. @else No tickets in this queue yet.@endif</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <footer class="ticket-table-footer"><span>Showing {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}</span>{{ $tickets->links() }}</footer>
    </section>
</div>
@endsection
