<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helpdesk') · {{ config('app.name', 'CUT ICT Helpdesk') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="helpdesk-body">
@auth
    @php
        $user = auth()->user();
        $canViewTickets = $user->can('tickets.view_own') || $user->can('tickets.view_all') || $user->can('tickets.view_assigned');
        $unreadNotificationCount = $user->unreadNotifications()->count();
    @endphp
    <div class="helpdesk-shell">
        <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close navigation"></button>
        <aside class="helpdesk-sidebar" id="helpdesk-sidebar" aria-label="Application navigation">
            <a class="sidebar-brand" href="{{ route('dashboard') }}" aria-label="Chinhoyi University of Technology Helpdesk home">
                <img src="{{ asset('logo.png') }}" alt="Chinhoyi University of Technology: Technology, Innovation, Wealth">
            </a>
            <nav class="sidebar-nav" aria-label="Main navigation">
                <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                    <span>Dashboard</span>
                </a>

                @if ($canViewTickets)
                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('tickets.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v4a3 3 0 0 0 0 6v4H4v-4a3 3 0 0 0 0-6zM9 8v2m0 4v2"/></svg>
                            <span>Tickets</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('tickets.index') ? 'is-current' : '' }}" href="{{ route('tickets.index') }}">All tickets</a>
                            @can('tickets.create')<a class="sidebar-sublink {{ request()->routeIs('tickets.create') ? 'is-current' : '' }}" href="{{ route('tickets.create') }}">Create ticket</a>@endcan
                            <a class="sidebar-sublink {{ request()->routeIs('notifications.*') ? 'is-current' : '' }}" href="{{ route('notifications.index') }}">Notifications @if ($unreadNotificationCount > 0)<span class="sidebar-count">{{ $unreadNotificationCount }}</span>@endif</a>
                        </div>
                    </details>
                @endif

                @can('users.view')
                    <a class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm6-3a4 4 0 0 1 0 8m2 1a4 4 0 0 1 3 4v1"/></svg>
                        <span>Users</span><span class="sidebar-chevron" aria-hidden="true"></span>
                    </a>
                @endcan

                @if ($user->can('campuses.view') || $user->can('zones.view') || $user->can('buildings.view') || $user->can('rooms.view'))
                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.campuses.*', 'admin.zones.*', 'admin.buildings.*', 'admin.rooms.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0zm-5 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                            <span>Locations</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('campuses.view')<a class="sidebar-sublink {{ request()->routeIs('admin.campuses.*') ? 'is-current' : '' }}" href="{{ route('admin.campuses.index') }}">Campuses</a>@endcan
                            @can('zones.view')<a class="sidebar-sublink {{ request()->routeIs('admin.zones.*') ? 'is-current' : '' }}" href="{{ route('admin.zones.index') }}">Zones</a>@endcan
                            @can('buildings.view')<a class="sidebar-sublink {{ request()->routeIs('admin.buildings.*') ? 'is-current' : '' }}" href="{{ route('admin.buildings.index') }}">Buildings</a>@endcan
                            @can('rooms.view')<a class="sidebar-sublink {{ request()->routeIs('admin.rooms.*') ? 'is-current' : '' }}" href="{{ route('admin.rooms.index') }}">Rooms</a>@endcan
                        </div>
                    </details>
                @endif

                @can('teams.manage')
                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.teams.*', 'admin.team-memberships.*', 'admin.technician-deployments.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm8-7v6m3-3h-6m3 5a4 4 0 0 1 3 4v2"/></svg>
                            <span>Teams &amp; Deployments</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('admin.teams.*') ? 'is-current' : '' }}" href="{{ route('admin.teams.index') }}">Teams</a>
                            <a class="sidebar-sublink {{ request()->routeIs('admin.team-memberships.*') ? 'is-current' : '' }}" href="{{ route('admin.team-memberships.index') }}">Technicians</a>
                            <a class="sidebar-sublink {{ request()->routeIs('admin.technician-deployments.*') ? 'is-current' : '' }}" href="{{ route('admin.technician-deployments.index') }}">Deployments</a>
                        </div>
                    </details>
                @endcan

                <details class="sidebar-group" open>
                    <summary class="sidebar-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18v13H3zM3 7l2-4h14l2 4M8 11h8m-8 4h5"/></svg>
                        <span>Assets</span><span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-submenu">
                        <span class="sidebar-sublink is-unavailable">Equipment</span>
                        <span class="sidebar-sublink is-unavailable">Maintenance</span>
                    </div>
                </details>

                @can('tickets.view_all')
                    <details class="sidebar-group">
                        <summary class="sidebar-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15v-4m5 4V7m5 8V9"/></svg>
                            <span>Reports</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu"><a class="sidebar-sublink {{ request()->routeIs('reports.tickets.*') ? 'is-current' : '' }}" href="{{ route('reports.tickets.index') }}">Ticket analytics</a></div>
                    </details>
                @endcan

                <details class="sidebar-group">
                    <summary class="sidebar-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4zM8 8h8m-8 4h8m-8 4h5"/></svg>
                        <span>Knowledge Base</span><span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-submenu">
                        <span class="sidebar-sublink is-unavailable">Articles</span>
                        <span class="sidebar-sublink is-unavailable">FAQs</span>
                    </div>
                </details>

                @if ($user->can('tickets.catalogues.manage') || $user->can('users.assign_roles') || $user->can('audit.view'))
                    <details class="sidebar-group">
                        <summary class="sidebar-link {{ request()->routeIs('admin.ticket-catalogues.*', 'admin.roles.*', 'admin.audit.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36-1.42 1.42M7.06 16.94l-1.42 1.42m12.72 0-1.42-1.42M7.06 7.06 5.64 5.64M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/></svg>
                            <span>Settings</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.ticket-catalogues.*') ? 'is-current' : '' }}" href="{{ route('admin.ticket-catalogues.index') }}">Ticket settings</a>@endcan
                            @can('users.assign_roles')<a class="sidebar-sublink {{ request()->routeIs('admin.roles.*') ? 'is-current' : '' }}" href="{{ route('admin.roles.index') }}">Roles</a>@endcan
                            @can('audit.view')<a class="sidebar-sublink {{ request()->routeIs('admin.audit.*') ? 'is-current' : '' }}" href="{{ route('admin.audit.index') }}">Audit log</a>@endcan
                        </div>
                    </details>
                @endif
            </nav>

            <div class="sidebar-footer">
                <img class="sidebar-campus-art" src="{{ asset('sidebar-vector.png') }}" alt="" aria-hidden="true">
                <a class="sidebar-user" href="{{ route('notifications.index') }}" aria-label="Notifications for {{ $user->name }}">
                    <span class="sidebar-user-name">{{ $user->name }}</span>
                    @if ($unreadNotificationCount > 0)<span class="sidebar-count">{{ $unreadNotificationCount }}</span>@endif
                </a>
                <div class="sidebar-caption">Chinhoyi University of Technology<br>ICT Helpdesk System</div>
                <div class="sidebar-version">v1.0.0</div>
                <form method="POST" action="{{ route('logout') }}" class="sidebar-logout-form">
                    @csrf
                    <button class="sidebar-logout" type="submit">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="helpdesk-content">
            <header class="workspace-header">
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="helpdesk-sidebar" aria-expanded="false" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="workspace-title">{{ config('app.name', 'CUT ICT Helpdesk') }}</span>
            </header>
            <main class="workspace-main">
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
@else
    <header class="guest-header">
        <a class="guest-brand" href="{{ route('login') }}"><img src="{{ asset('logo.png') }}" alt="Chinhoyi University of Technology"></a>
    </header>
    <main class="guest-main">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
@endauth
</body>
</html>
