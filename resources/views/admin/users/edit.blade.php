@extends('layouts.app')

@section('title', 'Edit account')

@section('content')
<div class="max-w-2xl space-y-6">
    <div><a class="text-sm underline" href="{{ route('admin.users.show', $user) }}">Back to account</a><h1 class="mt-3 text-2xl font-semibold">Edit profile</h1></div>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @method('PUT')
        <div><label for="name" class="block text-sm font-medium">Name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="name" /></div>
        <div><label for="email" class="block text-sm font-medium">Email</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="email" /></div>
        <p class="text-sm text-slate-600">Passwords, account status, and role assignments are managed through separate authorized workflows.</p>
        <button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Save profile</button>
    </form>
</div>
@endsection
