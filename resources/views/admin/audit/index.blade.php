@extends('layouts.app')

@section('title', 'Audit events')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-semibold">Administrative audit events</h1><p class="mt-1 text-sm text-slate-600">Read-only history of account, role, and location administration. Passwords and submitted field values are not recorded.</p></div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left"><tr><th class="px-4 py-3">Time</th><th class="px-4 py-3">Actor</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Record</th><th class="px-4 py-3">Metadata</th></tr></thead><tbody class="divide-y divide-slate-100">
    @forelse ($events as $event)
        <tr><td class="whitespace-nowrap px-4 py-3">{{ $event->created_at?->toDayDateTimeString() }}</td><td class="px-4 py-3">{{ $event->actor?->name ?? 'System / removed actor' }}</td><td class="px-4 py-3"><code>{{ $event->action }}</code></td><td class="px-4 py-3">{{ class_basename($event->subject_type ?? '') }} #{{ $event->subject_id ?? '—' }}</td><td class="px-4 py-3"><code>{{ json_encode($event->metadata, JSON_UNESCAPED_SLASHES) }}</code></td></tr>
    @empty<tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No audit events recorded yet.</td></tr>@endforelse
    </tbody></table></div>
    {{ $events->links() }}
</div>
@endsection
