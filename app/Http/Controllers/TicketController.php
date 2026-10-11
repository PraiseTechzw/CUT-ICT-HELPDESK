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
use App\Models\Room;
use App\Models\User;
use App\Services\TicketNotificationService;
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
        return $this->ticketList($request, false);
    }

    public function mine(Request $request): View
    {
        $request->query->set('scope', 'mine');

        return $this->ticketList($request, false);
    }

    public function overdue(Request $request): View
    {
        return $this->ticketList($request, true);
    }

    private function ticketList(Request $request, bool $overdueOnly): View
    {
        $user = $request->user();
        $canViewAll = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL);
        $canViewOwn = $user->hasPermissionTo(Permissions::TICKETS_VIEW_OWN);
        $canViewAssigned = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED);
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 120);
        $mineOnly = $request->query('scope') === 'mine';
        $status = (string) $request->query('status', '');
        $categoryId = (string) $request->query('category', '');
        $priorityId = (string) $request->query('priority', '');

        abort_unless($canViewAll || $canViewOwn || $canViewAssigned, 403);
        abort_if($mineOnly && ! ($canViewOwn || $canViewAssigned), 403);

        $visibleTickets = Ticket::query();
        if ($mineOnly || ! $canViewAll) {
            $visibleTickets->where(function (Builder $visible) use ($user, $canViewOwn, $canViewAssigned): void {
                if ($canViewOwn) {
                    $visible->where('requester_id', $user->id);
                }
                if ($canViewAssigned) {
                    $method = $canViewOwn ? 'orWhere' : 'where';
                    $visible->{$method}('assigned_technician_id', $user->id);
                }
            });
        }

        if ($overdueOnly) {
            $visibleTickets->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED]);
        }

        $statusCounts = (clone $visibleTickets)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count): int => (int) $count);
        $countStatus = fn (string $ticketStatus): int => $statusCounts->get($ticketStatus, 0);
        $summary = [
            'total' => $statusCounts->sum(),
            'open' => $countStatus(TicketStatus::OPEN) + $countStatus(TicketStatus::ASSIGNED),
            'in_progress' => $countStatus(TicketStatus::IN_PROGRESS) + $countStatus(TicketStatus::WAITING_FOR_REQUESTER),
            'resolved' => $countStatus(TicketStatus::RESOLVED),
            'closed' => $countStatus(TicketStatus::CLOSED),
            'overdue' => (clone $visibleTickets)->whereNotNull('due_at')->where('due_at', '<', now())->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])->count(),
        ];

        $query = (clone $visibleTickets)
            ->with(['requester', 'assignedTechnician', 'category', 'priority', 'room.building.campus'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $matches) use ($search): void {
                    $matches->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $matches->orWhere('id', (int) $search);
                    }
                });
            })
            ->when(ctype_digit($categoryId), fn (Builder $tickets): Builder => $tickets->where('ticket_category_id', (int) $categoryId))
            ->when(ctype_digit($priorityId), fn (Builder $tickets): Builder => $tickets->where('ticket_priority_id', (int) $priorityId))
            ->orderByDesc('updated_at');

        if ($status === TicketStatus::OPEN) {
            $query->whereIn('status', [TicketStatus::OPEN, TicketStatus::ASSIGNED]);
        } elseif ($status === TicketStatus::IN_PROGRESS) {
            $query->whereIn('status', [TicketStatus::IN_PROGRESS, TicketStatus::WAITING_FOR_REQUESTER]);
        } elseif (in_array($status, TicketStatus::values(), true)) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(15)->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'canViewStaffDetails' => $canViewAll || $canViewAssigned,
            'canCreateTicket' => $user->hasPermissionTo(Permissions::TICKETS_CREATE),
            'statusLabels' => TicketStatus::labels(),
            'search' => $search,
            'scope' => $mineOnly ? 'mine' : ($overdueOnly ? 'overdue' : 'all'),
            'overdueOnly' => $overdueOnly,
            'summary' => $summary,
            'categories' => TicketCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'priorities' => TicketPriority::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'filters' => ['status' => $status, 'category' => $categoryId, 'priority' => $priorityId],
        ]);
    }

    public function create(Request $request): View
    {
        return view('tickets.create', [
            'categories' => TicketCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'priorities' => TicketPriority::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'rooms' => Room::query()
                ->with('building.campus')
                ->where('is_active', true)
                ->whereHas('building', fn (Builder $buildings) => $buildings->where('is_active', true))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function assignmentForm(Request $request, Ticket $ticket): View
    {
        abort_unless($request->user()->can('view', $ticket), 404);
        abort_unless($request->user()->can('assign', $ticket), 403);

        $ticket->load(['requester', 'assignedTechnician', 'category', 'priority', 'room.building.campus']);

        return view('tickets.assign', [
            'ticket' => $ticket,
            'technicians' => User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('slug', Roles::TECHNICIAN))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(
        StoreTicketRequest $request,
        AuditLogger $auditLogger,
        TicketNotificationService $ticketNotifications,
    ): RedirectResponse {
        $validated = $request->validated();

        $ticket = DB::transaction(function () use ($request, $validated, $auditLogger, $ticketNotifications): Ticket {
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
                'fields' => ['subject', 'description', 'ticket_category_id', 'ticket_priority_id', 'room_id'],
            ]);
            $ticketNotifications->ticketCreated($ticket, $request->user());

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket created.');
    }

    public function show(Request $request, Ticket $ticket, TicketWorkflow $workflow): View
    {
        $user = $request->user();
        abort_unless($user->can('view', $ticket), 404);

        $ticket->load(['requester', 'assignedTechnician', 'category', 'priority', 'room.building.campus']);
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

    public function storeComment(
        StoreTicketCommentRequest $request,
        Ticket $ticket,
        AuditLogger $auditLogger,
        TicketNotificationService $ticketNotifications,
    ): RedirectResponse {
        $validated = $request->validated();
        DB::transaction(function () use ($ticket, $request, $validated, $auditLogger, $ticketNotifications): void {
            $ticket->comments()->create([
                'author_id' => $request->user()->id,
                'visibility' => $validated['visibility'],
                'body' => $validated['body'],
            ]);

            $auditLogger->record($request->user(), 'ticket.comment_added', $ticket, [
                'visibility' => $validated['visibility'],
            ]);
            $ticketNotifications->commentAdded($ticket, $request->user(), $validated['visibility']);
        });

        return back()->with('status', 'Reply added.');
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket, TicketWorkflow $workflow): RedirectResponse
    {
        $validated = $request->validated();
        $technicianId = $validated['technician_id'] ?? null;
        $workflow->assign($ticket, $technicianId === null ? null : (int) $technicianId, $request->user(), $validated['due_at'] ?? null);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket assignment updated.');
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
