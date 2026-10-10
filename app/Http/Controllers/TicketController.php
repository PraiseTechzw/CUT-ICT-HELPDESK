<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreTicketCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Services\TicketWorkflow;
use App\Support\AuditLogger;
use App\Support\Permissions;
use App\Support\Roles;
use App\Support\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewAll = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL);
        $canViewOwn = $user->hasPermissionTo(Permissions::TICKETS_VIEW_OWN);
        $canViewAssigned = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED);

        abort_unless($canViewAll || $canViewOwn || $canViewAssigned, 403);

        $tickets = Ticket::query()
            ->with(['requester', 'assignedTechnician', 'category', 'priority'])
            ->when(! $canViewAll, function (Builder $query) use ($user, $canViewOwn, $canViewAssigned): void {
                $query->where(function (Builder $visible) use ($user, $canViewOwn, $canViewAssigned): void {
                    $hasCondition = false;
                    if ($canViewOwn) {
                        $visible->where('requester_id', $user->id);
                        $hasCondition = true;
                    }
                    if ($canViewAssigned) {
                        $method = $hasCondition ? 'orWhere' : 'where';
                        $visible->{$method}('assigned_technician_id', $user->id);
                    }
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'canViewStaffDetails' => $canViewAll || $canViewAssigned,
            'canCreateTicket' => $user->hasPermissionTo(Permissions::TICKETS_CREATE),
            'statusLabels' => TicketStatus::labels(),
        ]);
    }

    public function create(): View
    {
        return view('tickets.create', [
            'categories' => TicketCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'priorities' => TicketPriority::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTicketRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = DB::transaction(function () use ($request, $validated, $auditLogger): Ticket {
            $ticket = new Ticket($validated);
            $ticket->requester()->associate($request->user());
            $ticket->status = TicketStatus::OPEN;
            $ticket->save();

            $ticket->statusHistory()->create([
                'from_status' => null,
                'to_status' => TicketStatus::OPEN,
                'actor_id' => $request->user()->id,
            ]);

            $auditLogger->record($request->user(), 'ticket.created', $ticket, [
                'fields' => ['subject', 'description', 'ticket_category_id', 'ticket_priority_id'],
            ]);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket created.');
    }

    public function show(Request $request, Ticket $ticket, TicketWorkflow $workflow): View
    {
        $user = $request->user();
        abort_unless($user->can('view', $ticket), 404);

        $ticket->load(['requester', 'assignedTechnician', 'category', 'priority']);
        $canViewStaffDetails = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            || ($ticket->assigned_technician_id === $user->id
                && $user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED));
        $canViewInternal = $user->can('viewInternal', $ticket);
        $comments = $ticket->comments()
            ->with('author')
            ->when(! $canViewInternal, fn ($query) => $query->where('visibility', 'public'))
            ->orderBy('created_at')
            ->get();

        $canCommentPublic = $user->can('comment', [$ticket, 'public']);
        $canCommentInternal = $user->can('comment', [$ticket, 'internal']);
        $canAssign = $user->can('assign', $ticket);

        return view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $comments,
            'statusHistory' => $ticket->statusHistory()->with('actor')->orderBy('created_at')->get(),
            'assignmentHistory' => $canViewStaffDetails
                ? $ticket->assignmentHistory()->with(['actor', 'fromTechnician', 'toTechnician'])->orderBy('created_at')->get()
                : collect(),
            'technicians' => $canAssign
                ? User::query()->where('is_active', true)->whereHas('roles', fn ($query) => $query->where('slug', Roles::TECHNICIAN))->orderBy('name')->get(['id', 'name'])
                : collect(),
            'canViewStaffDetails' => $canViewStaffDetails,
            'canViewInternal' => $canViewInternal,
            'canCommentPublic' => $canCommentPublic,
            'canCommentInternal' => $canCommentInternal,
            'canAssign' => $canAssign,
            'canReopen' => $user->can('reopen', $ticket),
            'allowedTransitions' => $workflow->allowedTransitionsFor($ticket, $user),
            'statusLabels' => TicketStatus::labels(),
        ]);
    }

    public function storeComment(StoreTicketCommentRequest $request, Ticket $ticket, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validated();
        $ticket->comments()->create([
            'author_id' => $request->user()->id,
            'visibility' => $validated['visibility'],
            'body' => $validated['body'],
        ]);

        $auditLogger->record($request->user(), 'ticket.comment_added', $ticket, [
            'visibility' => $validated['visibility'],
        ]);

        return back()->with('status', 'Reply added.');
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket, TicketWorkflow $workflow): RedirectResponse
    {
        $technicianId = $request->validated()['technician_id'] ?? null;
        $workflow->assign($ticket, $technicianId === null ? null : (int) $technicianId, $request->user());

        return back()->with('status', 'Ticket assignment updated.');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket, TicketWorkflow $workflow): RedirectResponse
    {
        $workflow->changeStatus($ticket, $request->validated()['status'], $request->user());

        return back()->with('status', 'Ticket status updated.');
    }

    public function reopen(Request $request, Ticket $ticket, TicketWorkflow $workflow): RedirectResponse
    {
        abort_unless($request->user()->can('view', $ticket), 404);
        abort_unless($request->user()->can('reopen', $ticket), 403);
        $workflow->reopen($ticket, $request->user());

        return back()->with('status', 'Ticket reopened and returned to the unassigned queue.');
    }
}
