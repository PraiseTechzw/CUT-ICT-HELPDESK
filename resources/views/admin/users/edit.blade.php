@extends('layouts.app')

@section('title', 'Edit account')

@section('content')
@php
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp
<div class="user-edit-page">
    <header class="user-edit-header">
        <div>
            <a class="admin-back-link" href="{{ route('admin.users.show', $user) }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Back to account</a>
            <p class="ticket-page-kicker">ACCOUNT ADMINISTRATION</p>
            <h1>Edit profile</h1>
            <p>Update the identity details used across the CUT ICT Helpdesk.</p>
        </div>
        <span class="admin-data-status is-secure"><span></span>Authorized workflow</span>
    </header>

    <section class="user-edit-identity" aria-label="Account being edited">
        <span class="user-detail-avatar" aria-hidden="true">{{ $initials }}</span>
        <div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div>
        <span class="user-account-state {{ $user->is_active ? 'is-active' : 'is-inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
    </section>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="ticket-page-panel user-edit-form">
        @csrf @method('PUT')
        <header class="ticket-panel-heading">
            <div><p class="ticket-page-kicker">PROFILE DETAILS</p><h2>Identity information</h2><p>Keep the account name and email address current and recognizable.</p></div>
            <span class="account-panel-icon is-blue"><svg data-lucide="user-round-cog" aria-hidden="true"></svg></span>
        </header>
        <div class="user-edit-fields">
            <div class="user-edit-field">
                <label for="name">Full name</label>
                <div class="user-edit-input-wrap"><svg data-lucide="user-round-cog" aria-hidden="true"></svg><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"></div>
                <x-field-error field="name" />
            </div>
            <div class="user-edit-field">
                <label for="email">Email address</label>
                <div class="user-edit-input-wrap"><svg data-lucide="mail" aria-hidden="true"></svg><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" inputmode="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"></div>
                <x-field-error field="email" />
            </div>
        </div>
        <div class="account-note is-blue user-edit-note"><svg data-lucide="lock-keyhole" aria-hidden="true"></svg><span>Passwords, account status, roles, and permissions are managed through separate authorized workflows.</span></div>
        <footer class="user-edit-actions">
            <a class="ticket-secondary-button" href="{{ route('admin.users.show', $user) }}">Cancel</a>
            <button class="ticket-primary-button" type="submit"><svg data-lucide="save" aria-hidden="true"></svg>Save profile</button>
        </footer>
    </form>
</div>
@endsection
