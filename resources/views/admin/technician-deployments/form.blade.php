@extends('layouts.app')
@section('title', $deployment->exists ? 'Edit deployment' : 'Record deployment')
@section('content')
<div class="max-w-2xl space-y-6">
    <div><a class="text-sm underline" href="{{ route('admin.technician-deployments.index') }}">Back to deployments</a><h1 class="mt-3 text-2xl font-semibold">{{ $deployment->exists ? 'Edit deployment' : 'Record temporary deployment' }}</h1><p class="mt-1 text-sm text-slate-600">Start and end are inclusive calendar dates. Overlaps are allowed and do not affect ticket authorization, assignment, or scheduling.</p></div>
    <form method="POST" action="{{ $deployment->exists ? route('admin.technician-deployments.update', $deployment) : route('admin.technician-deployments.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @if($deployment->exists) @method('PUT') @endif
        <div><label for="technician_id" class="block text-sm font-medium">Technician</label><select id="technician_id" name="technician_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Select a Technician-role account</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string)old('technician_id', $deployment->technician_id)===(string)$technician->id)>{{ $technician->name }} · {{ $technician->email }}{{ $technician->is_active ? '' : ' (inactive account)' }}</option>@endforeach</select><x-field-error field="technician_id" /></div>
        <div><label for="operational_zone_id" class="block text-sm font-medium">Operational zone</label><select id="operational_zone_id" name="operational_zone_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Select an existing zone</option>@foreach($zones as $zone)<option value="{{ $zone->id }}" @selected((string)old('operational_zone_id', $deployment->operational_zone_id)===(string)$zone->id)>{{ $zone->campus->name }} · {{ $zone->name }}{{ $zone->is_active ? '' : ' (inactive zone)' }}</option>@endforeach</select><x-field-error field="operational_zone_id" /></div>
        <div><label for="starts_on" class="block text-sm font-medium">From (inclusive)</label><input type="date" id="starts_on" name="starts_on" value="{{ old('starts_on', $deployment->starts_on?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="starts_on" /></div>
        <div><label for="ends_on" class="block text-sm font-medium">Through (inclusive)</label><input type="date" id="ends_on" name="ends_on" value="{{ old('ends_on', $deployment->ends_on?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="ends_on" /></div>
        <button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white">Save deployment</button>
    </form>
</div>
@endsection
