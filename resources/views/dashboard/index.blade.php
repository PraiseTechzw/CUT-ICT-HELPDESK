@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="space-y-6">
    <section>
        <h1 class="text-2xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-600">Your access is limited to the permissions assigned to your account.</p>
    </section>
    @if (auth()->user()->can('tickets.create') || auth()->user()->can('tickets.view_own') || auth()->user()->can('tickets.view_assigned') || auth()->user()->can('tickets.view_all'))
        <section aria-labelledby="ticketing-title" class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold" id="ticketing-title">Ticketing</h2>
            <ul class="mt-4 flex flex-wrap gap-3">
                @if (auth()->user()->can('tickets.view_own') || auth()->user()->can('tickets.view_assigned') || auth()->user()->can('tickets.view_all'))
                    <li><a class="inline-block rounded-md border border-slate-200 px-4 py-3 font-medium hover:bg-slate-50" href="{{ route('tickets.index') }}">Open my ticket list or queue</a></li>
                @endif
                @can('tickets.create')<li><a class="inline-block rounded-md border border-slate-200 px-4 py-3 font-medium hover:bg-slate-50" href="{{ route('tickets.create') }}">Create a support ticket</a></li>@endcan
            </ul>
        </section>
    @endif
    <section aria-labelledby="phase-one-title" class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 id="phase-one-title" class="text-lg font-semibold">Administration</h2>
        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @can('users.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.users.index') }}">User accounts</a></li>@endcan
            @can('users.assign_roles')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.roles.index') }}">Roles and permissions</a></li>@endcan
            @can('campuses.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.campuses.index') }}">Campuses</a></li>@endcan
            @can('zones.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.zones.index') }}">Operational zones</a></li>@endcan
            @can('buildings.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.buildings.index') }}">Buildings</a></li>@endcan
            @can('rooms.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.rooms.index') }}">Rooms</a></li>@endcan
            @can('audit.view')<li><a class="block rounded-md border border-slate-200 p-4 font-medium hover:bg-slate-50" href="{{ route('admin.audit.index') }}">Audit events</a></li>@endcan
        </ul>
    </section>
</div>
@endsection
