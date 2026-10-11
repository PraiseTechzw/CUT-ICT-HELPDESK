@extends('layouts.app')

@section('title', 'Ticket Categories')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div><p class="ticket-page-kicker">TICKET CONFIGURATION</p><h1>Ticket Categories</h1><p>Manage the categories used to classify support requests.</p></div>
        <a class="ticket-primary-button" href="#add-category"><svg data-lucide="plus" aria-hidden="true"></svg>Add Category</a>
    </header>
    <nav class="ticket-page-tabs" aria-label="Ticket configuration pages">
        <a class="is-active" href="{{ route('admin.ticket-catalogues.categories.index') }}">Categories</a>
        <a href="{{ route('admin.ticket-catalogues.priorities.index') }}">Priorities</a>
        <a href="{{ route('admin.ticket-catalogues.statuses.index') }}">Statuses</a>
    </nav>
    <section class="ticket-page-panel" id="add-category" aria-labelledby="add-category-heading">
        <header class="ticket-panel-heading"><div><h2 id="add-category-heading">Add a category</h2><p>New categories are available immediately when active.</p></div></header>
        <form class="catalogue-create-form" method="POST" action="{{ route('admin.ticket-catalogues.categories.store') }}">
            @csrf
            <div><label for="category-name">Category name</label><input id="category-name" name="name" maxlength="100" value="{{ old('name') }}" required><x-field-error field="name" /></div>
            <div><label for="category-description">Description</label><input id="category-description" name="description" maxlength="500" value="{{ old('description') }}"><x-field-error field="description" /></div>
            <div class="catalogue-order-field"><label for="category-order">Order</label><input id="category-order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}" required></div>
            <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')> Active</label>
            <button class="ticket-primary-button" type="submit">Add category</button>
        </form>
    </section>
    <section class="ticket-page-panel" aria-labelledby="category-list-heading">
        <header class="ticket-panel-heading"><div><h2 id="category-list-heading">All Categories</h2><p>{{ $categories->count() }} configured categories</p></div></header>
        <div class="ticket-table-wrap"><table class="ticket-data-table">
            <thead><tr><th scope="col">Category</th><th scope="col">Description</th><th scope="col">{{ $canViewTicketCounts ? 'Total Tickets' : 'Usage' }}</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong><small>{{ $category->slug }}</small></td>
                    <td>{{ $category->description ?: '—' }}</td>
                    <td>{{ $canViewTicketCounts ? number_format($category->tickets_count) : 'Restricted' }}</td>
                    <td><span class="ticket-state-pill {{ $category->is_active ? 'is-active' : 'is-inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><details class="catalogue-edit"><summary aria-label="Edit {{ $category->name }}"><svg data-lucide="pencil" aria-hidden="true"></svg></summary>
                        <form class="catalogue-edit-form" method="POST" action="{{ route('admin.ticket-catalogues.categories.update', $category) }}">
                            @csrf @method('PATCH')
                            <label>Name<input name="name" value="{{ $category->name }}" required maxlength="100"></label>
                            <label>Description<input name="description" value="{{ $category->description }}" maxlength="500"></label>
                            <label>Slug<input name="slug" value="{{ $category->slug }}" required maxlength="100"></label>
                            <label>Display order<input name="sort_order" type="number" min="0" max="65535" value="{{ $category->sort_order }}" required></label>
                            <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked($category->is_active)> Active</label>
                            <button class="ticket-primary-button" type="submit">Save changes</button>
                        </form>
                    </details></td>
                </tr>
            @empty
                <tr><td class="ticket-table-empty" colspan="5">No categories have been configured.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
</div>
@endsection
