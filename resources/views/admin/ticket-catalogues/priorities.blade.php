@extends('layouts.app')

@section('title', 'Ticket Priorities')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div><p class="ticket-page-kicker">TICKET CONFIGURATION</p><h1>Ticket Priorities</h1><p>Set the urgency labels available to support teams.</p></div>
        <a class="ticket-primary-button" href="#add-priority"><svg data-lucide="plus" aria-hidden="true"></svg>Add Priority</a>
    </header>
    <nav class="ticket-page-tabs" aria-label="Ticket configuration pages">
        <a href="{{ route('admin.ticket-catalogues.categories.index') }}">Categories</a>
        <a class="is-active" href="{{ route('admin.ticket-catalogues.priorities.index') }}">Priorities</a>
        <a href="{{ route('admin.ticket-catalogues.statuses.index') }}">Statuses</a>
    </nav>
    <section class="ticket-page-panel" id="add-priority" aria-labelledby="add-priority-heading">
        <header class="ticket-panel-heading"><div><h2 id="add-priority-heading">Add a priority</h2><p>Choose a label and color used in ticket queues.</p></div></header>
        <form class="catalogue-create-form" method="POST" action="{{ route('admin.ticket-catalogues.priorities.store') }}">
            @csrf
            <div><label for="priority-name">Priority name</label><input id="priority-name" name="name" maxlength="100" value="{{ old('name') }}" required><x-field-error field="name" /></div>
            <div><label for="priority-description">Description</label><input id="priority-description" name="description" maxlength="500" value="{{ old('description') }}"><x-field-error field="description" /></div>
            <div><label for="priority-color">Color</label><input id="priority-color" class="catalogue-color-input" name="color" type="color" value="{{ old('color', '#2675d0') }}"><x-field-error field="color" /></div>
            <div class="catalogue-order-field"><label for="priority-order">Order</label><input id="priority-order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}" required></div>
            <input name="is_active" type="hidden" value="0"><label class="catalogue-active-field"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1')> Active</label>
            <button class="ticket-primary-button" type="submit">Add priority</button>
        </form>
    </section>
    <section class="ticket-page-panel" aria-labelledby="priority-list-heading">
        <header class="ticket-panel-heading"><div><h2 id="priority-list-heading">All Priorities</h2><p>{{ $priorities->count() }} configured priorities</p></div></header>
        <div class="ticket-table-wrap"><table class="ticket-data-table">
            <thead><tr><th scope="col">Priority</th><th scope="col">Description</th><th scope="col">{{ $canViewTicketCounts ? 'Total Tickets' : 'Usage' }}</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
            @forelse ($priorities as $priority)
                <tr>
                    <td><strong class="priority-name"><i style="--priority-color: {{ $priority->color ?: '#8292a3' }}"></i>{{ $priority->name }}</strong><small>{{ $priority->slug }}</small></td>
                    <td>{{ $priority->description ?: '—' }}</td>
                    <td>{{ $canViewTicketCounts ? number_format($priority->tickets_count) : 'Restricted' }}</td>
                    <td><span class="ticket-state-pill {{ $priority->is_active ? 'is-active' : 'is-inactive' }}">{{ $priority->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><details class="catalogue-edit"><summary aria-label="Edit {{ $priority->name }}"><svg data-lucide="pencil" aria-hidden="true"></svg></summary>
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
                <tr><td class="ticket-table-empty" colspan="5">No priorities have been configured.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
</div>
@endsection
