<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helpdesk') · {{ config('app.name', 'CUT ICT Helpdesk') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a class="text-lg font-semibold tracking-tight" href="{{ auth()->check() ? route('dashboard') : route('login') }}">CUT ICT Helpdesk</a>
        @auth
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <a class="underline-offset-4 hover:underline" href="{{ route('dashboard') }}">Home</a>
                @can('users.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.users.index') }}">Accounts</a>@endcan
                @can('users.assign_roles')<a class="underline-offset-4 hover:underline" href="{{ route('admin.roles.index') }}">Roles</a>@endcan
                @can('campuses.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.campuses.index') }}">Campuses</a>@endcan
                @can('zones.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.zones.index') }}">Zones</a>@endcan
                @can('buildings.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.buildings.index') }}">Buildings</a>@endcan
                @can('rooms.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.rooms.index') }}">Rooms</a>@endcan
                @can('audit.view')<a class="underline-offset-4 hover:underline" href="{{ route('admin.audit.index') }}">Audit</a>@endcan
                <span class="text-slate-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-100" type="submit">Sign out</button>
                </form>
            </nav>
        @endauth
    </div>
</header>
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    @if (session('status'))
        <div role="status" class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
<footer class="mx-auto max-w-7xl px-4 py-6 text-xs text-slate-500 sm:px-6 lg:px-8">CUT branding assets and official interface standards are pending institutional approval.</footer>
</body>
</html>
