@extends('layouts.app')

@section('title', 'Ticket #'.$ticket->id)

@section('content')
<div class="ticket-workspace ticket-detail-page">
    <section class="ticket-page-header">
        <div>
            <p class="ticket-page-kicker">TICKETS / TKT-{{ $ticket->id }}</p>
            <h1>{{ $ticket->subject }}</h1>
            <span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        </div>
        <div class="ticket-header-actions">
            @if ($canAssign)<a class="ticket-secondary-button" href="{{ route('tickets.assignment.edit', $ticket) }}"><svg data-lucide="user-round-cog" aria-hidden="true"></svg>Assign Technician</a>@endif
            <a class="ticket-secondary-button" href="{{ route('tickets.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Back to tickets</a>
        </div>
    </section>

    <div class="ticket-detail-grid">
        <div class="ticket-detail-main">
            <section aria-labelledby="ticket-details-heading" class="ticket-page-panel">
                <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">REQUEST DETAILS</p><h2 id="ticket-details-heading">Issue Description</h2></div></header>
                <div class="ticket-description-body">{{ $ticket->description }}</div>
            </section>

            <section aria-labelledby="conversation-heading" class="ticket-page-panel ticket-conversation-panel">
                <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">DISCUSSION</p><h2 id="conversation-heading">Conversation and Work Notes</h2></div><span class="ticket-count-label">{{ $comments->count() }} updates</span></header>
                <div class="ticket-conversation-list">
                @forelse ($comments as $comment)
                    <article class="ticket-comment {{ $comment->visibility === 'internal' ? 'is-internal' : '' }}">
                        <div class="ticket-comment-meta">
                            <span class="ticket-comment-avatar" aria-hidden="true">{{ mb_substr($comment->author?->name ?? 'S', 0, 1) }}</span>
                            <strong>{{ $comment->visibility === 'internal' ? 'Internal note' : 'Reply' }} · {{ $canViewStaffDetails ? ($comment->author?->name ?? 'Unavailable') : ($comment->author_id === auth()->id() ? 'You' : 'Support team') }}</strong>
                            <time datetime="{{ $comment->created_at?->toIso8601String() }}">{{ $comment->created_at?->format('d M Y · H:i') }}</time>
                        </div>
                        <div class="ticket-comment-body">{{ $comment->body }}</div>
                    </article>
                @empty
                    <p class="ticket-empty-state">There are no replies yet.</p>
                @endforelse
                </div>

                @if ($canCommentPublic)
                    <form class="ticket-reply-form" method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                        @csrf
                        <input type="hidden" name="visibility" value="public">
                        <label for="public-body">{{ auth()->id() === $ticket->requester_id ? 'Reply to support' : 'Requester-visible reply' }}</label>
                        <textarea id="public-body" name="body" rows="4" maxlength="10000" required placeholder="Write a reply..."></textarea>
                        <x-field-error field="body" />
                        <button class="ticket-primary-button" type="submit"><svg data-lucide="send" aria-hidden="true"></svg>Send Reply</button>
                    </form>
                @endif

                @if ($canCommentInternal)
                    <form class="ticket-reply-form is-internal" method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                        @csrf
                        <input type="hidden" name="visibility" value="internal">
                        <label for="internal-body">Internal Work Note</label>
                        <textarea id="internal-body" name="body" rows="4" maxlength="10000" required placeholder="Add a note for support staff..."></textarea>
                        <x-field-error field="body" />
                        <button class="ticket-secondary-button" type="submit"><svg data-lucide="notebook-pen" aria-hidden="true"></svg>Add Internal Note</button>
                    </form>
                @endif
            </section>
        </div>

        <aside class="ticket-detail-side">
            <section class="ticket-page-panel" aria-labelledby="ticket-info-heading">
                <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">OVERVIEW</p><h2 id="ticket-info-heading">Ticket Information</h2></div></header>
                <dl class="ticket-detail-meta">
                    <div><dt>Requester</dt><dd>{{ $canViewStaffDetails ? ($ticket->requester?->name ?? 'Unavailable') : ($ticket->requester_id === auth()->id() ? 'You' : 'Support requester') }}</dd></div>
                    <div><dt>Category</dt><dd>{{ $ticket->category?->name ?? 'Not classified' }}</dd></div>
                    <div><dt>Priority</dt><dd>@if($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }}</span>@else Not set @endif</dd></div>
                    <div><dt>Location</dt><dd>@if($ticket->room){{ $ticket->room->building?->campus?->name }} · {{ $ticket->room->building?->name }} · {{ $ticket->room->name }}@else Not recorded @endif</dd></div>
                    <div><dt>Created</dt><dd>{{ $ticket->created_at?->format('d M Y · H:i') }}</dd></div>
                    <div><dt>Due date</dt><dd>{{ $ticket->due_at?->format('d M Y · H:i') ?? 'Not set' }}</dd></div>
                    @if ($canViewStaffDetails)<div><dt>Assigned technician</dt><dd>{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</dd></div>@endif
                </dl>
            </section>

            @if (count($allowedTransitions) > 0 || $canReopen)
                <section class="ticket-page-panel" aria-labelledby="workflow-heading">
                    <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">WORKFLOW</p><h2 id="workflow-heading">Update Ticket</h2></div></header>
            @if (count($allowedTransitions) > 0)
                <form class="ticket-reply-form" method="POST" action="{{ route('tickets.status.update', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <label for="status">Change status</label>
                    <select id="status" name="status" required>
                        @foreach ($allowedTransitions as $nextStatus)
                            <option value="{{ $nextStatus }}">{{ $statusLabels[$nextStatus] }}</option>
                        @endforeach
                    </select>
                    <x-field-error field="status" />
                    <button class="ticket-primary-button" type="submit">Update Status</button>
                </form>
            @endif

            @if ($canReopen)
                <form class="ticket-reply-form" method="POST" action="{{ route('tickets.reopen', $ticket) }}">
                    @csrf
                    <button class="ticket-secondary-button" type="submit">Reopen Ticket</button>
                    <p class="ticket-field-help">Reopening clears the previous technician assignment and returns the ticket to the open queue.</p>
                </form>
            @endif
                </section>
            @endif
        </aside>
    </div>

    <section class="ticket-page-panel" aria-labelledby="status-history-heading">
        <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">TICKET TIMELINE</p><h2 id="status-history-heading">Status History</h2></div></header>
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
        <section class="ticket-page-panel" aria-labelledby="assignment-history-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">TICKET TIMELINE</p><h2 id="assignment-history-heading">Assignment History</h2></div></header>
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
