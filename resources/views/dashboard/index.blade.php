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
    <div class="dashboard-overview-grid">
        <section class="dashboard-hero" aria-labelledby="dashboard-welcome">
            <img src="{{ asset('banner-image.png') }}" alt="Chinhoyi University of Technology campus" class="dashboard-hero-image">
            <div class="dashboard-hero-shade"></div>
            <div class="dashboard-hero-content">
                <p class="dashboard-eyebrow">ICT HELPDESK · OPERATIONS</p>
                <h1 id="dashboard-welcome">Welcome back, {{ Str::of($user->name)->before(' ') }}!</h1>
                <p class="dashboard-hero-subtitle">Here’s what’s happening with the ICT Helpdesk today.</p>
                <div class="dashboard-hero-meta">
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>{{ $dashboardDate->format('D, j M Y') }}</span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>{{ now()->format('g:i A') }}</span>
                </div>
                @if ($user->can('tickets.create'))
                    <a class="dashboard-hero-action" href="{{ route('tickets.create') }}">Create a ticket <span aria-hidden="true">&rarr;</span></a>
                @endif
            </div>
        </section>

        <section class="dashboard-panel dashboard-system-panel" aria-labelledby="system-status-heading">
            <header class="dashboard-panel-heading">
                <div><p class="dashboard-panel-kicker">HEALTH</p><h2 id="system-status-heading">System status</h2></div>
                <span class="system-overall"><i></i>All operational</span>
            </header>
            <p class="system-updated">Core services are responding</p>
            <ul class="system-service-list">
                <li>
                    <span class="service-icon service-icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4zM8 21h8m-4-3v3M8 10h8m-8 4h5"/></svg></span>
                    <span class="service-copy"><strong>Application</strong><small>Helpdesk portal</small></span>
                    <span class="service-state"><i></i>Operational</span>
                </li>
                <li>
                    <span class="service-icon service-icon-green"><svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5m-16 7v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/></svg></span>
                    <span class="service-copy"><strong>Database</strong><small>Live connection</small></span>
                    <span class="service-state"><i class="{{ $databaseOperational ? '' : 'is-down' }}"></i>{{ $databaseOperational ? 'Operational' : 'Unavailable' }}</span>
                </li>
                <li>
                    <span class="service-icon service-icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 7l8 6 8-6"/></svg></span>
                    <span class="service-copy"><strong>Notifications</strong><small>In-app delivery</small></span>
                    <span class="service-state"><i></i>Available</span>
                </li>
            </ul>
        </section>
    </div>

    @if (count($stats))
        <section class="dashboard-stat-grid" aria-label="Key metrics">
            @foreach ($stats as $stat)
                <article class="dashboard-stat dashboard-panel">
                    <span class="stat-icon stat-icon-{{ $stat['tone'] }}">
                        @switch($stat['icon'])
                            @case('ticket')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h16v4a2.5 2.5 0 0 0 0 5v4H4v-4a2.5 2.5 0 0 0 0-5v-4Z"/><path d="M9 8.5h.01M9 12h.01M9 15.5h.01M13 8.5h4"/></svg>@break
                            @case('clock')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>@break
                            @case('check')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>@break
                            @case('users')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>@break
                            @case('pin')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>@break
                            @case('building')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21V4l8-2v19m0-13h8v13M2 21h20"/><path d="M7 7h2m-2 4h2m-2 4h2m8-3h2m-2 4h2"/></svg>@break
                            @default<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18M8 15l4-4 3 3 6-7"/></svg>
                        @endswitch
                    </span>
                    <div class="stat-copy"><p>{{ $stat['label'] }}</p><strong>{{ $stat['value'] }}</strong><small>{{ $stat['caption'] }}</small></div>
                </article>
            @endforeach
        </section>
    @endif

    <div class="dashboard-middle-grid">
        @if ($canViewTickets)
            <section class="dashboard-panel trend-panel" aria-labelledby="ticket-trend-heading">
                <header class="dashboard-panel-heading">
                    <div><p class="dashboard-panel-kicker">LAST 7 DAYS</p><h2 id="ticket-trend-heading">Ticket trends</h2></div>
                    <a class="panel-link" href="{{ route('tickets.index') }}">View queue <span aria-hidden="true">&rarr;</span></a>
                </header>
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
                    <div class="dashboard-empty-chart"><span class="empty-chart-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18M7 15l4-4 3 3 6-7"/></svg></span><strong>No ticket trend yet</strong><span>Ticket activity will appear here as requests come in.</span></div>
                @endif
            </section>
        @endif

        <section class="dashboard-panel quick-actions-panel" aria-labelledby="quick-actions-heading">
            <header class="dashboard-panel-heading"><div><p class="dashboard-panel-kicker">SHORTCUTS</p><h2 id="quick-actions-heading">Quick actions</h2></div></header>
            <div class="quick-action-list">
                @forelse ($quickActions as $action)
                    <a class="quick-action" href="{{ $action['url'] }}">
                        <span class="quick-action-icon quick-action-{{ $action['tone'] }}">
                            @if ($action['icon'] === 'plus-ticket')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h16v4a2.5 2.5 0 0 0 0 5v4H4v-4a2.5 2.5 0 0 0 0-5v-4Z"/><path d="M12 8v8m-4-4h8"/></svg>
                            @elseif ($action['icon'] === 'list')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            @elseif ($action['icon'] === 'users')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            @else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18M8 15l4-4 3 3 6-7"/></svg>@endif
                        </span>
                        <span class="quick-action-copy"><strong>{{ $action['label'] }}</strong><small>{{ $action['detail'] }}</small></span>
                        <span class="quick-action-arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                @empty
                    <p class="dashboard-empty-copy">No actions are available for this account.</p>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel activity-panel" aria-labelledby="recent-activity-heading">
            <header class="dashboard-panel-heading"><div><p class="dashboard-panel-kicker">LATEST UPDATES</p><h2 id="recent-activity-heading">Recent activity</h2></div></header>
            <ol class="activity-list">
                @forelse ($recentActivity as $activity)
                    <li class="activity-item">
                        <span class="activity-marker"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/></svg></span>
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
    </div>

    @if ($canViewTickets)
        <section class="dashboard-panel recent-tickets-panel" aria-labelledby="recent-tickets-heading">
            <header class="dashboard-panel-heading recent-tickets-heading">
                <div><p class="dashboard-panel-kicker">YOUR WORKSPACE</p><h2 id="recent-tickets-heading">Recent tickets</h2></div>
                <a class="panel-link" href="{{ route('tickets.index') }}">View all <span aria-hidden="true">&rarr;</span></a>
            </header>
            @if ($recentTickets->isNotEmpty())
                <div class="recent-tickets-table-wrap">
                    <table class="recent-tickets-table">
                        <thead><tr><th scope="col">Ticket</th><th scope="col">Category</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Updated</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                        @foreach ($recentTickets as $ticket)
                            <tr>
                                <td><a class="recent-ticket-link" href="{{ route('tickets.show', $ticket) }}"><span class="ticket-id">#{{ $ticket->id }}</span>{{ $ticket->subject }}</a></td>
                                <td>{{ $ticket->category?->name ?? 'Uncategorized' }}</td>
                                <td>{{ $ticket->priority?->name ?? '—' }}</td>
                                <td><span class="ticket-status ticket-status-{{ str_replace('_', '-', $ticket->status) }}">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span></td>
                                <td class="ticket-updated">{{ $ticket->updated_at?->format('j M · g:i A') }}</td>
                                <td><a class="ticket-open-link" href="{{ route('tickets.show', $ticket) }}" aria-label="Open ticket #{{ $ticket->id }}">Open</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="recent-tickets-empty"><span class="empty-ticket-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h16v4a2.5 2.5 0 0 0 0 5v4H4v-4a2.5 2.5 0 0 0 0-5v-4Z"/><path d="M9 8.5h.01M9 12h.01M9 15.5h.01"/></svg></span><strong>No recent tickets</strong><span>New and updated tickets visible to you will appear here.</span></div>
            @endif
        </section>
    @endif
</div>
@endsection
