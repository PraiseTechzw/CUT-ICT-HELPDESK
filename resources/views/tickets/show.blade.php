@extends('layouts.app')

@section('title', 'Ticket #'.$ticket->id)

@section('content')
<div class="space-y-6">
    <section class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm text-slate-600">Ticket #{{ $ticket->id }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $ticket->subject }}</h1>
            <p class="mt-2 text-sm text-slate-600">Status: <span class="font-medium text-slate-900">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></p>
        </div>
        <a class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50" href="{{ route('tickets.index') }}">Back to tickets</a>
    </section>

    <section aria-labelledby="ticket-details-heading" class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 lg:grid-cols-3">
        <div class="space-y-3 lg:col-span-2">
            <h2 class="text-lg font-semibold" id="ticket-details-heading">Issue description</h2>
            <div class="whitespace-pre-wrap break-words text-sm leading-6">{{ $ticket->description }}</div>
        </div>
        <dl class="space-y-3 border-t border-slate-200 pt-4 text-sm lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
            <div><dt class="font-medium text-slate-700">Requester</dt><dd>{{ $ticket->requester?->name ?? 'Unavailable' }}</dd></div>
            <div><dt class="font-medium text-slate-700">Category</dt><dd>{{ $ticket->category?->name ?? 'Not classified' }}</dd></div>
            <div><dt class="font-medium text-slate-700">Priority</dt><dd>{{ $ticket->priority?->name ?? 'Not set' }}</dd></div>
            @if ($canViewStaffDetails)
                <div><dt class="font-medium text-slate-700">Assigned technician</dt><dd>{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</dd></div>
            @endif
            <div><dt class="font-medium text-slate-700">Created</dt><dd>{{ $ticket->created_at?->format('Y-m-d H:i') }}</dd></div>
        </dl>
    </section>

    @if ($canAssign || count($allowedTransitions) > 0 || $canReopen)
        <section aria-labelledby="workflow-heading" class="grid gap-5 rounded-xl border border-slate-200 bg-white p-6 md:grid-cols-2">
            <h2 class="text-lg font-semibold md:col-span-2" id="workflow-heading">Workflow</h2>
            @if ($canAssign)
                <form class="space-y-3" method="POST" action="{{ route('tickets.assignment.update', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium" for="technician_id">Assign to an individual technician</label>
                    <select class="w-full rounded-md border border-slate-300 px-3 py-2" id="technician_id" name="technician_id" required>
                        @if ($ticket->status === \App\Support\TicketStatus::ASSIGNED)
                            <option value="">Return to the unassigned queue</option>
                        @elseif ($ticket->assigned_technician_id === null)
                            <option value="" disabled selected>Choose an active technician</option>
                        @endif
                        @if ($ticket->assignedTechnician && ! $technicians->contains('id', $ticket->assignedTechnician->id))
                            <option value="{{ $ticket->assignedTechnician->id }}" selected disabled>{{ $ticket->assignedTechnician->name }} (inactive or unavailable)</option>
                        @endif
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}" @selected($ticket->assigned_technician_id === $technician->id)>{{ $technician->name }}</option>
                        @endforeach
                    </select>
                    <x-field-error field="technician_id" />
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50" type="submit">Save assignment</button>
                </form>
            @endif

            @if (count($allowedTransitions) > 0)
                <form class="space-y-3" method="POST" action="{{ route('tickets.status.update', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium" for="status">Change status</label>
                    <select class="w-full rounded-md border border-slate-300 px-3 py-2" id="status" name="status" required>
                        @foreach ($allowedTransitions as $nextStatus)
                            <option value="{{ $nextStatus }}">{{ $statusLabels[$nextStatus] }}</option>
                        @endforeach
                    </select>
                    <x-field-error field="status" />
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50" type="submit">Update status</button>
                </form>
            @endif

            @if ($canReopen)
                <form method="POST" action="{{ route('tickets.reopen', $ticket) }}">
                    @csrf
                    <button class="rounded-md border border-amber-400 px-3 py-2 text-sm font-medium text-amber-900 hover:bg-amber-50" type="submit">Reopen ticket</button>
                    <p class="mt-2 text-xs text-slate-600">Reopening clears the previous technician assignment and returns the ticket to the open queue.</p>
                </form>
            @endif
        </section>
    @endif

    <section aria-labelledby="conversation-heading" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold" id="conversation-heading">Conversation and work notes</h2>
        @forelse ($comments as $comment)
            <article class="rounded-lg border {{ $comment->visibility === 'internal' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50' }} p-4">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                    <span class="font-medium">{{ $comment->visibility === 'internal' ? 'Internal note' : 'Reply' }} · {{ $canViewStaffDetails ? ($comment->author?->name ?? 'Unavailable') : ($comment->author_id === auth()->id() ? 'You' : 'Support team') }}</span>
                    <time datetime="{{ $comment->created_at?->toIso8601String() }}">{{ $comment->created_at?->format('Y-m-d H:i') }}</time>
                </div>
                <div class="mt-3 whitespace-pre-wrap break-words text-sm leading-6">{{ $comment->body }}</div>
            </article>
        @empty
            <p class="text-sm text-slate-600">There are no replies yet.</p>
        @endforelse

        @if ($canCommentPublic)
            <form class="space-y-3 border-t border-slate-200 pt-5" method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                @csrf
                <input type="hidden" name="visibility" value="public">
                <label class="block text-sm font-medium" for="public-body">{{ auth()->id() === $ticket->requester_id ? 'Reply to support' : 'Requester-visible reply' }}</label>
                <textarea class="w-full rounded-md border border-slate-300 px-3 py-2" id="public-body" name="body" rows="4" maxlength="10000" required></textarea>
                <x-field-error field="body" />
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Send reply</button>
            </form>
        @endif

        @if ($canCommentInternal)
            <form class="space-y-3 border-t border-amber-200 pt-5" method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                @csrf
                <input type="hidden" name="visibility" value="internal">
                <label class="block text-sm font-medium" for="internal-body">Internal work note</label>
                <textarea class="w-full rounded-md border border-amber-300 px-3 py-2" id="internal-body" name="body" rows="4" maxlength="10000" required></textarea>
                <x-field-error field="body" />
                <button class="rounded-md border border-amber-400 px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-50" type="submit">Add internal note</button>
                <p class="text-xs text-slate-600">Internal notes are not visible to requesters.</p>
            </form>
        @endif
    </section>

    <section aria-labelledby="status-history-heading" class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold" id="status-history-heading">Status history</h2>
        <ol class="mt-4 space-y-3">
            @forelse ($statusHistory as $event)
                <li class="border-l-2 border-slate-300 pl-4 text-sm">
                    <span class="font-medium">{{ $event->from_status ? ($statusLabels[$event->from_status] ?? $event->from_status) : 'Created' }} → {{ $statusLabels[$event->to_status] ?? $event->to_status }}</span>
                    <span class="text-slate-600"> · {{ $event->created_at?->format('Y-m-d H:i') }}{{ $canViewStaffDetails ? ' · '.($event->actor?->name ?? 'Unavailable') : '' }}</span>
                </li>
            @empty
                <li class="text-sm text-slate-600">No status changes recorded.</li>
            @endforelse
        </ol>
    </section>

    @if ($canViewStaffDetails)
        <section aria-labelledby="assignment-history-heading" class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold" id="assignment-history-heading">Assignment history</h2>
            <ol class="mt-4 space-y-3">
                @forelse ($assignmentHistory as $event)
                    <li class="border-l-2 border-slate-300 pl-4 text-sm">
                        {{ $event->fromTechnician?->name ?? 'Unassigned' }} → {{ $event->toTechnician?->name ?? 'Unassigned' }}
                        <span class="text-slate-600"> · {{ $event->created_at?->format('Y-m-d H:i') }} · {{ $event->actor?->name ?? 'Unavailable' }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-600">No assignments recorded.</li>
                @endforelse
            </ol>
        </section>
    @endif
</div>
@endsection
