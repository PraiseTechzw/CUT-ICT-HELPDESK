@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="space-y-6">
    <section class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Tickets</h1>
            <p class="mt-2 text-sm text-slate-600">Requesters see their own tickets. Technicians see tickets assigned to them. Helpdesk staff see the triage queue.</p>
        </div>
        @if ($canCreateTicket)
            <a class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('tickets.create') }}">Create a ticket</a>
        @endif
    </section>

    <section aria-label="Ticket list" class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        @if ($tickets->isEmpty())
            <p class="p-6 text-sm text-slate-600">No tickets are available in your queue.</p>
        @else
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3" scope="col">Ticket</th>
                        <th class="px-4 py-3" scope="col">Status</th>
                        <th class="px-4 py-3" scope="col">Category</th>
                        <th class="px-4 py-3" scope="col">Priority</th>
                        @if ($canViewStaffDetails)
                            <th class="px-4 py-3" scope="col">Requester</th>
                            <th class="px-4 py-3" scope="col">Assigned technician</th>
                        @endif
                        <th class="px-4 py-3" scope="col">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td class="px-4 py-3">
                                <a class="font-medium underline underline-offset-2" href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }} · {{ $ticket->subject }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</td>
                            <td class="px-4 py-3">{{ $ticket->category?->name ?? 'Not classified' }}</td>
                            <td class="px-4 py-3">{{ $ticket->priority?->name ?? 'Not set' }}</td>
                            @if ($canViewStaffDetails)
                                <td class="px-4 py-3">{{ $ticket->requester?->name ?? 'Unavailable' }}</td>
                                <td class="px-4 py-3">{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</td>
                            @endif
                            <td class="whitespace-nowrap px-4 py-3">{{ $ticket->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <div>{{ $tickets->links() }}</div>
</div>
@endsection
