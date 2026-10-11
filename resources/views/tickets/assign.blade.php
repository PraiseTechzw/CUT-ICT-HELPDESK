@extends('layouts.app')

@section('title', 'Assign Ticket #'.$ticket->id)

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header ticket-assignment-header">
        <div><p class="ticket-page-kicker">TICKET ASSIGNMENT · TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</p><h1>Assign Technician</h1><p>Choose an owner and set the response due date.</p></div>
        <a class="ticket-secondary-button" href="{{ route('tickets.show', $ticket) }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg><span>Back to ticket</span></a>
    </header>
    <div class="ticket-assignment-grid">
        <section class="ticket-page-panel ticket-assignment-summary" aria-labelledby="assignment-ticket-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">TICKET DETAILS</p><h2 id="assignment-ticket-heading">{{ $ticket->subject }}</h2></div><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ \App\Support\TicketStatus::labels()[$ticket->status] ?? $ticket->status }}</span></header>
            <dl class="ticket-meta-grid">
                <div><dt>Requester</dt><dd>{{ $ticket->requester?->name ?? 'Unavailable' }}</dd></div>
                <div><dt>Category</dt><dd>{{ $ticket->category?->name ?? 'Not classified' }}</dd></div>
                <div><dt>Priority</dt><dd>{{ $ticket->priority?->name ?? 'Not set' }}</dd></div>
                <div><dt>Location</dt><dd>@if ($ticket->room){{ $ticket->room->building?->campus?->name }} · {{ $ticket->room->building?->name }} · {{ $ticket->room->name }}@else Not recorded @endif</dd></div>
                <div class="ticket-meta-wide"><dt>Issue</dt><dd class="ticket-description-preview">{{ $ticket->description }}</dd></div>
            </dl>
        </section>

        <section class="ticket-page-panel" aria-labelledby="assignment-form-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">ASSIGNMENT</p><h2 id="assignment-form-heading">Available Technicians</h2><p>Select an active technician for this ticket.</p></div><span class="ticket-count-label">{{ $technicians->count() }} available</span></header>
            @if ($technicians->isEmpty())
                <p class="ticket-inline-notice">No active technician accounts are available to assign.</p>
            @else
                <form method="POST" action="{{ route('tickets.assignment.update', $ticket) }}" class="ticket-assignment-form">
                    @csrf @method('PATCH')
                    <label class="ticket-technician-search"><svg data-lucide="search" aria-hidden="true"></svg><input type="search" placeholder="Search technicians..." aria-label="Search technicians" data-technician-search></label>
                    <div class="ticket-technician-options" role="radiogroup" aria-labelledby="assignment-form-heading">
                        @if ($ticket->status === \App\Support\TicketStatus::ASSIGNED)
                            <label class="ticket-technician-option is-unassigned">
                                <input type="radio" name="technician_id" value="" @checked(old('technician_id', $ticket->assigned_technician_id) === '') required>
                                <span class="ticket-technician-avatar" aria-hidden="true"><svg data-lucide="user-round-x" aria-hidden="true"></svg></span>
                                <span class="ticket-technician-copy"><strong>Return to unassigned</strong><small>Remove the current assignment</small></span>
                                <span class="ticket-technician-indicator" aria-hidden="true"></span>
                            </label>
                        @endif
                        @foreach ($technicians as $technician)
                            <label class="ticket-technician-option" data-technician-name="{{ strtolower($technician->name) }}">
                                <input type="radio" name="technician_id" value="{{ $technician->id }}" @checked((string) old('technician_id', $ticket->assigned_technician_id) === (string) $technician->id) required>
                                <span class="ticket-technician-avatar" aria-hidden="true">{{ collect(explode(' ', $technician->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                                <span class="ticket-technician-copy"><strong>{{ $technician->name }}</strong><small>Active technician</small></span>
                                <span class="ticket-technician-indicator" aria-hidden="true"></span>
                            </label>
                        @endforeach
                        <p class="ticket-technician-no-results" data-technician-no-results hidden>No technicians match this search.</p>
                    </div>
                    <x-field-error field="technician_id" />
                    <label class="ticket-field-label" for="due_at">Due date and time <span>Optional</span></label>
                    <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $ticket->due_at?->setTimezone(config('app.timezone'))->format('Y-m-d\\TH:i')) }}">
                    <x-field-error field="due_at" />
                    <p class="ticket-field-help">Overdue tickets are based on this deadline. Leave blank when no due date has been agreed.</p>
                    <div class="ticket-form-actions"><a class="ticket-secondary-button" href="{{ route('tickets.show', $ticket) }}">Cancel</a><button class="ticket-primary-button" type="submit"><svg data-lucide="user-round-cog" aria-hidden="true"></svg>Assign Ticket</button></div>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
