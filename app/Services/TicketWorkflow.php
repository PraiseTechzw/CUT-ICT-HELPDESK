<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAssignmentHistory;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Permissions;
use App\Support\Roles;
use App\Support\TicketStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketWorkflow
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TicketNotificationService $ticketNotifications,
    ) {}

    /** @return list<string> */
    public function allowedTransitionsFor(Ticket $ticket, User $actor): array
    {
        $isTriage = $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            && $actor->hasPermissionTo(Permissions::TICKETS_TRIAGE);
        $isAssignedTechnician = $ticket->assigned_technician_id === $actor->id
            && $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED)
            && $actor->hasPermissionTo(Permissions::TICKETS_WORK_ASSIGNED);

        if (! $isTriage && ! $isAssignedTechnician) {
            return [];
        }

        $transitions = TicketStatus::transitions()[$ticket->status] ?? [];

        if (! $isTriage) {
            return array_values(array_filter($transitions, fn (string $status): bool => $status !== TicketStatus::CLOSED));
        }

        return $transitions;
    }

    public function assign(Ticket $ticket, ?int $technicianId, User $actor): void
    {
        DB::transaction(function () use ($ticket, $technicianId, $actor): void {
            $lockedTicket = Ticket::query()->lockForUpdate()->findOrFail($ticket->getKey());

            if (in_array($lockedTicket->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED], true)) {
                throw ValidationException::withMessages(['technician_id' => 'Reopen the ticket before assigning a technician.']);
            }

            $technician = null;
            if ($technicianId !== null) {
                $technician = User::query()
                    ->whereKey($technicianId)
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($query) => $query->where('slug', Roles::TECHNICIAN))
                    ->first();

                if ($technician === null) {
                    throw ValidationException::withMessages(['technician_id' => 'Choose an active account with the Technician role.']);
                }
            }

            $previousId = $lockedTicket->assigned_technician_id;
            $nextId = $technician?->id;
            if ($previousId === $nextId) {
                return;
            }

            if ($technician === null && $lockedTicket->status !== TicketStatus::ASSIGNED) {
                throw ValidationException::withMessages(['technician_id' => 'An in-progress ticket must be reassigned rather than left without an owner.']);
            }

            $previousStatus = $lockedTicket->status;
            $lockedTicket->assigned_technician_id = $nextId;
            if ($technician !== null && $lockedTicket->status === TicketStatus::OPEN) {
                $lockedTicket->status = TicketStatus::ASSIGNED;
            } elseif ($technician === null) {
                $lockedTicket->status = TicketStatus::OPEN;
            }
            $lockedTicket->save();

            TicketAssignmentHistory::query()->create([
                'ticket_id' => $lockedTicket->id,
                'from_technician_id' => $previousId,
                'to_technician_id' => $nextId,
                'actor_id' => $actor->id,
            ]);

            if ($previousStatus !== $lockedTicket->status) {
                $this->recordStatusChange($lockedTicket, $previousStatus, $lockedTicket->status, $actor);
            }

            $this->auditLogger->record($actor, 'ticket.assignment_changed', $lockedTicket, [
                'from_technician_id' => $previousId,
                'to_technician_id' => $nextId,
            ]);
            $this->ticketNotifications->assignmentChanged($lockedTicket, $technician, $actor);
        });
    }

    public function changeStatus(Ticket $ticket, string $nextStatus, User $actor): void
    {
        DB::transaction(function () use ($ticket, $nextStatus, $actor): void {
            $lockedTicket = Ticket::query()->lockForUpdate()->findOrFail($ticket->getKey());
            $isTriage = $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
                && $actor->hasPermissionTo(Permissions::TICKETS_TRIAGE);
            $isAssignedTechnician = $lockedTicket->assigned_technician_id === $actor->id
                && $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED)
                && $actor->hasPermissionTo(Permissions::TICKETS_WORK_ASSIGNED);

            if (! $isTriage && ! $isAssignedTechnician) {
                throw new AuthorizationException;
            }

            $allowed = $this->allowedTransitionsFor($lockedTicket, $actor);
            if (! in_array($nextStatus, $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'That status transition is not allowed.']);
            }

            $previousStatus = $lockedTicket->status;
            $lockedTicket->status = $nextStatus;
            $lockedTicket->save();

            $this->recordStatusChange($lockedTicket, $previousStatus, $nextStatus, $actor);
            $this->auditLogger->record($actor, 'ticket.status_changed', $lockedTicket, [
                'from_status' => $previousStatus,
                'to_status' => $nextStatus,
            ]);
            $this->ticketNotifications->statusChanged($lockedTicket, $actor);
        });
    }

    public function reopen(Ticket $ticket, User $actor): void
    {
        DB::transaction(function () use ($ticket, $actor): void {
            $lockedTicket = Ticket::query()->lockForUpdate()->findOrFail($ticket->getKey());
            $isTriage = $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
                && $actor->hasPermissionTo(Permissions::TICKETS_TRIAGE);
            $isRequester = $lockedTicket->requester_id === $actor->id
                && $actor->hasPermissionTo(Permissions::TICKETS_REOPEN_OWN);

            if (! $isTriage && ! $isRequester) {
                throw new AuthorizationException;
            }

            if (! in_array($lockedTicket->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED], true)) {
                throw ValidationException::withMessages(['status' => 'Only a resolved or closed ticket can be reopened.']);
            }

            $previousStatus = $lockedTicket->status;
            $previousTechnicianId = $lockedTicket->assigned_technician_id;
            $lockedTicket->status = TicketStatus::OPEN;
            $lockedTicket->assigned_technician_id = null;
            $lockedTicket->save();

            if ($previousTechnicianId !== null) {
                TicketAssignmentHistory::query()->create([
                    'ticket_id' => $lockedTicket->id,
                    'from_technician_id' => $previousTechnicianId,
                    'to_technician_id' => null,
                    'actor_id' => $actor->id,
                ]);
            }

            $this->recordStatusChange($lockedTicket, $previousStatus, TicketStatus::OPEN, $actor);
            $this->auditLogger->record($actor, 'ticket.reopened', $lockedTicket, [
                'from_status' => $previousStatus,
                'previous_technician_id' => $previousTechnicianId,
            ]);
            $this->ticketNotifications->reopened($lockedTicket, $actor);
        });
    }

    private function recordStatusChange(Ticket $ticket, string $from, string $to, User $actor): void
    {
        TicketStatusHistory::query()->create([
            'ticket_id' => $ticket->id,
            'from_status' => $from,
            'to_status' => $to,
            'actor_id' => $actor->id,
        ]);
    }
}
