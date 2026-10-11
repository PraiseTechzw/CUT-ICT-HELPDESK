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
            <svg data-lucide="menu" aria-hidden="true"></svg>
        </button>
        @if ($canViewTickets)
            <form class="workspace-search" method="GET" action="{{ route('tickets.index') }}" role="search">
                <svg data-lucide="search" aria-hidden="true"></svg>
                <input type="search" name="search" value="{{ request()->query('search') }}" maxlength="120" placeholder="Search tickets, users, assets, locations..." aria-label="Search tickets">
                <kbd>Ctrl + K</kbd>
            </form>
        @else
            <div class="workspace-search is-disabled" role="search" aria-label="Search unavailable for this account">
                <svg data-lucide="search" aria-hidden="true"></svg>
                <input type="search" placeholder="Search tickets, users, assets, locations..." aria-label="Search unavailable for this account" disabled>
                <kbd>Ctrl + K</kbd>
            </div>
        @endif
        <div class="workspace-actions">
            <a class="header-icon-button" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' unread' : '' }}" title="Notifications">
                <svg data-lucide="bell" aria-hidden="true"></svg>
                @if ($unreadNotificationCount > 0)<span class="header-notification-dot"></span>@endif
            </a>
            <button class="header-icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark appearance" title="Switch appearance">
                <svg class="theme-sun" data-lucide="sun" aria-hidden="true"></svg>
                <svg class="theme-moon" data-lucide="moon" aria-hidden="true"></svg>
            </button>
            <details class="header-account">
                <summary aria-label="Account menu for {{ $user->name }}">
                    <span class="header-avatar" aria-hidden="true">{{ collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                    <span class="header-account-copy"><strong>{{ $user->name }}</strong><small>{{ $user->roles->pluck('name')->join(', ') ?: 'Helpdesk account' }}</small></span>
                    <svg class="header-account-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
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
                    <svg data-lucide="layout-dashboard" aria-hidden="true"></svg>
                    <span>Dashboard</span>
                </a>

                <details class="sidebar-group" open>
                    <summary class="sidebar-link {{ request()->routeIs('tickets.*', 'admin.ticket-catalogues.categories.*', 'admin.ticket-catalogues.priorities.*', 'admin.ticket-catalogues.statuses.*') ? 'is-active' : '' }}">
                            <svg data-lucide="ticket" aria-hidden="true"></svg>
                            <span>Tickets</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('tickets.view_all')<a class="sidebar-sublink {{ request()->routeIs('tickets.index') ? 'is-current' : '' }}" href="{{ route('tickets.index') }}">All Tickets</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">All Tickets</span>@endcan
                            @can('tickets.create')<a class="sidebar-sublink {{ request()->routeIs('tickets.create') ? 'is-current' : '' }}" href="{{ route('tickets.create') }}">Create Ticket</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Create Ticket</span>@endcan
                            @if ($user->can('tickets.view_own') || $user->can('tickets.view_assigned'))<a class="sidebar-sublink {{ request()->routeIs('tickets.mine') ? 'is-current' : '' }}" href="{{ route('tickets.mine') }}">My Tickets</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">My Tickets</span>@endif
                            @if ($canViewTickets)<a class="sidebar-sublink {{ request()->routeIs('tickets.overdue') ? 'is-current' : '' }}" href="{{ route('tickets.overdue') }}">Overdue Tickets</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Overdue Tickets</span>@endif
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.ticket-catalogues.categories.*') ? 'is-current' : '' }}" href="{{ route('admin.ticket-catalogues.categories.index') }}">Ticket Categories</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Categories</span>@endcan
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.ticket-catalogues.priorities.*') ? 'is-current' : '' }}" href="{{ route('admin.ticket-catalogues.priorities.index') }}">Ticket Priorities</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Priorities</span>@endcan
                            @can('tickets.catalogues.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.ticket-catalogues.statuses.*') ? 'is-current' : '' }}" href="{{ route('admin.ticket-catalogues.statuses.index') }}">Ticket Statuses</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Ticket Statuses</span>@endcan
                        </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.departments.*') ? 'is-active' : '' }}">
                            <svg data-lucide="users-round" aria-hidden="true"></svg>
                            <span>Users</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('users.view')<a class="sidebar-sublink {{ request()->routeIs('admin.users.*') ? 'is-current' : '' }}" href="{{ route('admin.users.index') }}">All Users</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">All Users</span>@endcan
                            @can('users.create')<a class="sidebar-sublink {{ request()->routeIs('admin.users.create') ? 'is-current' : '' }}" href="{{ route('admin.users.create') }}">Add User</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Add User</span>@endcan
                            @can('users.assign_roles')<a class="sidebar-sublink {{ request()->routeIs('admin.roles.*') ? 'is-current' : '' }}" href="{{ route('admin.roles.index') }}">Roles &amp; Permissions</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Roles &amp; Permissions</span>@endcan
                            @can('user_groups.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.user-groups.*') ? 'is-current' : '' }}" href="{{ route('admin.user-groups.index') }}">User Groups</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">User Groups</span>@endcan
                            <a class="sidebar-sublink {{ request()->routeIs('admin.departments.*') ? 'is-current' : '' }}" href="{{ route('admin.departments.index') }}">Departments</a>
                        </div>
                    </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.campuses.*', 'admin.zones.*', 'admin.buildings.*', 'admin.rooms.*') ? 'is-active' : '' }}">
                            <svg data-lucide="map-pin" aria-hidden="true"></svg>
                            <span>Locations</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
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
                            <svg data-lucide="users-round" aria-hidden="true"></svg>
                            <span>Teams &amp; Deployments</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
                        </summary>
                        <div class="sidebar-submenu">
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.teams.*') ? 'is-current' : '' }}" href="{{ route('admin.teams.index') }}">Teams</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Teams</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.team-memberships.*') ? 'is-current' : '' }}" href="{{ route('admin.team-memberships.index') }}">Technicians</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Technicians</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink {{ request()->routeIs('admin.technician-deployments.*') ? 'is-current' : '' }}" href="{{ route('admin.technician-deployments.index') }}">Deployments</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Deployments</span>@endcan
                            @can('teams.manage')<a class="sidebar-sublink" href="{{ route('admin.team-memberships.index') }}">Memberships</a>@else<span class="sidebar-sublink is-unavailable" aria-disabled="true">Memberships</span>@endcan
                        </div>
                    </details>

                <details class="sidebar-group" open>
                    <summary class="sidebar-link {{ request()->routeIs('admin.equipment.*') ? 'is-active' : '' }}">
                        <svg data-lucide="package" aria-hidden="true"></svg>
                        <span>Assets</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
                    </summary>
                    <div class="sidebar-submenu">
                        <a class="sidebar-sublink {{ request()->routeIs('admin.equipment.*') ? 'is-current' : '' }}" href="{{ route('admin.equipment.index') }}">Equipment</a>
                        <span class="sidebar-sublink is-unavailable">Maintenance</span>
                        <span class="sidebar-sublink is-unavailable">Asset Categories</span>
                    </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">
                            <svg data-lucide="chart-no-axes-column-increasing" aria-hidden="true"></svg>
                            <span>Reports</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
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
                        <svg data-lucide="book-open" aria-hidden="true"></svg>
                        <span>Knowledge Base</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
                    </summary>
                    <div class="sidebar-submenu">
                        <span class="sidebar-sublink is-unavailable">Articles</span>
                        <span class="sidebar-sublink is-unavailable">Categories</span>
                        <span class="sidebar-sublink is-unavailable">FAQs</span>
                    </div>
                </details>

                    <details class="sidebar-group" open>
                        <summary class="sidebar-link {{ request()->routeIs('admin.ticket-catalogues.*', 'admin.audit.*') ? 'is-active' : '' }}">
                            <svg data-lucide="settings" aria-hidden="true"></svg>
                            <span>Settings</span><svg class="sidebar-chevron" data-lucide="chevron-down" aria-hidden="true"></svg>
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
