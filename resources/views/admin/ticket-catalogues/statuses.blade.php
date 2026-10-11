@extends('layouts.app')

@section('title', 'Ticket Statuses')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div><p class="ticket-page-kicker">TICKET CONFIGURATION</p><h1>Ticket Statuses</h1><p>Statuses are controlled by the ticket workflow to preserve valid transitions.</p></div>
    </header>
    <nav class="ticket-page-tabs" aria-label="Ticket configuration pages">
        <a href="{{ route('admin.ticket-catalogues.categories.index') }}">Categories</a>
        <a href="{{ route('admin.ticket-catalogues.priorities.index') }}">Priorities</a>
        <a class="is-active" href="{{ route('admin.ticket-catalogues.statuses.index') }}">Statuses</a>
    </nav>
    <section class="ticket-page-panel" aria-labelledby="status-list-heading">
        <header class="ticket-panel-heading"><div><h2 id="status-list-heading">Workflow Statuses</h2><p>{{ count($statuses) }} statuses in the current workflow</p></div></header>
        <div class="ticket-table-wrap"><table class="ticket-data-table">
            <thead><tr><th scope="col">Status</th><th scope="col">Description</th><th scope="col">Next statuses</th><th scope="col">Configuration</th></tr></thead>
            <tbody>
            @foreach ($statuses as $key => $label)
                <tr>
                    <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $key) }}">{{ $label }}</span></td>
                    <td>{{ $descriptions[$key] }}</td>
                    <td>{{ count($transitions[$key]) ? collect($transitions[$key])->map(fn ($next) => $statuses[$next])->join(', ') : 'No further transition' }}</td>
                    <td><span class="ticket-state-pill is-active">System managed</span></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </section>
</div>
@endsection
