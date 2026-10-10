<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Support\Permissions;
use App\Support\TicketStatus;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            || ($user->hasPermissionTo(Permissions::TICKETS_VIEW_OWN) && $ticket->requester_id === $user->id)
            || ($user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED) && $ticket->assigned_technician_id === $user->id);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            && $user->hasPermissionTo(Permissions::TICKETS_ASSIGN);
    }

    public function viewInternal(User $user, Ticket $ticket): bool
    {
        $isTriage = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            && $user->hasPermissionTo(Permissions::TICKETS_TRIAGE);
        $isAssignedTechnician = $ticket->assigned_technician_id === $user->id
            && $user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED)
            && $user->hasPermissionTo(Permissions::TICKETS_WORK_ASSIGNED);

        return $user->hasPermissionTo(Permissions::TICKETS_VIEW_INTERNAL)
            && ($isTriage || $isAssignedTechnician);
    }

    public function comment(User $user, Ticket $ticket, string $visibility): bool
    {
        if (! $this->view($user, $ticket)) {
            return false;
        }

        $isRequester = $ticket->requester_id === $user->id;
        $isAssignedTechnician = $ticket->assigned_technician_id === $user->id
            && $user->hasPermissionTo(Permissions::TICKETS_WORK_ASSIGNED);
        $isTriage = $user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            && $user->hasPermissionTo(Permissions::TICKETS_TRIAGE);

        if ($visibility === 'public') {
            return ($isRequester && $user->hasPermissionTo(Permissions::TICKETS_REPLY_OWN))
                || (($isAssignedTechnician || $isTriage) && $user->hasPermissionTo(Permissions::TICKETS_COMMENT_PUBLIC));
        }

        if ($visibility === 'internal') {
            return $this->viewInternal($user, $ticket)
                && $user->hasPermissionTo(Permissions::TICKETS_COMMENT_INTERNAL);
        }

        return false;
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return ($user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
                && $user->hasPermissionTo(Permissions::TICKETS_TRIAGE))
            || ($ticket->assigned_technician_id === $user->id
                && $user->hasPermissionTo(Permissions::TICKETS_VIEW_ASSIGNED)
                && $user->hasPermissionTo(Permissions::TICKETS_WORK_ASSIGNED));
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        if (! in_array($ticket->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED], true)) {
            return false;
        }

        return ($ticket->requester_id === $user->id
                && $user->hasPermissionTo(Permissions::TICKETS_REOPEN_OWN))
            || ($user->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
                && $user->hasPermissionTo(Permissions::TICKETS_TRIAGE));
    }
}
