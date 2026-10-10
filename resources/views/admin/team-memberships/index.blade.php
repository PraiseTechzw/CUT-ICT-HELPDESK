@extends('layouts.app')
@section('title', 'Technician memberships')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><a class="text-sm underline" href="{{ route('admin.teams.index') }}">Back to teams</a><h1 class="mt-3 text-2xl font-semibold">Technician team memberships</h1><p class="mt-1 max-w-3xl text-sm text-slate-600">Start and end dates are inclusive calendar dates. A blank end date means ongoing. Periods may overlap across different teams; periods for the same technician and team may not overlap. These records do not grant ticket access.</p></div>
        <a class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white" href="{{ route('admin.team-memberships.create', $teamId ? ['team_id' => $teamId] : []) }}">Add membership</a>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th class="px-4 py-3">Technician</th><th class="px-4 py-3">Team</th><th class="px-4 py-3">Effective from</th><th class="px-4 py-3">Effective through</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($memberships as $membership)
                <tr><td class="px-4 py-3">{{ $membership->technician->name }}<span class="block text-xs text-slate-500">{{ $membership->technician->email }}{{ $membership->technician->is_active ? '' : ' · inactive account' }}</span></td><td class="px-4 py-3">{{ $membership->team->name }}{{ $membership->team->is_active ? '' : ' · inactive team' }}</td><td class="px-4 py-3">{{ $membership->starts_on->format('Y-m-d') }}</td><td class="px-4 py-3">{{ $membership->ends_on?->format('Y-m-d') ?? 'Ongoing (no end date)' }}</td><td class="px-4 py-3 text-right"><a class="underline" href="{{ route('admin.team-memberships.edit', $membership) }}">Edit dates</a></td></tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No technician memberships recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $memberships->links() }}
</div>
@endsection
