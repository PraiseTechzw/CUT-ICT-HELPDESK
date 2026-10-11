@extends('layouts.app')

@section('title', 'Ticket Priorities')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header ticket-catalogue-header">
        <div><h1>Ticket Priorities</h1><p>Manage priority levels and their visual indicators.</p></div>
        <details class="catalogue-add-menu" id="add-priority" @if ($errors->any() && old('_method') !== 'PATCH') open @endif>
            <summary class="ticket-primary-button"><svg data-lucide="plus" aria-hidden="true"></svg><span>Add Priority</span></summary>
            <form class="catalogue-add-popover catalogue-priority-add-form" method="POST" action="{{ route('admin.ticket-catalogues.priorities.store') }}">
                @csrf
                <h2>Add a priority</h2>
                <label for="priority-name">Priority name<input id="priority-name" name="name" maxlength="100" value="{{ old('name') }}" required><x-field-error field="name" /></label>
                <label for="priority-description">Description<input id="priority-description" name="description" maxlength="500" value="{{ old('description') }}"><x-field-error field="description" /></label>
                <label for="priority-color">Color<input id="priority-color" class="catalogue-color-input" name="color" type="color" value="{{ old('color', '#2675d0') }}"><x-field-error field="color" /></label>
                <label for="priority-order">Display order<input id="priority-order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}" required><x-field-error field="sort_order" /></label>
                <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')> Active</label>
                <button class="ticket-primary-button" type="submit">Save Priority</button>
            </form>
        </details>
    </header>
    <section class="ticket-page-panel" aria-labelledby="priority-list-heading">
        <div class="ticket-table-wrap"><table class="ticket-data-table catalogue-data-table priority-data-table">
            <caption class="sr-only" id="priority-list-heading">{{ $priorities->count() }} configured ticket priorities</caption>
            <thead><tr><th scope="col">#</th><th scope="col">Priority Name</th><th scope="col">Description</th><th scope="col">Color</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
            <tbody>
            @forelse ($priorities as $priority)
                <tr>
                    <td class="catalogue-row-number">{{ $loop->iteration }}</td>
                    <td><strong>{{ $priority->name }}</strong></td>
                    <td>{{ $priority->description ?: '—' }}</td>
                    <td><span class="priority-color-swatch" style="--priority-color: {{ $priority->color ?: '#8292a3' }}" role="img" aria-label="{{ $priority->color ?: 'Default' }}"></span></td>
                    <td><span class="ticket-state-pill {{ $priority->is_active ? 'is-active' : 'is-inactive' }}">{{ $priority->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><details class="catalogue-edit"><summary aria-label="Edit {{ $priority->name }}"><svg data-lucide="pencil" aria-hidden="true"></svg><span>Edit</span></summary>
                        <form class="catalogue-edit-form" method="POST" action="{{ route('admin.ticket-catalogues.priorities.update', $priority) }}">
                            @csrf @method('PATCH')
                            <label>Name<input name="name" value="{{ $priority->name }}" required maxlength="100"></label>
                            <label>Description<input name="description" value="{{ $priority->description }}" maxlength="500"></label>
                            <label>Slug<input name="slug" value="{{ $priority->slug }}" required maxlength="100"></label>
                            <label>Color<input class="catalogue-color-input" name="color" type="color" value="{{ $priority->color ?: '#8292a3' }}"></label>
                            <label>Display order<input name="sort_order" type="number" min="0" max="65535" value="{{ $priority->sort_order }}" required></label>
                            <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked($priority->is_active)> Active</label>
                            <button class="ticket-primary-button" type="submit">Save changes</button>
                        </form>
                    </details></td>
                </tr>
            @empty
                <tr><td class="ticket-table-empty" colspan="6">No priorities have been configured.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
</div>
@endsection
