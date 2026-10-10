@extends('layouts.app')
@section('title', 'Technician deployments')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><a class="text-sm underline" href="{{ route('admin.teams.index') }}">Back to teams</a><h1 class="mt-3 text-2xl font-semibold">Temporary technician deployments</h1><p class="mt-1 max-w-3xl text-sm text-slate-600">Deployment dates are inclusive calendar dates. Overlapping deployments are permitted as records; they are not shifts, exclusive assignments, or ticket access grants.</p></div>
        <a class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white" href="{{ route('admin.technician-deployments.create') }}">Record deployment</a>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th class="px-4 py-3">Technician</th><th class="px-4 py-3">Operational zone</th><th class="px-4 py-3">From (inclusive)</th><th class="px-4 py-3">Through (inclusive)</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($deployments as $deployment)
                <tr><td class="px-4 py-3">{{ $deployment->technician->name }}<span class="block text-xs text-slate-500">{{ $deployment->technician->email }}{{ $deployment->technician->is_active ? '' : ' · inactive account' }}</span></td><td class="px-4 py-3">{{ $deployment->operationalZone->campus->name }} · {{ $deployment->operationalZone->name }}{{ $deployment->operationalZone->is_active ? '' : ' · inactive zone' }}</td><td class="px-4 py-3">{{ $deployment->starts_on->format('Y-m-d') }}</td><td class="px-4 py-3">{{ $deployment->ends_on->format('Y-m-d') }}</td><td class="px-4 py-3 text-right"><a class="underline" href="{{ route('admin.technician-deployments.edit', $deployment) }}">Edit dates</a></td></tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No deployments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $deployments->links() }}
</div>
@endsection
