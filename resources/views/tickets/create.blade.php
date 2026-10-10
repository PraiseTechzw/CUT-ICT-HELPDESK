@extends('layouts.app')

@section('title', 'Create a ticket')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <section>
        <h1 class="text-2xl font-semibold tracking-tight">Create a support ticket</h1>
        <p class="mt-2 text-sm text-slate-600">Describe the issue. Your ticket is visible to you and authorized support staff.</p>
    </section>

    <form class="space-y-5 rounded-xl border border-slate-200 bg-white p-6" method="POST" action="{{ route('tickets.store') }}">
        @csrf
        <div>
            <label class="block text-sm font-medium" for="subject">Subject</label>
            <input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="subject" name="subject" maxlength="200" required value="{{ old('subject') }}" aria-describedby="subject-error">
            <x-field-error field="subject" />
        </div>

        <div>
            <label class="block text-sm font-medium" for="description">What do you need help with?</label>
            <textarea class="mt-1 min-h-40 w-full rounded-md border border-slate-300 px-3 py-2" id="description" name="description" rows="7" maxlength="20000" required aria-describedby="description-error">{{ old('description') }}</textarea>
            <x-field-error field="description" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="ticket_category_id">Category (optional)</label>
                <select class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="ticket_category_id" name="ticket_category_id">
                    <option value="">Not classified</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('ticket_category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-field-error field="ticket_category_id" />
            </div>
            <div>
                <label class="block text-sm font-medium" for="ticket_priority_id">Priority (optional)</label>
                <select class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="ticket_priority_id" name="ticket_priority_id">
                    <option value="">Not set</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->id }}" @selected((string) old('ticket_priority_id') === (string) $priority->id)>{{ $priority->name }}</option>
                    @endforeach
                </select>
                <x-field-error field="ticket_priority_id" />
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Submit ticket</button>
            <a class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50" href="{{ route('tickets.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
