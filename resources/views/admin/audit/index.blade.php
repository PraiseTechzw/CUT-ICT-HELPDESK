@extends('layouts.app')

@section('title', 'System audit events')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">System audit events</h1>
        <p class="mt-1 text-sm text-slate-600">Read-only event history for account, location, ticket, catalogue, team, and deployment actions. Passwords, ticket descriptions, and comment bodies are not recorded. This application view does not make the underlying database tamper-proof.</p>
    </div>

    <form method="GET" action="{{ route('admin.audit.index') }}" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="action" class="block text-sm font-medium">Action contains</label>
            <input id="action" name="action" type="text" maxlength="120" value="{{ $filters['action'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
        </div>
        <div>
            <label for="actor" class="block text-sm font-medium">Actor name contains</label>
            <input id="actor" name="actor" type="text" maxlength="120" value="{{ $filters['actor'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
        </div>
        <div>
            <label for="from" class="block text-sm font-medium">From (inclusive)</label>
            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
            @error('from')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="through" class="block text-sm font-medium">Through (inclusive)</label>
            <input id="through" name="through" type="date" value="{{ $filters['through'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
            @error('through')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-3 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filter events</button>
            <a href="{{ route('admin.audit.index') }}" class="text-sm underline">Clear filters</a>
            <span class="text-sm text-slate-600">Date filters require both dates and allow up to 366 inclusive calendar days.</span>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Time</th><th scope="col" class="px-4 py-3">Actor</th><th scope="col" class="px-4 py-3">Action</th><th scope="col" class="px-4 py-3">Record</th><th scope="col" class="px-4 py-3">Safe metadata</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($events as $event)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3">{{ $event->created_at?->timezone(config('app.timezone'))->toDayDateTimeString() }}</td>
                        <td class="px-4 py-3">{{ $event->actor?->name ?? 'System / removed actor' }}</td>
                        <td class="px-4 py-3"><code>{{ $event->action }}</code></td>
                        <td class="px-4 py-3">{{ class_basename($event->subject_type ?? '') }} #{{ $event->subject_id ?? '—' }}</td>
                        <td class="max-w-xl break-words px-4 py-3"><code>{{ json_encode($event->metadata, JSON_UNESCAPED_SLASHES) }}</code></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No audit events match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $events->links() }}
</div>
@endsection
