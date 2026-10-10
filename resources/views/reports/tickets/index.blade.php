@extends('layouts.app')
@section('title', 'Ticket analytics')
@section('content')
<div class="space-y-8">
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold">Ticket analytics</h1>
        <p class="max-w-4xl text-sm text-slate-600">Aggregate counts only for tickets created during the selected range. Status counts reflect current status at report time. No requester, technician, ticket title, description, or ticket link is included. Calendar dates use the configured application timezone ({{ $timezone }}).</p>
    </header>

    <form method="GET" action="{{ route('reports.tickets.index') }}" class="flex flex-wrap items-end gap-4 rounded-xl border border-slate-200 bg-white p-4">
        <div><label for="from" class="block text-sm font-medium">From (inclusive)</label><input type="date" id="from" name="from" value="{{ old('from', $from) }}" required class="mt-1 rounded-md border border-slate-300 px-3 py-2"><x-field-error field="from" /></div>
        <div><label for="through" class="block text-sm font-medium">Through (inclusive)</label><input type="date" id="through" name="through" value="{{ old('through', $through) }}" required class="mt-1 rounded-md border border-slate-300 px-3 py-2"><x-field-error field="through" /></div>
        <button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white">Apply range</button>
    </form>

    <section aria-labelledby="summary-heading" class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 id="summary-heading" class="text-lg font-semibold">Summary · {{ $from }} to {{ $through }}</h2>
        <p class="mt-2 text-3xl font-semibold tabular-nums">{{ number_format($report['total']) }}</p>
        <p class="text-sm text-slate-600">Tickets created during this period</p>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section aria-labelledby="daily-heading" class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 id="daily-heading" class="text-lg font-semibold">Daily ticket volume</h2>
            <p class="mt-1 text-sm text-slate-600">The table includes zero-volume dates; bars are a visual aid.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="border-b text-left"><th scope="col" class="py-2 pr-4">Date</th><th scope="col" class="py-2 pr-4">Volume</th><th scope="col" class="py-2 text-right">Tickets</th></tr></thead>
                    <tbody>
                    @foreach($report['daily'] as $point)
                        <tr class="border-b border-slate-100"><th scope="row" class="py-2 pr-4 font-normal">{{ $point['date'] }}</th><td class="w-1/2 py-2 pr-4"><div class="h-3 rounded bg-slate-100"><div class="h-3 rounded bg-blue-700" style="width: {{ $point['percentage'] }}%" aria-hidden="true"></div></div></td><td class="py-2 text-right tabular-nums">{{ number_format($point['total']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="status-heading" class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 id="status-heading" class="text-lg font-semibold">Current status of tickets created in range</h2>
            <p class="mt-1 text-sm text-slate-600">Status is a snapshot at the time this report is generated, not historical status as of each date.</p>
            <ul class="mt-4 divide-y divide-slate-100">
                @forelse($report['statuses'] as $item)
                    <li class="flex justify-between gap-4 py-2"><span>{{ $item['label'] }}</span><span class="font-medium tabular-nums">{{ number_format($item['total']) }}</span></li>
                @empty
                    <li class="py-3 text-sm text-slate-600">No tickets were created in this period.</li>
                @endforelse
            </ul>
        </section>

        <section aria-labelledby="category-heading" class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 id="category-heading" class="text-lg font-semibold">Tickets by category</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @forelse($report['categories'] as $item)
                    <li class="flex justify-between gap-4 py-2"><span>{{ $item['label'] }}</span><span class="font-medium tabular-nums">{{ number_format($item['total']) }}</span></li>
                @empty
                    <li class="py-3 text-sm text-slate-600">No tickets were created in this period.</li>
                @endforelse
            </ul>
        </section>

        <section aria-labelledby="priority-heading" class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 id="priority-heading" class="text-lg font-semibold">Tickets by priority</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @forelse($report['priorities'] as $item)
                    <li class="flex justify-between gap-4 py-2"><span>{{ $item['label'] }}</span><span class="font-medium tabular-nums">{{ number_format($item['total']) }}</span></li>
                @empty
                    <li class="py-3 text-sm text-slate-600">No tickets were created in this period.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <p class="text-xs text-slate-500">This is a provisional operations summary, not a service-level, workload, productivity, or performance assessment. Audience, metric definitions, reporting timezone, retention, and export policy require CUT ICT confirmation.</p>
</div>
@endsection
