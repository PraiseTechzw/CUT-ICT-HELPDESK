@extends('layouts.app')

@section('title', 'Ticket #'.$ticket->id)

@section('content')
<div class="ticket-workspace ticket-detail-page">
    <header class="ticket-page-header ticket-detail-header">
        <div>
            <p class="ticket-page-kicker">TICKET DETAILS · TKT-{{ $ticket->created_at?->format('Y') }}-{{ str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</p>
            <h1>{{ $ticket->subject }}</h1>
            <div class="ticket-detail-heading-meta">
                <span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                @if ($ticket->priority)<span class="ticket-priority-label" @if($ticket->priority->color) style="--priority-color: {{ $ticket->priority->color }}" @endif>{{ $ticket->priority->name }} priority</span>@endif
                <span class="ticket-detail-created">Created {{ $ticket->created_at?->format('d M Y · H:i') }}</span>
            </div>
        </div>
        <div class="ticket-header-actions">
            @if ($canAssign)<a class="ticket-secondary-button" href="{{ route('tickets.assignment.edit', $ticket) }}"><svg data-lucide="user-round-cog" aria-hidden="true"></svg>Assign Technician</a>@endif
            <a class="ticket-secondary-button" href="{{ route('tickets.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Back to tickets</a>
        </div>
    </header>

    <div class="ticket-detail-grid">
        <div class="ticket-detail-main">
            <section aria-labelledby="ticket-details-heading" class="ticket-page-panel">
                <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">REQUEST DETAILS</p><h2 id="ticket-details-heading">Issue Description</h2></div><span class="ticket-count-label">Ticket description</span></header>
                <div class="ticket-description-body">{{ $ticket->description }}</div>
            </section>

            <section aria-labelledby="conversation-heading" class="ticket-page-panel ticket-conversation-panel">
                <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">DISCUSSION</p><h2 id="conversation-heading">Conversation and Work Notes</h2></div><span class="ticket-count-label">{{ $comments->count() }} updates</span></header>
                <div class="ticket-conversation-list">
                @forelse ($comments as $comment)
                    <article class="ticket-comment {{ $comment->visibility === 'internal' ? 'is-internal' : '' }}">
                        <div class="ticket-comment-meta">
                            <span class="ticket-comment-avatar" aria-hidden="true">{{ mb_substr($comment->author?->name ?? 'S', 0, 1) }}</span>
                            <strong>{{ $canViewStaffDetails ? ($comment->author?->name ?? 'Unavailable') : ($comment->author_id === auth()->id() ? 'You' : 'Support team') }}</strong>
                            <span class="ticket-comment-visibility {{ $comment->visibility === 'internal' ? 'is-internal' : 'is-public' }}">{{ $comment->visibility === 'internal' ? 'Internal note' : 'Public reply' }}</span>
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
                        <label for="public-body">{{ auth()->id() === $ticket->requester_id ? 'Reply to support' : 'Public reply' }}</label>
                        <textarea id="public-body" name="body" rows="4" maxlength="10000" required placeholder="Write a message visible to the requester..."></textarea>
                        <x-field-error field="body" />
                        <button class="ticket-primary-button" type="submit"><svg data-lucide="send" aria-hidden="true"></svg>Send Reply</button>
                    </form>
                @endif

                @if ($canCommentInternal)
                    <form class="ticket-reply-form is-internal" method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                        @csrf
                        <input type="hidden" name="visibility" value="internal">
                        <label for="internal-body">Internal work note</label>
                        <textarea id="internal-body" name="body" rows="4" maxlength="10000" required placeholder="Add a note visible only to authorized support staff..."></textarea>
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
                    <div><dt>Last updated</dt><dd>{{ $ticket->updated_at?->format('d M Y · H:i') }}</dd></div>
                    <div class="ticket-detail-due {{ $ticket->due_at && $ticket->due_at->isPast() && !in_array($ticket->status, [\App\Support\TicketStatus::RESOLVED, \App\Support\TicketStatus::CLOSED], true) ? 'is-overdue' : '' }}"><dt>Due date</dt><dd>{{ $ticket->due_at?->format('d M Y · H:i') ?? 'Not set' }}@if($ticket->due_at && $ticket->due_at->isPast() && !in_array($ticket->status, [\App\Support\TicketStatus::RESOLVED, \App\Support\TicketStatus::CLOSED], true))<small>Overdue {{ $ticket->due_at->diffForHumans() }}</small>@endif</dd></div>
                    @if ($canViewStaffDetails)<div><dt>Assigned technician</dt><dd>{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</dd></div>@endif
                </dl>
            </section>

            @if (count($allowedTransitions) > 0 || $canReopen)
                <section class="ticket-page-panel" aria-labelledby="workflow-heading">
                    <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">WORKFLOW</p><h2 id="workflow-heading">Update Ticket</h2></div></header>
            @if (count($allowedTransitions) > 0)
                <form class="ticket-reply-form ticket-workflow-form" method="POST" action="{{ route('tickets.status.update', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <label for="status">Change status</label>
                    <select id="status" name="status" required>
                        @foreach ($allowedTransitions as $nextStatus)
                            <option value="{{ $nextStatus }}">{{ $statusLabels[$nextStatus] }}</option>
                        @endforeach
                    </select>
                    <x-field-error field="status" />
                    <button class="ticket-primary-button" type="submit"><svg data-lucide="refresh-cw" aria-hidden="true"></svg>Update Status</button>
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

    <section class="ticket-page-panel ticket-history-panel" aria-labelledby="status-history-heading">
        <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">TICKET TIMELINE</p><h2 id="status-history-heading">Status History</h2></div></header>
        <ol class="ticket-history-list">
            @forelse ($statusHistory as $event)
                <li>
                    <span class="ticket-history-marker"><svg data-lucide="refresh-cw" aria-hidden="true"></svg></span>
                    <span class="ticket-history-event"><strong>{{ $event->from_status ? ($statusLabels[$event->from_status] ?? $event->from_status) : 'Ticket created' }}</strong><span>{{ $event->from_status ? 'moved to' : 'started as' }} <b>{{ $statusLabels[$event->to_status] ?? $event->to_status }}</b></span></span>
                    <span class="ticket-history-meta"><time datetime="{{ $event->created_at?->toIso8601String() }}">{{ $event->created_at?->format('d M Y · H:i') }}</time>{{ $canViewStaffDetails ? ($event->actor?->name ?? 'Unavailable') : '' }}</span>
                </li>
            @empty
                <li class="ticket-history-empty">No status changes recorded.</li>
            @endforelse
        </ol>
    </section>

    @if ($canViewStaffDetails)
        <section class="ticket-page-panel ticket-history-panel" aria-labelledby="assignment-history-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">TICKET TIMELINE</p><h2 id="assignment-history-heading">Assignment History</h2></div></header>
            <ol class="ticket-history-list">
                @forelse ($assignmentHistory as $event)
                    <li>
                        <span class="ticket-history-marker is-assignment"><svg data-lucide="user-round-cog" aria-hidden="true"></svg></span>
                        <span class="ticket-history-event"><strong>{{ $event->fromTechnician?->name ?? 'Unassigned' }}</strong><span>assigned to <b>{{ $event->toTechnician?->name ?? 'Unassigned' }}</b></span></span>
                        <span class="ticket-history-meta"><time datetime="{{ $event->created_at?->toIso8601String() }}">{{ $event->created_at?->format('d M Y · H:i') }}</time>{{ $event->actor?->name ?? 'Unavailable' }}</span>
                    </li>
                @empty
                    <li class="ticket-history-empty">No assignments recorded.</li>
                @endforelse
            </ol>
        </section>
    @endif
</div>
@endsection
