@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><h1 class="text-2xl font-semibold tracking-tight">User accounts</h1><p class="mt-1 text-sm text-slate-600">Search and review accounts. Access to changes is separately permissioned.</p></div>
        @can('users.create')<a class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" href="{{ route('admin.users.create') }}">Create account</a>@endcan
    </div>
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex max-w-xl gap-2">
        <label class="sr-only" for="search">Search accounts</label>
        <input id="search" name="search" value="{{ $search }}" maxlength="120" class="min-w-0 flex-1 rounded-md border border-slate-300 px-3 py-2" placeholder="Name or email">
        <button class="rounded-md border border-slate-300 px-4 py-2 font-medium hover:bg-slate-100">Search</button>
    </form>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3 font-semibold">Name</th><th scope="col" class="px-4 py-3 font-semibold">Email</th><th scope="col" class="px-4 py-3 font-semibold">Roles</th><th scope="col" class="px-4 py-3 font-semibold">Status</th><th scope="col" class="px-4 py-3 font-semibold"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <tr><td class="px-4 py-3 font-medium">{{ $user->name }}</td><td class="px-4 py-3">{{ $user->email }}</td><td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</td><td class="px-4 py-3">{{ $user->is_active ? 'Active' : 'Inactive' }}</td><td class="px-4 py-3 text-right"><a class="font-medium underline" href="{{ route('admin.users.show', $user) }}">View</a></td></tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">No accounts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
