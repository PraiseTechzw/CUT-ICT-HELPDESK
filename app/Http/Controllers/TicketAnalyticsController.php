<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TicketAnalyticsRequest;
use App\Services\TicketAnalyticsService;
use App\Support\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketAnalyticsController extends Controller
{
    public function __invoke(TicketAnalyticsRequest $request, TicketAnalyticsService $analytics): View
    {
        Gate::authorize(Permissions::TICKETS_VIEW_ALL);

        $filters = $request->validated();
        $timezone = (string) config('app.timezone', 'UTC');
        $through = isset($filters['through'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['through'], $timezone)
            : CarbonImmutable::today($timezone);
        $from = isset($filters['from'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $filters['from'], $timezone)
            : $through->subDays(29);
        $report = $analytics->summarize($from, $through);

        return view('reports.tickets.index', [
            'report' => $report,
            'from' => $from->toDateString(),
            'through' => $through->toDateString(),
            'timezone' => $timezone,
        ]);
    }
}
