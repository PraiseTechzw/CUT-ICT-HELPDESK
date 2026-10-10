@extends('layouts.app')
@section('title', $campus->exists ? 'Edit campus' : 'Add campus')
@section('content')
<div class="max-w-2xl space-y-6"><div><a class="text-sm underline" href="{{ route('admin.campuses.index') }}">Back to campuses</a><h1 class="mt-3 text-2xl font-semibold">{{ $campus->exists ? 'Edit campus' : 'Add campus' }}</h1></div>
<form method="POST" action="{{ $campus->exists ? route('admin.campuses.update', $campus) : route('admin.campuses.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">@csrf @if($campus->exists)@method('PUT')@endif
<div><label for="name" class="block text-sm font-medium">Name</label><input id="name" name="name" value="{{ old('name', $campus->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="name" /></div>
<div><label for="code" class="block text-sm font-medium">Unique code</label><input id="code" name="code" value="{{ old('code', $campus->code) }}" required maxlength="32" pattern="[A-Za-z0-9_-]+" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="code" /></div>
<div><label for="description" class="block text-sm font-medium">Description <span class="font-normal text-slate-500">(optional)</span></label><textarea id="description" name="description" rows="3" maxlength="2000" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('description', $campus->description) }}</textarea><x-field-error field="description" /></div>
<div><label for="is_active" class="block text-sm font-medium">Status</label><select id="is_active" name="is_active" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="1" @selected((string)old('is_active', $campus->exists ? (int)$campus->is_active : 1)==='1')>Active</option><option value="0" @selected((string)old('is_active', $campus->exists ? (int)$campus->is_active : 1)==='0')>Inactive</option></select></div>
<button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white">Save campus</button></form></div>
@endsection
