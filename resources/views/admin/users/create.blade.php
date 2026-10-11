@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="user-create-page">
    <header class="user-create-header">
        <a class="user-create-back" href="{{ route('admin.users.index') }}"><svg data-lucide="arrow-left" aria-hidden="true"></svg>Users <span>/</span> Add User</a>
        <h1>Add New User</h1>
        <p>Create a user account and set its initial sign-in credentials.</p>
    </header>
    <form method="POST" action="{{ route('admin.users.store') }}" class="user-create-form">
        @csrf
        <div class="user-create-columns">
            <section class="user-create-panel" aria-labelledby="user-information-heading">
                <header class="user-create-panel-heading"><span class="user-create-icon is-blue"><svg data-lucide="user-round" aria-hidden="true"></svg></span><div><h2 id="user-information-heading">User Information</h2><p>Basic account details</p></div></header>
                <div class="user-create-fields">
                    <div class="user-create-field"><label for="name">Full Name <span>Required</span></label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Enter full name"><x-field-error field="name" /></div>
                    <div class="user-create-field"><label for="email">Email Address <span>Required</span></label><span class="user-input-with-icon"><svg data-lucide="mail" aria-hidden="true"></svg><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="name@example.edu"></span><x-field-error field="email" /></div>
                </div>
            </section>

            <div class="user-create-side">
                <section class="user-create-panel" aria-labelledby="user-access-heading">
                    <header class="user-create-panel-heading"><span class="user-create-icon is-green"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><div><h2 id="user-access-heading">Initial Access</h2><p>Default account permissions</p></div></header>
                    <div class="user-access-defaults">
                        <div><span>Assigned Role</span><strong class="user-default-pill"><svg data-lucide="user-round" aria-hidden="true"></svg>Requester</strong></div>
                        <div><span>Account Status</span><strong class="user-account-state is-active">Active</strong></div>
                        <p>New accounts start with requester access. Additional roles can be assigned from the account details page.</p>
                    </div>
                </section>

                <section class="user-create-panel" aria-labelledby="user-password-heading">
                    <header class="user-create-panel-heading"><span class="user-create-icon is-amber"><svg data-lucide="key-round" aria-hidden="true"></svg></span><div><h2 id="user-password-heading">Initial Password</h2><p>Set a temporary sign-in password</p></div></header>
                    <div class="user-create-fields">
                        <div class="user-create-field"><label for="password">Password <span>Required</span></label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" placeholder="At least 12 characters"><x-field-error field="password" /></div>
                        <div class="user-create-field"><label for="password_confirmation">Confirm Password <span>Required</span></label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" placeholder="Enter the password again"></div>
                        <p class="user-password-guidance"><svg data-lucide="lock-keyhole" aria-hidden="true"></svg><span>Use at least 12 characters. Share credentials only through a CUT-approved secure channel.</span></p>
                    </div>
                </section>
            </div>
        </div>
        <footer class="user-create-actions"><a class="ticket-secondary-button" href="{{ route('admin.users.index') }}">Cancel</a><button class="ticket-primary-button" type="submit"><svg data-lucide="user-round-plus" aria-hidden="true"></svg>Create User</button></footer>
    </form>
</div>
@endsection
