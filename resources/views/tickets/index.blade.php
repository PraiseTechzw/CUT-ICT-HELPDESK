@extends('layouts.app')

@section('title', $scope === 'mine' ? 'My Tickets' : ($scope === 'overdue' ? 'Overdue Tickets' : 'All Tickets'))

@section('content')
@php
    $listAction = $scope === 'mine' ? route('tickets.mine') : ($scope === 'overdue' ? route('tickets.overdue') : route('tickets.index'));
    $mineStatusTabs = [
        '' => 'All',
        \App\Support\TicketStatus::OPEN => 'Open',
        \App\Support\TicketStatus::IN_PROGRESS => 'In Progress',
        \App\Support\TicketStatus::RESOLVED => 'Resolved',
        \App\Support\TicketStatus::CLOSED => 'Closed',
    ];
@endphp
<div class="ticket-workspace">
    <header class="ticket-page-header {{ $scope === 'all' ? 'ticket-all-header' : '' }}">
        <div>
            @if ($scope !== 'all')<p class="ticket-page-kicker">SUPPORT OPERATIONS</p>@endif
            <h1>{{ $scope === 'mine' ? 'My Tickets' : ($scope === 'overdue' ? 'Overdue Tickets' : 'All Tickets') }}</h1>
            <p>{{ $scope === 'overdue' ? 'Open tickets past their assigned due date.' : ($scope === 'mine' ? 'View and track tickets you requested or are assigned to you.' : 'View and manage all ICT helpdesk tickets.') }}</p>
        </div>
        @if ($canCreateTicket && $scope !== 'overdue')<a class="ticket-primary-button" href="{{ route('tickets.create') }}"><svg data-lucide="ticket-plus" aria-hidden="true"></svg>Create Ticket</a>@endif
    </header>

    @if ($scope === 'mine')
    <nav class="ticket-page-tabs ticket-status-tabs" aria-label="Filter my tickets by status">
        @foreach ($mineStatusTabs as $tabStatus => $tabLabel)
            @php
                $tabQuery = request()->query();
                unset($tabQuery['page'], $tabQuery['scope']);
                if ($tabStatus === '') {
                    unset($tabQuery['status']);
                } else {
                    $tabQuery['status'] = $tabStatus;
                }
            @endphp
            <a class="{{ $filters['status'] === $tabStatus ? 'is-active' : '' }}" @if ($filters['status'] === $tabStatus) aria-current="page" @endif href="{{ route('tickets.mine', $tabQuery) }}">{{ $tabLabel }}</a>
        @endforeach
    </nav>
    @endif

    @if ($scope === 'all')
    <section class="ticket-summary-grid ticket-summary-grid-all" aria-label="Ticket status summary">
        <article class="ticket-summary-card ticket-summary-total"><span class="ticket-summary-icon"><svg data-lucide="tickets" aria-hidden="true"></svg></span><span><small>Total Tickets</small><strong>{{ number_format($summary['total']) }}</strong><em>All requests</em></span></article>
        <article class="ticket-summary-card ticket-summary-open"><span class="ticket-summary-icon"><svg data-lucide="circle-alert" aria-hidden="true"></svg></span><span><small>Open</small><strong>{{ number_format($summary['open']) }}</strong><em>Awaiting action</em></span></article>
        <article class="ticket-summary-card ticket-summary-progress"><span class="ticket-summary-icon"><svg data-lucide="clock-3" aria-hidden="true"></svg></span><span><small>In Progress</small><strong>{{ number_format($summary['in_progress']) }}</strong><em>Being handled</em></span></article>
        <article class="ticket-summary-card ticket-summary-resolved"><span class="ticket-summary-icon"><svg data-lucide="circle-check" aria-hidden="true"></svg></span><span><small>Resolved</small><strong>{{ number_format($summary['resolved']) }}</strong><em>Solution provided</em></span></article>
        <article class="ticket-summary-card ticket-summary-closed"><span class="ticket-summary-icon"><svg data-lucide="archive" aria-hidden="true"></svg></span><span><small>Closed</small><strong>{{ number_format($summary['closed']) }}</strong><em>Completed</em></span></article>
    </section>
    @elseif ($scope !== 'mine' && $scope !== 'overdue')
    <section class="ticket-summary-grid" aria-label="Ticket queue summary">
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-blue"><svg data-lucide="tickets" aria-hidden="true"></svg></span><span><small>Total tickets</small><strong>{{ number_format($summary['total']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-red"><svg data-lucide="circle-alert" aria-hidden="true"></svg></span><span><small>Open / assigned</small><strong>{{ number_format($summary['open']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-amber"><svg data-lucide="clock-3" aria-hidden="true"></svg></span><span><small>In progress</small><strong>{{ number_format($summary['in_progress']) }}</strong></span></article>
        <article class="ticket-summary-card"><span class="ticket-summary-icon ticket-summary-green"><svg data-lucide="alarm-clock" aria-hidden="true"></svg></span><span><small>Overdue</small><strong>{{ number_format($summary['overdue']) }}</strong></span></article>
    </section>
    @endif

    <section class="ticket-page-panel" aria-labelledby="ticket-queue-heading">
        @if ($scope === 'all')<header class="ticket-panel-heading ticket-queue-heading"><div><h2 id="ticket-queue-heading">All Tickets</h2><p>{{ number_format($tickets->total()) }} {{ $tickets->total() === 1 ? 'ticket' : 'tickets' }}</p></div></header>@endif
        @if ($scope === 'overdue')
            <div class="ticket-overdue-alert" role="status">
                <svg data-lucide="circle-alert" aria-hidden="true"></svg>
                <span>You have {{ number_format($summary['overdue']) }} overdue {{ $summary['overdue'] === 1 ? 'ticket that requires' : 'tickets that require' }} attention.</span>
            </div>
        @endif
        <form class="ticket-filter-bar {{ $scope === 'all' ? 'ticket-filter-bar-all' : '' }} {{ $scope === 'mine' ? 'ticket-filter-bar-mine' : '' }} {{ $scope === 'overdue' ? 'ticket-filter-bar-overdue' : '' }}" method="GET" action="{{ $listAction }}">
            @if ($scope === 'mine')<input type="hidden" name="scope" value="mine">@endif
            @if ($scope !== 'overdue')<label class="ticket-search-field"><svg data-lucide="search" aria-hidden="true"></svg><input type="search" name="search" value="{{ $search }}" placeholder="Search tickets..." aria-label="Search tickets"></label>@endif
            @if ($scope === 'overdue')
                <label class="sr-only" for="filter-priority">Priority</label><select id="filter-priority" name="priority"><option value="">All Priorities</option>@foreach ($priorities as $priority)<option value="{{ $priority->id }}" @selected($filters['priority'] === (string) $priority->id)>{{ $priority->name }}</option>@endforeach</select>
                <label class="sr-only" for="filter-category">Category</label><select id="filter-category" name="category"><option value="">All Categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected($filters['category'] === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            @else
                <label class="sr-only" for="filter-category">Category</label><select id="filter-category" name="category"><option value="">All Categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected($filters['category'] === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                <label class="sr-only" for="filter-priority">Priority</label><select id="filter-priority" name="priority"><option value="">All Priorities</option>@foreach ($priorities as $priority)<option value="{{ $priority->id }}" @selected($filters['priority'] === (string) $priority->id)>{{ $priority->name }}</option>@endforeach</select>
            @endif
            @if ($scope !== 'overdue')<label class="sr-only" for="filter-status">Status</label><select id="filter-status" name="status"><option value="">All Statuses</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select>@endif
            <button class="ticket-secondary-button" type="submit"><svg data-lucide="list-filter" aria-hidden="true"></svg><span>{{ $scope === 'overdue' ? 'Filter' : 'Filters' }}</span></button>
            @if ($search !== '' || $filters['status'] !== '' || $filters['category'] !== '' || $filters['priority'] !== '')<a class="ticket-clear-filters" href="{{ $listAction }}">Clear</a>@endif
        </form>

        <div class="ticket-table-wrap">
            <table class="ticket-data-table ticket-queue-table {{ $scope === 'all' ? 'ticket-all-table' : '' }} {{ $scope === 'mine' ? 'ticket-my-table' : '' }} {{ $scope === 'overdue' ? 'ticket-overdue-table' : '' }}">
                <thead><tr>
                    @if ($scope === 'all')
                        <th scope="col">#</th><th scope="col">Ticket ID</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Created</th><th scope="col">Actions</th>
                    @elseif ($scope === 'mine')
                        <th scope="col">#</th><th scope="col">Ticket ID</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Created</th><th scope="col">Actions</th>
                    @elseif ($scope === 'overdue')
                        <th scope="col">#</th><th scope="col">Ticket ID</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Days Overdue</th><th scope="col">Action</th>
                    @else
                        <th scope="col">Ticket</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Location</th>
                        @if ($canViewStaffDetails)<th scope="col">Requester</th><th scope="col">Technician</th>@endif
                        <th scope="col">Created</th><th scope="col"><span class="sr-only">Actions</span></th>
                    @endif
                </tr></thead>
                <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        @if ($scope === 'all')
                            <td class="ticket-row-number">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td><a class="ticket-list-subject" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-list-id">TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span><strong>{{ $ticket->subject }}</strong></a></td>
                            <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                            <td>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else—@endif</td>
                            <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                            <td class="ticket-date-cell"><time datetime="{{ $ticket->created_at?->toIso8601String() }}">{{ $ticket->created_at?->format('d M Y · H:i') }}</time>@if($ticket->due_at && !in_array($ticket->status, [\App\Support\TicketStatus::RESOLVED, \App\Support\TicketStatus::CLOSED], true) && $ticket->due_at->isPast())<small class="ticket-overdue-note">Overdue {{ $ticket->due_at->diffForHumans() }}</small>@endif</td>
                            <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}">View</a></td>
                        @elseif ($scope === 'mine')
                            <td class="ticket-row-number">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td><a class="ticket-list-subject" href="{{ route('tickets.show', $ticket) }}" aria-label="View TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}: {{ $ticket->subject }}"><span class="ticket-list-id">TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span><strong>{{ $ticket->subject }}</strong></a></td>
                            <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                            <td>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else—@endif</td>
                            <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                            <td class="ticket-date-cell"><time datetime="{{ $ticket->created_at?->toIso8601String() }}">{{ $ticket->created_at?->format('d M Y · H:i') }}</time></td>
                            <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}">View</a></td>
                        @elseif ($scope === 'overdue')
                            <td class="ticket-row-number">{{ ($tickets->firstItem() ?? 1) + $loop->index }}</td>
                            <td><a class="ticket-list-subject" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-list-id">TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span><strong>{{ $ticket->subject }}</strong></a></td>
                            <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                            <td>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else—@endif</td>
                            <td class="ticket-days-overdue"><strong>{{ max(1, (int) ceil($ticket->due_at->diffInHours(now()) / 24)) }}</strong><small>days</small></td>
                            <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}">View</a></td>
                        @else
                            <td><a class="ticket-list-subject" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-list-id">TKT-{{ $ticket->id }}</span><strong>{{ $ticket->subject }}</strong></a></td>
                            <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                            <td>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else—@endif</td>
                            <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                            <td>@if($ticket->room){{ $ticket->room->building?->campus?->name }} · {{ $ticket->room->building?->name }} · {{ $ticket->room->name }}@else—@endif</td>
                            @if ($canViewStaffDetails)<td>{{ $ticket->requester?->name ?? 'Unavailable' }}</td><td>{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</td>@endif
                            <td class="ticket-date-cell">{{ $ticket->created_at?->format('d M Y · H:i') }}@if($ticket->due_at && !in_array($ticket->status, [\App\Support\TicketStatus::RESOLVED, \App\Support\TicketStatus::CLOSED], true) && $ticket->due_at->isPast())<small class="ticket-overdue-note">Overdue {{ $ticket->due_at->diffForHumans() }}</small>@endif</td>
                            <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}">View</a></td>
                        @endif
                    </tr>
                @empty
                    <tr><td class="ticket-table-empty" colspan="{{ $scope === 'all' || $scope === 'mine' ? 7 : ($scope === 'overdue' ? 6 : ($canViewStaffDetails ? 9 : 7)) }}">@if($scope === 'overdue') No overdue tickets. Tickets become overdue after a due date is assigned. @elseif($search !== '') No tickets match “{{ $search }}”. @else No tickets in this queue yet.@endif</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <footer class="ticket-table-footer"><span>Showing {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}</span>{{ $tickets->links() }}</footer>
    </section>
</div>
@endsection
