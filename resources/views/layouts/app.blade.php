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
    <header class="workspace-header">
        <a class="workspace-brand" href="{{ route('dashboard') }}" aria-label="Chinhoyi University of Technology Helpdesk home">
            <img src="{{ asset('logo.png') }}" alt="Chinhoyi University of Technology: Technology, Innovation, Wealth">
        </a>
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="helpdesk-sidebar" aria-expanded="false" aria-label="Open navigation">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        @if ($canViewTickets)
            <form class="workspace-search" method="GET" action="{{ route('tickets.index') }}" role="search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
                <input type="search" name="search" value="{{ request()->query('search') }}" maxlength="120" placeholder="Search tickets, users, assets, locations..." aria-label="Search tickets">
                <kbd>Ctrl + K</kbd>
            </form>
        @else
            <div class="workspace-search is-disabled" role="search" aria-label="Search unavailable for this account">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.2 4.2"/></svg>
                <input type="search" placeholder="Search tickets, users, assets, locations..." aria-label="Search unavailable for this account" disabled>
                <kbd>Ctrl + K</kbd>
            </div>
        @endif
        <div class="workspace-actions">
            <a class="header-icon-button" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' unread' : '' }}" title="Notifications">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                @if ($unreadNotificationCount > 0)<span class="header-notification-dot"></span>@endif
            </a>
            <button class="header-icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark appearance" title="Switch appearance">
                <svg class="theme-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                <svg class="theme-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.2A8.7 8.7 0 0 1 8.8 3.3 8.8 8.8 0 1 0 20.7 15.2Z"/></svg>
            </button>
            <details class="header-account">
                <summary aria-label="Account menu for {{ $user->name }}">
                    <span class="header-avatar" aria-hidden="true">{{ collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                    <span class="header-account-copy"><strong>{{ $user->name }}</strong><small>{{ $user->roles->pluck('name')->join(', ') ?: 'Helpdesk account' }}</small></span>
                    <span class="header-account-chevron" aria-hidden="true"></span>
                </summary>
                <div class="header-account-menu">
                    <a href="{{ route('notifications.index') }}">Notifications</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
                </div>
            </details>
        </div>
    </header>
    <div class="helpdesk-shell">
        <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close navigation"></button>
        <aside class="helpdesk-sidebar" id="helpdesk-sidebar" aria-label="Application navigation">
            <nav class="sidebar-nav" aria-label="Main navigation">
                <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/><path d="M9 21v-7h6v7"/></svg>
                    <span>Dashboard</span>
                </a>

                <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('tickets.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h16v4a2.5 2.5 0 0 0 0 5v4H4v-4a2.5 2.5 0 0 0 0-5v-4Z"/><path d="M9 8.5h.01M9 12h.01M9 15.5h.01M13 8.5h4"/></svg>
                            <span>Tickets</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('tickets.view_all')<a class="sidebar-sublink {{ request()->routeIs('tickets.index') && !request()->query('search') ? 'is-current' : '' }}" href="{{ route('tickets.index') }}">All Tickets</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">All Tickets</span>@endcan
                            @can('tickets.create')<a class="sidebar-sublink {{ request()->routeIs('tickets.create') ? 'is-current' : '' }}" href="{{ route('tickets.create') }}">Create Ticket</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Create Ticket</span>@endcan
                            @can('tickets.view_own')<a class="sidebar-sublink" href="{{ route('tickets.index') }}">My Tickets</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">My Tickets</span>@endcan
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">Overdue Tickets</span>
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink" href="{{ route('admin.ticket-catalogues.index') }}#categories-heading">Ticket Categories</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Categories</span>@endcan
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink" href="{{ route('admin.ticket-catalogues.index') }}#priorities-heading">Ticket Priorities</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Priorities</span>@endcan
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Statuses</span>
                        </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.users.*', 'admin.roles.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Users</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('users.view')<a class="sidebar-sublink {{ request()->routeIs('admin.users.*') ? 'is-current' : '' }}" href="{{ route('admin.users.index') }}">All Users</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">All Users</span>@endcan
                            @can('users.create')<a class="sidebar-sublink {{ request()->routeIs('admin.users.create') ? 'is-current' : '' }}" href="{{ route('admin.users.create') }}">Add User</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Add User</span>@endcan
                            @can('users.assign_roles')<a class="sidebar-sublink {{ request()->routeIs('admin.roles.*') ? 'is-current' : '' }}" href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Roles &amp; Permissions</span>@endcan
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">User Groups</span>
                        </div>
                    </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.campuses.*', 'admin.zones.*', 'admin.buildings.*', 'admin.rooms.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            <span>Locations</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('campuses.view')<a class="sidebar-sublink {{ request()->routeIs('admin.campuses.*') ? 'is-current' : '' }}" href="{{ route('admin.campuses.index') }}">Campuses</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Campuses</span>@endcan
                            @can('zones.view')<a class="sidebar-sublink {{ request()->routeIs('admin.zones.*') ? 'is-current' : '' }}" href="{{ route('admin.zones.index') }}">Zones</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Zones</span>@endcan
                            @can('buildings.view')<a class="sidebar-sublink {{ request()->routeIs('admin.buildings.*') ? 'is-current' : '' }}" href="{{ route('admin.buildings.index') }}">Buildings</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Buildings</span>@endcan
                            @can('rooms.view')<a class="sidebar-sublink {{ request()->routeIs('admin.rooms.*') ? 'is-current' : '' }}" href="{{ route('admin.rooms.index') }}">Rooms</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Rooms</span>@endcan
                        </div>
                    </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.teams.*', 'admin.team-memberships.*', 'admin.technician-deployments.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6m3-3h-6"/></svg>
                            <span>Teams &amp; Deployments</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.teams.*') ? 'is-current' : '' }}" href="{{ route('admin.teams.index') }}">Teams</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Teams</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.team-memberships.*') ? 'is-current' : '' }}" href="{{ route('admin.team-memberships.index') }}">Technicians</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Technicians</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.technician-deployments.*') ? 'is-current' : '' }}" href="{{ route('admin.technician-deployments.index') }}">Deployments</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Deployments</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink" href="{{ route('admin.team-memberships.index') }}">Memberships</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Memberships</span>@endcan
                        </div>
                    </details>

                <details class="sidebar-group" open>
                    <summary class="sidebar-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 8-9 5-9-5 9-5 9 5Z"/><path d="M3 8v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5"/></svg>
                        <span>Assets</span><span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-submenu">
                        <span class="sidebar-sublink is-unavailable">Equipment</span>
                        <span class="sidebar-sublink is-unavailable">Maintenance</span>
                        <span class="sidebar-sublink is-unavailable">Asset Categories</span>
                    </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg>
                            <span>Reports</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('tickets.view_all')<a class="sidebar-sublink {{ request()->routeIs('reports.tickets.*') ? 'is-current' : '' }}" href="{{ route('reports.tickets.index') }}">Ticket Reports</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Reports</span>@endcan
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">User Reports</span>
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">Asset Reports</span>
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">System Reports</span>
                        </div>
                    </details>

                <details class="sidebar-group" open>
                    <summary class="sidebar-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7v14m0-14C10.5 5.6 8.4 5 5 5H3v14h3c3.4 0 5.5.6 6 2m0-14c1.5-1.4 3.6-2 7-2h2v14h-3c-3.4 0-5.5.6-6 2"/><path d="M6 9h3m-3 4h3m6-4h3m-3 4h3"/></svg>
                        <span>Knowledge Base</span><span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <div class="sidebar-submenu">
                        <span class="sidebar-sublink is-unavailable">Articles</span>
                        <span class="sidebar-sublink is-unavailable">Categories</span>
                        <span class="sidebar-sublink is-unavailable">FAQs</span>
                    </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.ticket-catalogues.*', 'admin.audit.*') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 .1.1 1.2 1-.9 1.6-1.5-.5a8 8 0 0 1-1.5.9l-.3 1.6h-1.9l-.3-1.6a8 8 0 0 1-1.7 0l-.9 1.3-1.7-.7.2-1.6a8 8 0 0 1-1.1-1.2l-1.6.2-.7-1.7 1.3-.9a8 8 0 0 1 0-1.7l-1.3-.9.7-1.7 1.6.2a8 8 0 0 1 1.2-1.1l-.2-1.6 1.7-.7.9 1.3a8 8 0 0 1 1.7 0l.9-1.3 1.7.7-.2 1.6a8 8 0 0 1 1.1 1.2l1.6-.2.7 1.7-1.3.9a8 8 0 0 1 0 1.7l.2.1"/></svg>
                            <span>Settings</span><span class="sidebar-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="sidebar-submenu">
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">System Settings</span>
                            <span class="sidebar-sublink is-unavailable" aria-disabled="true">Email Templates</span>
                            <a class="sidebar-sublink {{ request()->routeIs('notifications.*') ? 'is-current' : '' }}" href="{{ route('notifications.index') }}">Notifications @if ($unreadNotificationCount > 0)<span class="sidebar-count">{{ $unreadNotificationCount }}</span>@endif</a>
                            @can('audit.view')<a class="sidebar-sublink {{ request()->routeIs('admin.audit.*') ? 'is-current' : '' }}" href="{{ route('admin.audit.index') }}">Audit Logs</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Audit Logs</span>@endcan
                        </div>
                    </details>
            </nav>

            <div class="sidebar-footer">
                <img class="sidebar-campus-art" src="{{ asset('sidebar-vector.png') }}" alt="" aria-hidden="true">
                <div class="sidebar-caption">Chinhoyi University of Technology<br>ICT Helpdesk System</div>
                <div class="sidebar-version">v1.0.0</div>
            </div>
        </aside>

        <div class="helpdesk-content">
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
