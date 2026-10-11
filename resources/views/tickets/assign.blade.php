@extends('layouts.app')

@section('title', 'Assign Ticket #'.$ticket->id)

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div><p class="ticket-page-kicker">TICKET #{{ $ticket->id }}</p><h1>Assign Technician</h1><p>Choose an owner and set the response due date.</p></div>
        <a class="ticket-secondary-button" href="{{ route('tickets.show', $ticket) }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Back to ticket</a>
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
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">ASSIGNMENT</p><h2 id="assignment-form-heading">Available Technicians</h2><p>Select an active technician for this ticket.</p></div></header>
            @if ($technicians->isEmpty())
                <p class="ticket-inline-notice">No active technician accounts are available to assign.</p>
            @else
                <form method="POST" action="{{ route('tickets.assignment.update', $ticket) }}" class="ticket-assignment-form">
                    @csrf @method('PATCH')
                    <label class="ticket-field-label" for="technician_id">Technician</label>
                    <select id="technician_id" name="technician_id" required>
                        @if ($ticket->status === \App\Support\TicketStatus::ASSIGNED)<option value="">Return to unassigned queue</option>@endif
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}" @selected((string) old('technician_id', $ticket->assigned_technician_id) === (string) $technician->id)>{{ $technician->name }}</option>
                        @endforeach
                    </select>
                    <x-field-error field="technician_id" />
                    <label class="ticket-field-label" for="due_at">Due date and time <span>Optional</span></label>
                    <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', $ticket->due_at?->setTimezone(config('app.timezone'))->format('Y-m-d\\TH:i')) }}">
                    <x-field-error field="due_at" />
                    <p class="ticket-field-help">Overdue tickets are based on this deadline. Leave blank when no due date has been agreed.</p>
                    <div class="ticket-form-actions"><a class="ticket-secondary-button" href="{{ route('tickets.show', $ticket) }}">Cancel</a><button class="ticket-primary-button" type="submit">Assign Ticket</button></div>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
