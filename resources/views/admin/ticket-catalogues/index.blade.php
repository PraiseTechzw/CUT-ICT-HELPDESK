@extends('layouts.app')

@section('title', 'Ticket catalogues')

@section('content')
<div class="space-y-8">
    <section>
        <h1 class="text-2xl font-semibold tracking-tight">Ticket categories and priorities</h1>
        <p class="mt-2 text-sm text-slate-600">No CUT-specific values are preloaded. Add only values approved for institutional use. Existing values can be deactivated but not deleted.</p>
    </section>

    <section aria-labelledby="categories-heading" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold" id="categories-heading">Categories</h2>
        <form class="grid gap-3 sm:grid-cols-4" method="POST" action="{{ route('admin.ticket-catalogues.categories.store') }}">
            @csrf
            <div><label class="block text-sm font-medium" for="category-name">New category</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="category-name" name="name" maxlength="100" required></div>
            <div><label class="block text-sm font-medium" for="category-order">Display order</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="category-order" name="sort_order" type="number" min="0" max="65535" value="0" required></div>
            <input name="is_active" type="hidden" value="0">
            <label class="flex items-center gap-2 self-end py-2 text-sm"><input name="is_active" type="checkbox" value="1" checked> Active</label>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Add category</button>
        </form>
        <x-field-error field="name" />
        <x-field-error field="slug" />
        <x-field-error field="sort_order" />
        <x-field-error field="is_active" />
        @if ($categories->isEmpty())
            <p class="text-sm text-slate-600">No categories configured.</p>
        @else
            <div class="space-y-3">
                @foreach ($categories as $category)
                    <form class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-5" method="POST" action="{{ route('admin.ticket-catalogues.categories.update', $category) }}">
                        @csrf
                        @method('PATCH')
                        <div><label class="block text-xs font-medium" for="category-name-{{ $category->id }}">Name</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="category-name-{{ $category->id }}" name="name" maxlength="100" value="{{ $category->name }}" required></div>
                        <div><label class="block text-xs font-medium" for="category-slug-{{ $category->id }}">Slug</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="category-slug-{{ $category->id }}" name="slug" maxlength="100" value="{{ $category->slug }}" required></div>
                        <div><label class="block text-xs font-medium" for="category-order-{{ $category->id }}">Order</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="category-order-{{ $category->id }}" name="sort_order" type="number" min="0" max="65535" value="{{ $category->sort_order }}" required></div>
                        <div class="flex items-center gap-2 self-end py-2"><input name="is_active" type="hidden" value="0"><input id="category-active-{{ $category->id }}" name="is_active" type="checkbox" value="1" @checked($category->is_active)><label class="text-sm" for="category-active-{{ $category->id }}">Active</label></div>
                        <button class="self-end rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50" type="submit">Save</button>
                    </form>
                @endforeach
            </div>
        @endif
    </section>

    <section aria-labelledby="priorities-heading" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold" id="priorities-heading">Priorities</h2>
        <form class="grid gap-3 sm:grid-cols-4" method="POST" action="{{ route('admin.ticket-catalogues.priorities.store') }}">
            @csrf
            <div><label class="block text-sm font-medium" for="priority-name">New priority</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="priority-name" name="name" maxlength="100" required></div>
            <div><label class="block text-sm font-medium" for="priority-order">Display order</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="priority-order" name="sort_order" type="number" min="0" max="65535" value="0" required></div>
            <input name="is_active" type="hidden" value="0">
            <label class="flex items-center gap-2 self-end py-2 text-sm"><input name="is_active" type="checkbox" value="1" checked> Active</label>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Add priority</button>
        </form>
        @if ($priorities->isEmpty())
            <p class="text-sm text-slate-600">No priorities configured.</p>
        @else
            <div class="space-y-3">
                @foreach ($priorities as $priority)
                    <form class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-5" method="POST" action="{{ route('admin.ticket-catalogues.priorities.update', $priority) }}">
                        @csrf
                        @method('PATCH')
                        <div><label class="block text-xs font-medium" for="priority-name-{{ $priority->id }}">Name</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="priority-name-{{ $priority->id }}" name="name" maxlength="100" value="{{ $priority->name }}" required></div>
                        <div><label class="block text-xs font-medium" for="priority-slug-{{ $priority->id }}">Slug</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="priority-slug-{{ $priority->id }}" name="slug" maxlength="100" value="{{ $priority->slug }}" required></div>
                        <div><label class="block text-xs font-medium" for="priority-order-{{ $priority->id }}">Order</label><input class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" id="priority-order-{{ $priority->id }}" name="sort_order" type="number" min="0" max="65535" value="{{ $priority->sort_order }}" required></div>
                        <div class="flex items-center gap-2 self-end py-2"><input name="is_active" type="hidden" value="0"><input id="priority-active-{{ $priority->id }}" name="is_active" type="checkbox" value="1" @checked($priority->is_active)><label class="text-sm" for="priority-active-{{ $priority->id }}">Active</label></div>
                        <button class="self-end rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50" type="submit">Save</button>
                    </form>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
