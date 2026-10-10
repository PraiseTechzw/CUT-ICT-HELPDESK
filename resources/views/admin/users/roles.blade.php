@extends('layouts.app')

@section('title', 'Roles and permissions')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-semibold">Roles and permissions</h1><p class="mt-1 text-sm text-slate-600">These role categories and initial grants are proposed for review. The role catalogue is maintained centrally in code and database seed data.</p></div>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($roles as $role)
            <section class="rounded-xl border border-slate-200 bg-white p-5"><h2 class="font-semibold">{{ $role->name }}</h2><p class="mt-1 text-sm text-slate-600">{{ $role->description }}</p><h3 class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Granted permissions</h3><ul class="mt-2 space-y-1 text-sm">@forelse ($role->permissions->sortBy('slug') as $permission)<li><code>{{ $permission->slug }}</code> <span class="text-slate-600">— {{ $permission->name }}</span></li>@empty<li class="text-slate-600">No permissions.</li>@endforelse</ul></section>
        @endforeach
    </div>
</div>
@endsection
