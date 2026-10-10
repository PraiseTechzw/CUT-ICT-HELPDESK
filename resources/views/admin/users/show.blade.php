@extends('layouts.app')

@section('title', 'Account details')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4"><div><a class="text-sm underline" href="{{ route('admin.users.index') }}">Back to accounts</a><h1 class="mt-3 text-2xl font-semibold">{{ $user->name }}</h1><p class="mt-1 text-slate-600">{{ $user->email }}</p></div>@can('users.edit')<a class="rounded-md border border-slate-300 px-4 py-2 font-medium" href="{{ route('admin.users.edit', $user) }}">Edit profile</a>@endcan</div>
    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">Account status</h2><p class="mt-2">{{ $user->is_active ? 'Active' : 'Inactive' }}</p>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2"><div><dt class="font-medium">Created</dt><dd>{{ $user->created_at?->toDayDateTimeString() }}</dd></div><div><dt class="font-medium">Last updated</dt><dd>{{ $user->updated_at?->toDayDateTimeString() }}</dd></div></dl>
        @can('users.deactivate')
            @unless (auth()->user()->is($user))
                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="mt-5">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}"><button class="rounded-md border border-slate-300 px-4 py-2 font-medium hover:bg-slate-100">{{ $user->is_active ? 'Deactivate account' : 'Activate account' }}</button><x-field-error field="is_active" /></form>
            @endunless
        @endcan
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">Assigned roles</h2>
        <ul class="mt-3 flex flex-wrap gap-2">@forelse ($user->roles as $role)<li class="rounded-full bg-slate-100 px-3 py-1 text-sm">{{ $role->name }}</li>@empty<li class="text-sm text-slate-600">No assigned roles.</li>@endforelse</ul>
        @can('users.assign_roles')
            @unless (auth()->user()->is($user))
                <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" class="mt-6 border-t border-slate-200 pt-5">@csrf @method('PUT')<input type="hidden" name="role_ids_present" value="1"><fieldset><legend class="font-medium">Change roles</legend><p class="mt-1 text-sm text-slate-600">Role labels are proposed and require CUT ICT approval. No extra role is assigned by default. Clear every role to revoke all application access.</p><div class="mt-3 grid gap-2 sm:grid-cols-2">@foreach ($roles as $role)<label class="flex items-start gap-2 rounded-md border border-slate-200 p-3"><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $user->roles->modelKeys()))) class="mt-1 rounded border-slate-400"><span><span class="block font-medium">{{ $role->name }}</span><span class="text-xs text-slate-600">{{ $role->description }}</span></span></label>@endforeach</div><x-field-error field="role_ids" /><button class="mt-4 rounded-md bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Save roles</button></fieldset></form>
            @endunless
        @endcan
    </section>
</div>
@endsection
