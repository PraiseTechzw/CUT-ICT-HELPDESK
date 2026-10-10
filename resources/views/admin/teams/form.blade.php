@extends('layouts.app')
@section('title', $team->exists ? 'Edit team' : 'Add team')
@section('content')
<div class="max-w-2xl space-y-6">
    <div><a class="text-sm underline" href="{{ route('admin.teams.index') }}">Back to teams</a><h1 class="mt-3 text-2xl font-semibold">{{ $team->exists ? 'Edit team' : 'Add team' }}</h1><p class="mt-1 text-sm text-slate-600">Team records are administrative metadata only; they do not change ticket access or assignment.</p></div>
    <form method="POST" action="{{ $team->exists ? route('admin.teams.update', $team) : route('admin.teams.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @if($team->exists) @method('PUT') @endif
        <div><label for="name" class="block text-sm font-medium">Team name</label><input id="name" name="name" value="{{ old('name', $team->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="name" /></div>
        <div><label for="slug" class="block text-sm font-medium">Slug <span class="font-normal text-slate-500">(optional; generated from team name when blank)</span></label><input id="slug" name="slug" value="{{ old('slug', $team->slug) }}" maxlength="100" pattern="[A-Za-z0-9_-]+" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="slug" /></div>
        <div><label for="description" class="block text-sm font-medium">Description <span class="font-normal text-slate-500">(optional)</span></label><textarea id="description" name="description" rows="3" maxlength="2000" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('description', $team->description) }}</textarea><x-field-error field="description" /></div>
        <div><label for="is_active" class="block text-sm font-medium">Status</label><select id="is_active" name="is_active" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="1" @selected((string)old('is_active', $team->exists ? (int)$team->is_active : 1)==='1')>Active</option><option value="0" @selected((string)old('is_active', $team->exists ? (int)$team->is_active : 1)==='0')>Inactive</option></select></div>
        <button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white">Save team</button>
    </form>
</div>
@endsection
