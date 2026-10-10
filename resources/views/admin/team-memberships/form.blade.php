@extends('layouts.app')
@section('title', $membership->exists ? 'Edit technician membership' : 'Add technician membership')
@section('content')
<div class="max-w-2xl space-y-6">
    <div><a class="text-sm underline" href="{{ route('admin.team-memberships.index') }}">Back to memberships</a><h1 class="mt-3 text-2xl font-semibold">{{ $membership->exists ? 'Edit technician membership' : 'Add technician membership' }}</h1><p class="mt-1 text-sm text-slate-600">Dates are inclusive. Leave the end date blank for an ongoing membership. Different-team periods may overlap; same-team periods may not.</p></div>
    <form method="POST" action="{{ $membership->exists ? route('admin.team-memberships.update', $membership) : route('admin.team-memberships.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @if($membership->exists) @method('PUT') @endif
        <div><label for="technician_id" class="block text-sm font-medium">Technician</label><select id="technician_id" name="technician_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Select a Technician-role account</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string)old('technician_id', $membership->technician_id)===(string)$technician->id)>{{ $technician->name }} · {{ $technician->email }}{{ $technician->is_active ? '' : ' (inactive account)' }}</option>@endforeach</select><x-field-error field="technician_id" /></div>
        <div><label for="team_id" class="block text-sm font-medium">Team</label><select id="team_id" name="team_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Select a team</option>@foreach($teams as $team)<option value="{{ $team->id }}" @selected((string)old('team_id', $membership->team_id)===(string)$team->id)>{{ $team->name }}{{ $team->is_active ? '' : ' (inactive team)' }}</option>@endforeach</select><x-field-error field="team_id" /></div>
        <div><label for="starts_on" class="block text-sm font-medium">Effective from (inclusive)</label><input type="date" id="starts_on" name="starts_on" value="{{ old('starts_on', $membership->starts_on?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="starts_on" /></div>
        <div><label for="ends_on" class="block text-sm font-medium">Effective through (inclusive) <span class="font-normal text-slate-500">(blank means ongoing)</span></label><input type="date" id="ends_on" name="ends_on" value="{{ old('ends_on', $membership->ends_on?->format('Y-m-d')) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="ends_on" /></div>
        <button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white">Save membership</button>
    </form>
</div>
@endsection
