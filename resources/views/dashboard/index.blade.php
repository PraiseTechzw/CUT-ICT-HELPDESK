@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $chartMaximum = max(1, collect($trend)->flatMap(fn (array $point): array => [$point['total'], $point['open'], $point['resolved']])->max() ?? 0);
    $chartSeries = static function (string $key) use ($trend, $chartMaximum): string {
        return collect($trend)->values()->map(function (array $point, int $index) use ($key, $chartMaximum, $trend): string {
            $x = count($trend) > 1 ? 36 + ($index * 388 / (count($trend) - 1)) : 230;
            $y = 130 - ($point[$key] / $chartMaximum * 104);

            return number_format($x, 1, '.', '').','.number_format($y, 1, '.', '');
        })->implode(' ');
    };
@endphp
<div class="dashboard-page">
    <div class="dashboard-layout-grid">
        <main class="dashboard-primary-column">
            <section class="dashboard-hero" aria-labelledby="dashboard-welcome">
                <img src="{{ asset('banner-image.png') }}" alt="Chinhoyi University of Technology campus" class="dashboard-hero-image">
                <div class="dashboard-hero-shade"></div>
                <div class="dashboard-hero-content">
                    <p class="dashboard-eyebrow">ICT HELPDESK · OPERATIONS</p>
                    <h1 id="dashboard-welcome">Welcome back, {{ Str::of($user->name)->before(' ') }}!</h1>
                    <p class="dashboard-hero-subtitle">Here’s what’s happening with the ICT Helpdesk today.</p>
                    <div class="dashboard-hero-meta">
                        <span><svg data-lucide="calendar-days" aria-hidden="true"></svg>{{ $dashboardDate->format('D, j M Y') }}</span>
                        <span><svg data-lucide="clock-3" aria-hidden="true"></svg>{{ now()->format('g:i A') }}</span>
                    </div>
                </div>
            </section>

            @if (count($stats))
                <section class="dashboard-stat-grid" aria-label="Key metrics">
                    @foreach ($stats as $stat)
                        <article class="dashboard-stat dashboard-panel">
                            <span class="stat-icon stat-icon-{{ $stat['tone'] }}">
                                @switch($stat['icon'])
                                    @case('ticket')<svg data-lucide="ticket" aria-hidden="true"></svg>@break
                                    @case('clock')<svg data-lucide="clock-3" aria-hidden="true"></svg>@break
                                    @case('check')<svg data-lucide="circle-check" aria-hidden="true"></svg>@break
                                    @case('bell')<svg data-lucide="bell" aria-hidden="true"></svg>@break
                                    @case('users')<svg data-lucide="users-round" aria-hidden="true"></svg>@break
                                    @case('pin')<svg data-lucide="map-pin" aria-hidden="true"></svg>@break
                                    @case('building')<svg data-lucide="building-2" aria-hidden="true"></svg>@break
                                    @default<svg data-lucide="activity" aria-hidden="true"></svg>
                                @endswitch
                            </span>
                            <div class="stat-copy"><p>{{ $stat['label'] }}</p><strong>{{ $stat['value'] }}</strong><small>{{ $stat['caption'] }}</small></div>
                        </article>
                    @endforeach
                </section>
            @endif

            <div class="dashboard-middle-grid">
                <section class="dashboard-panel trend-panel" aria-labelledby="ticket-trend-heading">
                    <header class="dashboard-panel-heading">
                        <div><p class="dashboard-panel-kicker">LAST 7 DAYS</p><h2 id="ticket-trend-heading">Ticket Trends</h2></div>
                        @if ($canViewTickets)<a class="panel-link" href="{{ route('tickets.index') }}">View queue <svg data-lucide="chevron-right" aria-hidden="true"></svg></a>@endif
                    </header>
                    @if ($canViewTickets)
                        <div class="trend-legend" aria-label="Chart legend">
                            <span><i class="legend-total"></i>Total</span><span><i class="legend-resolved"></i>Resolved</span><span><i class="legend-open"></i>Open</span>
                        </div>
                        @if (collect($trend)->sum('total') > 0)
                            <div class="trend-chart-wrap">
                                <svg class="trend-chart" viewBox="0 0 460 170" role="img" aria-label="Ticket totals, resolutions, and open tickets for the last seven days">
                                    <line class="chart-grid" x1="36" y1="26" x2="424" y2="26"/><line class="chart-grid" x1="36" y1="78" x2="424" y2="78"/><line class="chart-grid" x1="36" y1="130" x2="424" y2="130"/>
                                    <polyline class="chart-line chart-line-total" points="{{ $chartSeries('total') }}"/>
                                    <polyline class="chart-line chart-line-resolved" points="{{ $chartSeries('resolved') }}"/>
                                    <polyline class="chart-line chart-line-open" points="{{ $chartSeries('open') }}"/>
                                    @foreach ($trend as $index => $point)
                                        @php $cx = count($trend) > 1 ? 36 + ($index * 388 / (count($trend) - 1)) : 230; @endphp
                                        <circle class="chart-point chart-point-total" cx="{{ $cx }}" cy="{{ 130 - ($point['total'] / $chartMaximum * 104) }}" r="3"/>
                                    @endforeach
                                </svg>
                                <div class="trend-day-labels">@foreach ($trend as $point)<span>{{ $point['label'] }}</span>@endforeach</div>
                            </div>
                        @else
                            <div class="dashboard-empty-chart"><span class="empty-chart-icon"><svg data-lucide="chart-no-axes-combined" aria-hidden="true"></svg></span><strong>No ticket trend yet</strong><span>Ticket activity will appear here as requests come in.</span></div>
                        @endif
                    @else
                        <div class="dashboard-empty-chart dashboard-restricted-state"><span class="empty-chart-icon"><svg data-lucide="ticket" aria-hidden="true"></svg></span><strong>Ticket data is restricted</strong><span>Your account does not have permission to view ticket trends.</span></div>
                    @endif
                </section>

                <section class="dashboard-panel quick-actions-panel" aria-labelledby="quick-actions-heading">
                    <header class="dashboard-panel-heading"><div><p class="dashboard-panel-kicker">SHORTCUTS</p><h2 id="quick-actions-heading">Quick Actions</h2></div></header>
                    <div class="quick-action-list">
                        @foreach ($quickActions as $action)
                            @if ($action['url'])<a class="quick-action" href="{{ $action['url'] }}">@else<div class="quick-action is-unavailable" aria-disabled="true">@endif
                                <span class="quick-action-icon quick-action-{{ $action['tone'] }}">
                                    @if ($action['icon'] === 'plus-ticket')<svg data-lucide="ticket-plus" aria-hidden="true"></svg>
                                    @elseif ($action['icon'] === 'list')<svg data-lucide="list" aria-hidden="true"></svg>
                                    @elseif ($action['icon'] === 'book')<svg data-lucide="book-open" aria-hidden="true"></svg>
                                    @elseif ($action['icon'] === 'headset')<svg data-lucide="headset" aria-hidden="true"></svg>
                                    @else<svg data-lucide="activity" aria-hidden="true"></svg>@endif
                                </span>
                                <span class="quick-action-copy"><strong>{{ $action['label'] }}</strong><small>{{ $action['detail'] }}</small></span>
                                @if ($action['url'])<svg class="quick-action-arrow" data-lucide="chevron-right" aria-hidden="true"></svg>@else<span class="quick-action-unavailable-label">Unavailable</span>@endif
                            @if ($action['url'])</a>@else</div>@endif
                        @endforeach
                    </div>
                </section>
            </div>

            <section class="dashboard-panel recent-tickets-panel" aria-labelledby="recent-tickets-heading">
                <header class="dashboard-panel-heading recent-tickets-heading">
                    <div><p class="dashboard-panel-kicker">YOUR WORKSPACE</p><h2 id="recent-tickets-heading">Recent Tickets</h2></div>
                    @if ($canViewTickets)<a class="panel-link" href="{{ route('tickets.index') }}">View all <svg data-lucide="chevron-right" aria-hidden="true"></svg></a>@endif
                </header>
                <div class="recent-tickets-table-wrap">
                    <table class="recent-tickets-table">
                        <thead><tr><th scope="col">Ticket</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Location</th><th scope="col">Created</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                        @if ($canViewTickets && $recentTickets->isNotEmpty())
                            <tbody>
                            @foreach ($recentTickets as $ticket)
                                <tr>
                                    <td><a class="recent-ticket-link" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-id">#{{ $ticket->id }}</span>{{ $ticket->subject }}</a></td>
                                    <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                                    <td>{{ $ticket->priority?->name ?? '—' }}</td>
                                    <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                                    <td>@if($ticket->room){{ $ticket->room->building?->campus?->name }} · {{ $ticket->room->building?->name }} · {{ $ticket->room->name }}@else—@endif</td>
                                    <td class="ticket-updated">{{ $ticket->created_at?->format('j M · g:i A') }}</td>
                                    <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}" aria-label="Open ticket #{{ $ticket->id }}">Open</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        @elseif ($canViewTickets)
                            <tbody><tr><td class="recent-tickets-empty-row" colspan="7">No recent tickets. New and updated tickets visible to you will appear here.</td></tr></tbody>
                        @else
                            <tbody><tr><td class="recent-tickets-empty-row" colspan="7"><svg data-lucide="ticket" aria-hidden="true"></svg><span>Ticket details are restricted for this account.</span></td></tr></tbody>
                        @endif
                    </table>
                </div>
            </section>
        </main>

        <aside class="dashboard-right-rail" aria-label="System and activity">
            <section class="dashboard-panel dashboard-system-panel" aria-labelledby="system-status-heading">
                <header class="dashboard-panel-heading">
                    <div><p class="dashboard-panel-kicker">HEALTH</p><h2 id="system-status-heading">Current System Status</h2></div>
                    <span class="system-overall"><i></i>Portal responding</span>
                </header>
                <p class="system-updated">Database {{ $databaseOperational ? 'operational' : 'unavailable' }} · external monitoring not configured</p>
                <ul class="system-service-list">
                    <li>
                        <span class="service-icon service-icon-blue"><svg data-lucide="network" aria-hidden="true"></svg></span>
                        <span class="service-copy"><strong>Network &amp; Internet</strong><small>External connectivity</small></span>
                        <span class="service-state"><i class="is-down"></i>Not monitored</span>
                    </li>
                    <li>
                        <span class="service-icon service-icon-amber"><svg data-lucide="mail" aria-hidden="true"></svg></span>
                        <span class="service-copy"><strong>Email Service</strong><small>Outbound mail</small></span>
                        <span class="service-state"><i class="is-down"></i>Not configured</span>
                    </li>
                    <li>
                        <span class="service-icon service-icon-blue"><svg data-lucide="building-2" aria-hidden="true"></svg></span>
                        <span class="service-copy"><strong>Campus Systems</strong><small>External integration</small></span>
                        <span class="service-state"><i class="is-down"></i>Not monitored</span>
                    </li>
                </ul>
            </section>

            <section class="dashboard-panel activity-panel" aria-labelledby="recent-activity-heading">
                <header class="dashboard-panel-heading"><div><p class="dashboard-panel-kicker">LATEST UPDATES</p><h2 id="recent-activity-heading">Recent Activity</h2></div><a class="panel-link" href="{{ route('notifications.index') }}">View all <svg data-lucide="chevron-right" aria-hidden="true"></svg></a></header>
                <ol class="activity-list">
                    @forelse ($recentActivity as $activity)
                        <li class="activity-item">
                            <span class="activity-marker"><svg data-lucide="activity" aria-hidden="true"></svg></span>
                            <span class="activity-copy">
                                @if (isset($activity->ticket_id))
                                    <strong>Ticket #{{ $activity->ticket_id }} updated</strong>
                                    <small>{{ $activity->from_status ? ($statusLabels[$activity->from_status] ?? $activity->from_status).' to ' : '' }}{{ $statusLabels[$activity->to_status] ?? $activity->to_status }}</small>
                                @else
                                    <strong>{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $activity->action)) }}</strong>
                                    <small>{{ class_basename($activity->subject_type ?? 'System') }}</small>
                                @endif
                                <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time>
                            </span>
                        </li>
                    @empty
                        <li class="activity-empty">No recent activity to show.</li>
                    @endforelse
                </ol>
            </section>

            <section class="dashboard-brand-panel" aria-label="Chinhoyi University of Technology">
                <div class="dashboard-brand-logo-wrap"><img src="{{ asset('logo.png') }}" alt="Chinhoyi University of Technology"></div>
                <p class="dashboard-brand-quote">“Empowering innovation<br>through technology.”</p>
                <span class="dashboard-brand-accent" aria-hidden="true"></span>
            </section>
        </aside>
    </div>
</div>
@endsection
