@extends('layouts.app')

@section('title', 'Ticket Statuses')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header ticket-catalogue-header">
        <div><h1>Ticket Statuses</h1><p>Manage workflow states and their allowed transitions.</p></div>
    </header>
    <section class="ticket-page-panel" aria-labelledby="status-list-heading">
        <div class="ticket-table-wrap"><table class="ticket-data-table catalogue-data-table ticket-status-catalogue-table">
            <caption class="sr-only" id="status-list-heading">{{ count($statuses) }} workflow statuses</caption>
            <thead><tr><th scope="col">#</th><th scope="col">Status Name</th><th scope="col">Description</th><th scope="col">Color</th><th scope="col">Next Statuses</th><th scope="col">Configuration</th></tr></thead>
            <tbody>
            @foreach ($statuses as $key => $label)
                <tr>
                    @php
                        $statusColor = match ($key) {
                            \App\Support\TicketStatus::OPEN => '#f3b638',
                            \App\Support\TicketStatus::ASSIGNED, \App\Support\TicketStatus::IN_PROGRESS => '#2780cf',
                            \App\Support\TicketStatus::WAITING_FOR_REQUESTER => '#ec9447',
                            \App\Support\TicketStatus::RESOLVED => '#2f9e69',
                            \App\Support\TicketStatus::CLOSED => '#8292a2',
                            default => '#8292a2',
                        };
                    @endphp
                    <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                    <td><strong>{{ $label }}</strong></td>
                    <td>{{ $descriptions[$key] }}</td>
                    <td><span class="priority-color-swatch status-color-swatch" style="--priority-color: {{ $statusColor }}" role="img" aria-label="{{ $statusColor }}"></span></td>
                    <td>{{ count($transitions[$key]) ? collect($transitions[$key])->map(fn ($next) => $statuses[$next])->join(', ') : 'No further transition' }}</td>
                    <td><span class="ticket-state-pill is-inactive">System managed</span></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </section>
</div>
@endsection
