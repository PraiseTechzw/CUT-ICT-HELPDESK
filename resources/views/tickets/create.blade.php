@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
<div class="ticket-workspace">
    <header class="ticket-page-header ticket-create-header">
        <div><p class="ticket-page-kicker">SUPPORT REQUEST</p><h1>Create New Ticket</h1><p>Tell us what you need help with and we’ll get it to the right team.</p></div>
        <a class="ticket-secondary-button" href="{{ route('tickets.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg><span>Back to tickets</span></a>
    </header>

    <form class="ticket-create-grid" method="POST" action="{{ route('tickets.store') }}">
        @csrf
        <section class="ticket-page-panel ticket-create-main" aria-labelledby="ticket-details-heading">
            <header class="ticket-panel-heading ticket-create-panel-heading"><span class="ticket-create-heading-icon ticket-create-heading-blue"><svg data-lucide="file-text" aria-hidden="true"></svg></span><div><p class="ticket-page-kicker">REQUEST INFORMATION</p><h2 id="ticket-details-heading">Describe the issue</h2><p>Include the details support needs to investigate.</p></div></header>
            <div class="ticket-form-fields">
                <div class="ticket-form-field ticket-create-subject-field">
                    <label for="subject">Subject <span class="ticket-field-required">Required</span></label>
                    <input id="subject" name="subject" maxlength="200" required autofocus value="{{ old('subject') }}" placeholder="For example, Unable to connect to campus Wi-Fi">
                    <small class="ticket-create-field-help">A short summary makes your request easier to identify.</small>
                    <x-field-error field="subject" />
                </div>
                <div class="ticket-form-field ticket-create-description-field">
                    <label for="description">Description <span class="ticket-field-required">Required</span></label>
                    <textarea id="description" name="description" rows="8" maxlength="20000" required placeholder="Describe what happened, when it started, and any steps you have already tried.">{{ old('description') }}</textarea>
                    <small class="ticket-create-field-help">Avoid including passwords or other sensitive information.</small>
                    <x-field-error field="description" />
                </div>
                <div class="ticket-attachment-placeholder" aria-label="Attachments unavailable">
                    <span class="ticket-attachment-icon"><svg data-lucide="paperclip" aria-hidden="true"></svg></span>
                    <span><strong>Attachments</strong><small>File uploads are not available yet.</small></span>
                    <span class="ticket-attachment-unavailable">Not available</span>
                </div>
            </div>
        </section>

        <aside class="ticket-page-panel ticket-create-aside" aria-labelledby="additional-info-heading">
            <header class="ticket-panel-heading ticket-create-panel-heading"><span class="ticket-create-heading-icon ticket-create-heading-green"><svg data-lucide="tags" aria-hidden="true"></svg></span><div><p class="ticket-page-kicker">CLASSIFICATION</p><h2 id="additional-info-heading">Additional Information</h2><p>Help us direct this to the right support team.</p></div></header>
            <div class="ticket-form-fields">
                <div class="ticket-form-field">
                    <label for="ticket_category_id">Category <span class="ticket-field-optional">Optional</span></label>
                    <select id="ticket_category_id" name="ticket_category_id"><option value="">Choose a category</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('ticket_category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                    <x-field-error field="ticket_category_id" />
                </div>
                <div class="ticket-form-field">
                    <label for="ticket_priority_id">Priority <span class="ticket-field-optional">Optional</span></label>
                    <select id="ticket_priority_id" name="ticket_priority_id"><option value="">Choose a priority</option>@foreach ($priorities as $priority)<option value="{{ $priority->id }}" @selected((string) old('ticket_priority_id') === (string) $priority->id)>{{ $priority->name }}</option>@endforeach</select>
                    <x-field-error field="ticket_priority_id" />
                </div>
                <div class="ticket-form-field">
                    <label for="room_id">Location <span class="ticket-field-optional">Optional</span></label>
                    <select id="room_id" name="room_id"><option value="">Choose a room</option>@foreach ($rooms->groupBy(fn ($room) => ($room->building?->campus?->name ?? 'Campus'). ' · '.($room->building?->name ?? 'Building')) as $buildingLabel => $buildingRooms)<optgroup label="{{ $buildingLabel }}">@foreach ($buildingRooms as $room)<option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>{{ $room->name }}{{ $room->code ? ' · '.$room->code : '' }}</option>@endforeach</optgroup>@endforeach</select>
                    <x-field-error field="room_id" />
                    @if($rooms->isEmpty())<small class="ticket-field-help">No active rooms are configured yet.</small>@endif
                </div>
                <div class="ticket-privacy-note"><span class="ticket-privacy-icon"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><strong>Private to your support team</strong><small>Your request is visible to you and authorized helpdesk staff.</small></span></div>
            </div>
            <div class="ticket-form-actions ticket-create-actions"><a class="ticket-secondary-button" href="{{ route('tickets.index') }}">Cancel</a><button class="ticket-primary-button" type="submit"><svg data-lucide="send" aria-hidden="true"></svg>Submit Ticket</button></div>
        </aside>
    </form>
</div>
@endsection
