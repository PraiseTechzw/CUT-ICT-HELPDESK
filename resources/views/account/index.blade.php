@extends('layouts.app')

@section('title', 'My account')

@section('content')
@php
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp
<div class="account-page">
    <header class="account-page-header">
        <div>
            <p class="ticket-page-kicker">PERSONAL WORKSPACE</p>
            <h1>My account</h1>
            <p>Review your profile, access level, and helpdesk account preferences.</p>
        </div>
        <span class="admin-data-status is-secure"><span></span>Account active</span>
    </header>

    <section class="account-hero" aria-labelledby="account-identity-heading">
        <div class="account-avatar" aria-hidden="true">{{ $initials }}</div>
        <div class="account-identity">
            <p class="account-eyebrow">CUT ICT ACCOUNT</p>
            <h2 id="account-identity-heading">{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="account-role-list" aria-label="Assigned roles">
                @forelse ($user->roles as $role)
                    <span><svg data-lucide="shield-check" aria-hidden="true"></svg>{{ $role->name }}</span>
                @empty
                    <span><svg data-lucide="shield-check" aria-hidden="true"></svg>Helpdesk account</span>
                @endforelse
            </div>
        </div>
        <div class="account-hero-meta">
            <span class="user-account-state is-active">Active account</span>
            <small>Member since {{ $user->created_at?->format('d M Y') ?? '—' }}</small>
        </div>
    </section>

    <div class="account-content-grid">
        <section class="ticket-page-panel account-panel" aria-labelledby="profile-details-heading">
            <header class="ticket-panel-heading">
                <div><p class="ticket-page-kicker">PROFILE</p><h2 id="profile-details-heading">Profile details</h2><p>Information used to identify you in the helpdesk workspace.</p></div>
                <span class="account-panel-icon is-blue"><svg data-lucide="user-round-cog" aria-hidden="true"></svg></span>
            </header>
            <dl class="account-detail-list">
                <div><dt>Full name</dt><dd>{{ $user->name }}</dd></div>
                <div><dt>Email address</dt><dd>{{ $user->email }}</dd></div>
                <div><dt>Account status</dt><dd><span class="user-account-state is-active">Active</span></dd></div>
                <div><dt>Last profile update</dt><dd>{{ $user->updated_at?->toDayDateTimeString() ?? '—' }}</dd></div>
            </dl>
            <div class="account-note"><svg data-lucide="info" aria-hidden="true"></svg><span>Need to change your name or email address? Contact an authorized ICT Helpdesk administrator.</span></div>
        </section>

        <section class="ticket-page-panel account-panel" aria-labelledby="access-settings-heading">
            <header class="ticket-panel-heading">
                <div><p class="ticket-page-kicker">ACCOUNT SETTINGS</p><h2 id="access-settings-heading">Access &amp; preferences</h2><p>Your current workspace settings and service access.</p></div>
                <span class="account-panel-icon is-green"><svg data-lucide="settings" aria-hidden="true"></svg></span>
            </header>
            <div class="account-setting-list">
                <article class="account-setting-item"><span class="account-setting-icon is-blue"><svg data-lucide="bell" aria-hidden="true"></svg></span><span><strong>Notifications</strong><small>{{ number_format($unreadNotificationCount) }} unread · {{ number_format($notificationCount) }} total in your inbox</small></span><a href="{{ route('notifications.index') }}" class="account-setting-link">Open<svg data-lucide="chevron-right" aria-hidden="true"></svg></a></article>
                <article class="account-setting-item"><span class="account-setting-icon is-green"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><strong>Role-based access</strong><small>{{ $user->roles->count() ? $user->roles->pluck('name')->join(', ') : 'Standard helpdesk access' }}</small></span><span class="account-setting-state">Protected</span></article>
                <article class="account-setting-item"><span class="account-setting-icon is-violet"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><strong>Organizational groups</strong><small>{{ $user->userGroups->count() ? $user->userGroups->pluck('name')->join(', ') : 'No groups assigned' }}</small></span><span class="account-setting-state">{{ $user->userGroups->count() ? 'Assigned' : 'None' }}</span></article>
            </div>
            <div class="account-note is-blue"><svg data-lucide="lock-keyhole" aria-hidden="true"></svg><span>Passwords, roles, and account status are protected administrative settings. Contact ICT Helpdesk for assistance.</span></div>
        </section>
    </div>

    <section class="account-support-banner" aria-label="Account support">
        <span class="account-support-icon"><svg data-lucide="headset" aria-hidden="true"></svg></span>
        <div><strong>Need help with your account?</strong><p>Contact the ICT Helpdesk administrator if your details, permissions, or notifications need attention.</p></div>
        <a href="{{ route('notifications.index') }}" class="ticket-secondary-button">View notifications<svg data-lucide="arrow-right" aria-hidden="true"></svg></a>
    </section>
</div>
@endsection
