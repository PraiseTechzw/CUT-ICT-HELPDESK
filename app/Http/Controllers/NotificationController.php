<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest('created_at')->paginate(20)->withQueryString();

        $ticketIds = $notifications->getCollection()
            ->map(fn ($notification) => $notification->data['ticket_id'] ?? null)
            ->filter(fn ($ticketId) => is_numeric($ticketId))
            ->map(fn ($ticketId) => (int) $ticketId)
            ->unique()
            ->values();

        $tickets = $ticketIds->isEmpty()
            ? collect()
            : Ticket::query()->whereKey($ticketIds)->get()->keyBy('id');

        $notifications->getCollection()->transform(function ($notification) use ($tickets, $user) {
            $ticketId = $notification->data['ticket_id'] ?? null;
            $ticket = is_numeric($ticketId) ? $tickets->get((int) $ticketId) : null;
            $notification->setAttribute('ticket_is_viewable', $ticket !== null && $user->can('view', $ticket));

            return $notification;
        });

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $ticketId = $item->data['ticket_id'] ?? null;

        abort_unless(is_int($ticketId) || (is_string($ticketId) && ctype_digit($ticketId)), 404);

        $ticket = Ticket::query()->findOrFail((int) $ticketId);
        abort_unless($request->user()->can('view', $ticket), 404);

        $item->markAsRead();

        return redirect()->route('tickets.show', $ticket);
    }
}
