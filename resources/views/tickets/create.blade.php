@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header">
        <div><p class="ticket-page-kicker">SUPPORT REQUEST</p><h1>Create New Ticket</h1><p>Submit a support request to the ICT Helpdesk.</p></div>
        <a class="ticket-secondary-button" href="{{ route('tickets.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Back to tickets</a>
    </header>

    <form class="ticket-create-grid" method="POST" action="{{ route('tickets.store') }}">
        @csrf
        <section class="ticket-page-panel ticket-create-main" aria-labelledby="ticket-details-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">REQUEST INFORMATION</p><h2 id="ticket-details-heading">Ticket Details</h2><p>Describe the issue so support can route it correctly.</p></div></header>
            <div class="ticket-form-fields">
                <div class="ticket-form-field"><label for="subject">Subject <span>Required</span></label><input id="subject" name="subject" maxlength="200" required value="{{ old('subject') }}" placeholder="Briefly summarize the issue"><x-field-error field="subject" /></div>
                <div class="ticket-form-field"><label for="description">Description <span>Required</span></label><textarea id="description" name="description" rows="7" maxlength="20000" required placeholder="What happened? Include the steps you tried and any relevant details.">{{ old('description') }}</textarea><x-field-error field="description" /></div>
                <div class="ticket-attachment-placeholder"><svg data-lucide="paperclip" aria-hidden="true"></svg><span><strong>Attachments</strong><small>File uploads are not enabled for this helpdesk yet.</small></span><span class="ticket-attachment-unavailable">Unavailable</span></div>
            </div>
        </section>

        <aside class="ticket-page-panel ticket-create-aside" aria-labelledby="additional-info-heading">
            <header class="ticket-panel-heading"><div><p class="ticket-page-kicker">CLASSIFICATION</p><h2 id="additional-info-heading">Additional Information</h2><p>Help us route your request.</p></div></header>
            <div class="ticket-form-fields">
                <div class="ticket-form-field"><label for="ticket_category_id">Category</label><select id="ticket_category_id" name="ticket_category_id"><option value="">Select category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('ticket_category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><x-field-error field="ticket_category_id" /></div>
                <div class="ticket-form-field"><label for="ticket_priority_id">Priority</label><select id="ticket_priority_id" name="ticket_priority_id"><option value="">Select priority</option>@foreach ($priorities as $priority)<option value="{{ $priority->id }}" @selected((string) old('ticket_priority_id') === (string) $priority->id)>{{ $priority->name }}</option>@endforeach</select><x-field-error field="ticket_priority_id" /></div>
                <div class="ticket-form-field"><label for="room_id">Location <span>Optional</span></label><select id="room_id" name="room_id"><option value="">Select room or location</option>@foreach ($rooms->groupBy(fn ($room) => ($room->building?->campus?->name ?? 'Campus'). ' · '.($room->building?->name ?? 'Building')) as $buildingLabel => $buildingRooms)<optgroup label="{{ $buildingLabel }}">@foreach ($buildingRooms as $room)<option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>{{ $room->name }}{{ $room->code ? ' · '.$room->code : '' }}</option>@endforeach</optgroup>@endforeach</select><x-field-error field="room_id" />@if($rooms->isEmpty())<small class="ticket-field-help">No active rooms are configured yet.</small>@endif</div>
                <div class="ticket-privacy-note"><svg data-lucide="shield-check" aria-hidden="true"></svg><span>Your ticket is visible only to you and authorized support staff.</span></div>
            </div>
            <div class="ticket-form-actions"><a class="ticket-secondary-button" href="{{ route('tickets.index') }}">Cancel</a><button class="ticket-primary-button" type="submit"><svg data-lucide="send" aria-hidden="true"></svg>Submit Ticket</button></div>
        </aside>
    </form>
</div>
@endsection
