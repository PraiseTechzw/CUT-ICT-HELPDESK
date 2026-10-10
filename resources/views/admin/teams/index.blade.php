@extends('layouts.app')
@section('title', 'Teams')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-2xl font-semibold">Teams</h1><p class="mt-1 max-w-3xl text-sm text-slate-600">Generic, locally configured team records. No team names or zone mappings are preloaded. Memberships do not grant ticket access or change routing.</p></div>
        <a class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white" href="{{ route('admin.teams.create') }}">Add team</a>
    </div>
    <div class="flex flex-wrap gap-4 text-sm">
        <a class="underline" href="{{ route('admin.team-memberships.index') }}">Technician memberships</a>
        <a class="underline" href="{{ route('admin.technician-deployments.index') }}">Temporary deployments</a>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th class="px-4 py-3">Team</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Membership records</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($teams as $team)
                <tr>
                    <td class="px-4 py-3"><span class="font-medium">{{ $team->name }}</span>@if($team->description)<p class="mt-1 text-xs text-slate-600">{{ $team->description }}</p>@endif</td>
                    <td class="px-4 py-3"><code>{{ $team->slug }}</code></td>
                    <td class="px-4 py-3">{{ $team->memberships_count }}</td>
                    <td class="px-4 py-3">{{ $team->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="space-x-3 px-4 py-3 text-right"><a class="underline" href="{{ route('admin.team-memberships.index', ['team_id' => $team->id]) }}">Memberships</a><a class="underline" href="{{ route('admin.teams.edit', $team) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No teams configured. No sample teams are seeded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $teams->links() }}
</div>
@endsection
