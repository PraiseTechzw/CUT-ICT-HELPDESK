<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Support\TicketStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class TicketAnalyticsService
{
    /**
     * Build aggregate metrics for tickets created within an inclusive calendar-date range.
     * Status counts reflect each ticket's current status when this report is generated.
     *
     * @return array{total:int,daily:array<int,array{date:string,total:int,percentage:float}>,statuses:array<int,array{label:string,total:int}>,categories:array<int,array{label:string,total:int}>,priorities:array<int,array{label:string,total:int}>}
     */
    public function summarize(CarbonImmutable $from, CarbonImmutable $through): array
    {
        $start = $from->startOfDay();
        $endExclusive = $through->addDay()->startOfDay();
        $applyRange = static fn (Builder $query): Builder => $query
            ->where('tickets.created_at', '>=', $start)
            ->where('tickets.created_at', '<', $endExclusive);

        $total = $applyRange(Ticket::query())->count();
        $dailyCounts = $applyRange(Ticket::query())
            ->selectRaw('DATE(tickets.created_at) as report_date, COUNT(tickets.id) as total')
            ->groupByRaw('DATE(tickets.created_at)')
            ->orderBy('report_date')
            ->pluck('total', 'report_date')
            ->all();

        $daily = [];
        for ($date = $start; $date->lte($through); $date = $date->addDay()) {
            $key = $date->toDateString();
            $daily[] = ['date' => $key, 'total' => (int) ($dailyCounts[$key] ?? 0), 'percentage' => 0.0];
        }
        $maxDaily = max(array_column($daily, 'total') ?: [0]);
        foreach ($daily as &$point) {
            $point['percentage'] = $maxDaily > 0 ? round(($point['total'] / $maxDaily) * 100, 1) : 0.0;
        }
        unset($point);

        $statuses = $applyRange(Ticket::query())
            ->selectRaw('tickets.status, COUNT(tickets.id) as total')
            ->groupBy('tickets.status')
            ->orderBy('tickets.status')
            ->get()
            ->map(fn ($row): array => [
                'label' => TicketStatus::labels()[$row->status] ?? 'Other',
                'total' => (int) $row->total,
            ])
            ->all();

        $categories = $applyRange(Ticket::query()
            ->leftJoin('ticket_categories', 'tickets.ticket_category_id', '=', 'ticket_categories.id'))
            ->selectRaw("COALESCE(ticket_categories.name, 'Uncategorized') as label, COUNT(tickets.id) as total")
            ->groupBy('ticket_categories.id', 'ticket_categories.name')
            ->orderBy('label')
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'total' => (int) $row->total])
            ->all();

        $priorities = $applyRange(Ticket::query()
            ->leftJoin('ticket_priorities', 'tickets.ticket_priority_id', '=', 'ticket_priorities.id'))
            ->selectRaw("COALESCE(ticket_priorities.name, 'Uncategorized') as label, COUNT(tickets.id) as total")
            ->groupBy('ticket_priorities.id', 'ticket_priorities.name')
            ->orderBy('label')
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'total' => (int) $row->total])
            ->all();

        return compact('total', 'daily', 'statuses', 'categories', 'priorities');
    }
}
