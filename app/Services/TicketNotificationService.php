<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActivityNotification;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Notification;

class TicketNotificationService
{
    public function ticketCreated(Ticket $ticket, User $actor): void
    {
        $this->send(
            $this->helpdeskRecipients(),
            $ticket,
            $actor,
            'ticket.created',
            'A new ticket is waiting for helpdesk triage.',
        );
    }

    public function assignmentChanged(Ticket $ticket, ?User $assignee, User $actor): void
    {
        if ($assignee !== null) {
            $this->send(
                [$assignee],
                $ticket,
                $actor,
                'ticket.assigned',
                'A ticket was assigned to you.',
            );
        }

        $this->send(
            [$ticket->requester],
            $ticket,
            $actor,
            'ticket.assignment_changed',
            'The assignment on a ticket you submitted was updated.',
        );
    }

    public function statusChanged(Ticket $ticket, User $actor): void
    {
        $this->send(
            [$ticket->requester],
            $ticket,
            $actor,
            'ticket.status_changed',
            'The status of a ticket you submitted has changed.',
        );

        $this->send(
            [$ticket->assignedTechnician],
            $ticket,
            $actor,
            'ticket.status_changed',
            'A ticket assigned to you has a status update.',
        );
    }

    public function reopened(Ticket $ticket, User $actor): void
    {
        $this->send(
            $this->helpdeskRecipients(),
            $ticket,
            $actor,
            'ticket.reopened',
            'A ticket was reopened and is waiting for helpdesk triage.',
        );
    }

    public function commentAdded(Ticket $ticket, User $actor, string $visibility): void
    {
        if ($visibility === 'public') {
            if ($ticket->requester_id === $actor->id) {
                $assignee = $ticket->assignedTechnician;
                if ($assignee !== null) {
                    $this->send(
                        [$assignee],
                        $ticket,
                        $actor,
                        'ticket.public_reply_added',
                        'A requester added a public reply to a ticket assigned to you.',
                    );
                } else {
                    $this->send(
                        $this->helpdeskRecipients(),
                        $ticket,
                        $actor,
                        'ticket.public_reply_added',
                        'A requester added a public reply to an unassigned ticket.',
                    );
                }

                return;
            }

            $this->send(
                [$ticket->requester],
                $ticket,
                $actor,
                'ticket.public_reply_added',
                'A new public reply was added to a ticket you submitted.',
            );

            return;
        }

        $isTriage = $actor->hasPermissionTo(Permissions::TICKETS_VIEW_ALL)
            && $actor->hasPermissionTo(Permissions::TICKETS_TRIAGE);

        if ($isTriage) {
            $this->send(
                [$ticket->assignedTechnician],
                $ticket,
                $actor,
                'ticket.internal_note_added',
                'A new internal note was added to a ticket assigned to you.',
            );

            return;
        }

        $this->send(
            $this->helpdeskRecipients(),
            $ticket,
            $actor,
            'ticket.internal_note_added',
            'A new internal note was added to a ticket in the helpdesk queue.',
        );
    }

    /** @return Collection<int, User> */
    private function helpdeskRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles.permissions', fn ($query) => $query->where('slug', Permissions::TICKETS_VIEW_ALL))
            ->whereHas('roles.permissions', fn ($query) => $query->where('slug', Permissions::TICKETS_TRIAGE))
            ->get();
    }

    /** @param iterable<User|null> $recipients */
    private function send(iterable $recipients, Ticket $ticket, User $actor, string $event, string $message): void
    {
        /** @var SupportCollection<int, User> $activeRecipients */
        $activeRecipients = collect($recipients)
            ->filter(fn ($recipient) => $recipient instanceof User
                && $recipient->is_active
                && $recipient->id !== $actor->id)
            ->unique('id')
            ->values();

        if ($activeRecipients->isEmpty()) {
            return;
        }

        Notification::send(
            $activeRecipients,
            new TicketActivityNotification((int) $ticket->getKey(), $event, $message),
        );
    }
}
