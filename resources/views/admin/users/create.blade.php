@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="max-w-2xl space-y-6">
    <div><a class="text-sm underline" href="{{ route('admin.users.index') }}">Back to accounts</a><h1 class="mt-3 text-2xl font-semibold">Create account</h1></div>
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        <div><label for="name" class="block text-sm font-medium">Name</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="name" /></div>
        <div><label for="email" class="block text-sm font-medium">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><x-field-error field="email" /></div>
        <div><label for="password" class="block text-sm font-medium">Initial password</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><p class="mt-1 text-xs text-slate-600">Use at least 12 characters. Share credentials only through a CUT-approved secure channel.</p><x-field-error field="password" /></div>
        <div><label for="password_confirmation" class="block text-sm font-medium">Confirm initial password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></div>
        <div class="flex gap-3"><button class="rounded-md bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Create account</button><a class="rounded-md border border-slate-300 px-4 py-2" href="{{ route('admin.users.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
