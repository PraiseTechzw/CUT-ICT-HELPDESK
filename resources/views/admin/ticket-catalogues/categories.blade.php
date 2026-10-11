@extends('layouts.app')

@section('title', 'Ticket Categories')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header ticket-catalogue-header">
        <div><h1>Ticket Categories</h1><p>Browse available categories and their details.</p></div>
        <details class="catalogue-add-menu" id="add-category" @if ($errors->any() && old('_method') !== 'PATCH') open @endif>
            <summary class="ticket-primary-button"><svg data-lucide="plus" aria-hidden="true"></svg><span>Add Category</span></summary>
            <form class="catalogue-add-popover" method="POST" action="{{ route('admin.ticket-catalogues.categories.store') }}">
            @csrf
                <h2>Add a category</h2>
                <label for="category-name">Category name<input id="category-name" name="name" maxlength="100" value="{{ old('name') }}" required><x-field-error field="name" /></label>
                <label for="category-description">Description<input id="category-description" name="description" maxlength="500" value="{{ old('description') }}"><x-field-error field="description" /></label>
                <label for="category-order">Display order<input id="category-order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}" required><x-field-error field="sort_order" /></label>
                <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')> Active</label>
                <button class="ticket-primary-button" type="submit">Save Category</button>
            </form>
        </details>
    </header>
    <section class="ticket-page-panel" aria-labelledby="category-list-heading">
        <div class="ticket-table-wrap"><table class="ticket-data-table catalogue-data-table">
            <caption class="sr-only" id="category-list-heading">{{ $categories->count() }} configured ticket categories</caption>
            <thead><tr><th scope="col">#</th><th scope="col">Category Title</th><th scope="col">Description</th><th scope="col">{{ $canViewTicketCounts ? 'Total Tickets' : 'Usage' }}</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>{{ $category->description ?: '—' }}</td>
                    <td>{{ $canViewTicketCounts ? number_format($category->tickets_count) : 'Restricted' }}</td>
                    <td><span class="ticket-state-pill {{ $category->is_active ? 'is-active' : 'is-inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><details class="catalogue-edit"><summary aria-label="Edit {{ $category->name }}"><svg data-lucide="pencil" aria-hidden="true"></svg><span>Edit</span></summary>
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
                <tr><td class="ticket-table-empty" colspan="6">No categories have been configured.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
</div>
@endsection
