@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <h1 class="text-2xl font-semibold tracking-tight">Sign in</h1>
    <p class="mt-2 text-sm text-slate-600">Use an account issued by an authorized helpdesk administrator.</p>
    <form class="mt-6 space-y-5" method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">
            <x-field-error field="email" />
        </div>
        <div>
            <label for="password" class="block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300">
            <x-field-error field="password" />
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded border-slate-400"> Remember me</label>
        <button type="submit" class="w-full rounded-md bg-slate-900 px-4 py-2.5 font-semibold text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">Sign in</button>
    </form>
</div>
@endsection
