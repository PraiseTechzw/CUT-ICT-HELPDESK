<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Building;
use App\Models\Campus;
use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Support\TicketStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $canViewAllTickets = $user->can('tickets.view_all');
        $canViewOwnTickets = $user->can('tickets.view_own');
        $canViewAssignedTickets = $user->can('tickets.view_assigned');
        $canViewTickets = $canViewAllTickets || $canViewOwnTickets || $canViewAssignedTickets;

        $scopeTickets = static function (Builder $query) use ($user, $canViewAllTickets, $canViewOwnTickets, $canViewAssignedTickets): Builder {
            if ($canViewAllTickets) {
                return $query;
            }

            return $query->where(function (Builder $visible) use ($user, $canViewOwnTickets, $canViewAssignedTickets): void {
                if ($canViewOwnTickets) {
                    $visible->where('requester_id', $user->id);
                }
                if ($canViewAssignedTickets) {
                    $method = $canViewOwnTickets ? 'orWhere' : 'where';
                    $visible->{$method}('assigned_technician_id', $user->id);
                }
            });
        };

        $today = CarbonImmutable::today();
        $trendStart = $today->subDays(6);
        $stats = [];
        $recentTickets = collect();
        $trend = [];
        $recentActivity = collect();

        if ($canViewTickets) {
            $visibleTickets = $scopeTickets(Ticket::query());
            $stats[] = [
                'label' => 'Total tickets',
                'value' => number_format((clone $visibleTickets)->count()),
                'caption' => 'In your visible queue',
                'tone' => 'blue',
                'icon' => 'ticket',
            ];
            $stats[] = [
                'label' => 'Open tickets',
                'value' => number_format($scopeTickets(Ticket::query())->whereIn('status', [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_FOR_REQUESTER])->count()),
                'caption' => 'Needs follow-up',
                'tone' => 'amber',
                'icon' => 'clock',
            ];

            $resolvedToday = TicketStatusHistory::query()
                ->where('to_status', TicketStatus::RESOLVED)
                ->whereDate('created_at', $today)
                ->whereHas('ticket', fn (Builder $query): Builder => $scopeTickets($query))
                ->count();
            $stats[] = [
                'label' => 'Resolved today',
                'value' => number_format($resolvedToday),
                'caption' => $today->format('D, j M'),
                'tone' => 'green',
                'icon' => 'check',
            ];

            if ($user->can('users.view')) {
                $stats[] = [
                    'label' => 'Active users',
                    'value' => number_format(User::query()->where('is_active', true)->count()),
                    'caption' => 'Enabled accounts',
                    'tone' => 'violet',
                    'icon' => 'users',
                ];
            } else {
                $stats[] = [
                    'label' => 'Unread notifications',
                    'value' => number_format($user->unreadNotifications()->count()),
                    'caption' => 'Updates for your account',
                    'tone' => 'violet',
                    'icon' => 'bell',
                ];
            }

            $dailyTickets = $scopeTickets(Ticket::query())
                ->whereBetween('created_at', [$trendStart->startOfDay(), $today->endOfDay()])
                ->selectRaw('DATE(created_at) as trend_date, status, COUNT(*) as total')
                ->groupByRaw('DATE(created_at), status')
                ->get();
            $dailyResolutions = TicketStatusHistory::query()
                ->where('to_status', TicketStatus::RESOLVED)
                ->whereBetween('created_at', [$trendStart->startOfDay(), $today->endOfDay()])
                ->whereHas('ticket', fn (Builder $query): Builder => $scopeTickets($query))
                ->selectRaw('DATE(created_at) as trend_date, COUNT(*) as total')
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'trend_date');

            for ($day = $trendStart; $day->lte($today); $day = $day->addDay()) {
                $dateKey = $day->toDateString();
                $rows = $dailyTickets->where('trend_date', $dateKey);
                $trend[] = [
                    'label' => $day->format('D'),
                    'date' => $dateKey,
                    'total' => (int) $rows->sum('total'),
                    'open' => (int) $rows->whereIn('status', [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_FOR_REQUESTER])->sum('total'),
                    'resolved' => (int) ($dailyResolutions[$dateKey] ?? 0),
                ];
            }

            $recentTickets = $scopeTickets(Ticket::query())
                ->with(['category:id,name', 'priority:id,name', 'room.building.campus'])
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'subject', 'status', 'ticket_category_id', 'ticket_priority_id', 'room_id', 'created_at', 'updated_at']);

            $recentActivity = TicketStatusHistory::query()
                ->with('ticket:id')
                ->whereHas('ticket', fn (Builder $query): Builder => $scopeTickets($query))
                ->latest()
                ->limit(5)
                ->get(['id', 'ticket_id', 'from_status', 'to_status', 'created_at']);
        } else {
            if ($user->can('users.view')) {
                $stats[] = ['label' => 'Active users', 'value' => number_format(User::query()->where('is_active', true)->count()), 'caption' => 'Enabled accounts', 'tone' => 'blue', 'icon' => 'users'];
            }
            if ($user->can('campuses.view')) {
                $stats[] = ['label' => 'Campuses', 'value' => number_format(Campus::query()->count()), 'caption' => 'Registered locations', 'tone' => 'green', 'icon' => 'pin'];
            }
            if ($user->can('buildings.view')) {
                $stats[] = ['label' => 'Buildings', 'value' => number_format(Building::query()->count()), 'caption' => 'Across all campuses', 'tone' => 'amber', 'icon' => 'building'];
            }
            if ($user->can('audit.view')) {
                $stats[] = ['label' => 'Audit events', 'value' => number_format(AuditEvent::query()->count()), 'caption' => 'Recorded system actions', 'tone' => 'violet', 'icon' => 'activity'];
            }
            if ($user->can('audit.view')) {
                $recentActivity = AuditEvent::query()->latest()->limit(5)->get(['id', 'action', 'subject_type', 'created_at']);
            }
        }

        $quickActions = collect([
            [
                'label' => 'Create New Ticket',
                'detail' => 'Report an issue or request assistance',
                'url' => $user->can('tickets.create') ? route('tickets.create') : null,
                'unavailable' => 'Ticket creation is not available to this account',
                'icon' => 'plus-ticket',
                'tone' => 'blue',
            ],
            [
                'label' => 'My Tickets',
                'detail' => 'View and track your requests',
                'url' => $canViewTickets ? route('tickets.index') : null,
                'unavailable' => 'Ticket access is not available to this account',
                'icon' => 'list',
                'tone' => 'cyan',
            ],
            ['label' => 'Knowledge Base', 'detail' => 'Knowledge articles are not published yet', 'url' => null, 'icon' => 'book', 'tone' => 'cyan'],
            ['label' => 'Contact Helpdesk', 'detail' => 'Support contact is not configured', 'url' => null, 'icon' => 'headset', 'tone' => 'violet'],
        ]);

        $databaseOperational = DB::select('select 1') !== [];

        return view('dashboard.index', [
            'user' => $user,
            'canViewTickets' => $canViewTickets,
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'trend' => $trend,
            'recentActivity' => $recentActivity,
            'quickActions' => $quickActions,
            'statusLabels' => TicketStatus::labels(),
            'databaseOperational' => $databaseOperational,
            'dashboardDate' => $today,
        ]);
    }
}
