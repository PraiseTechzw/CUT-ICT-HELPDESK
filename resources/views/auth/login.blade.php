@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="auth-page">
    <section class="auth-intro" aria-labelledby="auth-intro-title">
        <div class="auth-intro-backdrop" aria-hidden="true"></div>
        <div class="auth-intro-content">
            <div class="auth-brand-lockup">
                <img src="{{ asset('logo.png') }}" alt="Chinhoyi University of Technology">
            </div>

            <p class="auth-kicker"><span aria-hidden="true"></span>CUT ICT HELPDESK</p>
            <h1 id="auth-intro-title">IT support,<br><em>without the queue.</em></h1>
            <p class="auth-intro-copy">Report issues, track progress, and stay connected to the people resolving them.</p>

            <div class="auth-benefits" aria-label="Helpdesk benefits">
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="ticket-check" aria-hidden="true"></svg></span>
                    <span><strong>One place for every request</strong><small>Track, manage and resolve all your IT requests<br class="auth-desktop-break"> in one convenient location.</small></span>
                </div>
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="shield-check" aria-hidden="true"></svg></span>
                    <span><strong>Secure access</strong><small>Your university data is protected with<br class="auth-desktop-break"> enterprise-grade security.</small></span>
                </div>
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="clock-3" aria-hidden="true"></svg></span>
                    <span><strong>Clear progress at every step</strong><small>See real-time updates and know exactly<br class="auth-desktop-break"> what happens next.</small></span>
                </div>
            </div>

            <p class="auth-intro-footer"><span aria-hidden="true"></span>Chinhoyi University of Technology&nbsp; · &nbsp;ICT Helpdesk</p>
        </div>
    </section>

    <section class="auth-card-wrap" aria-labelledby="auth-card-title">
        <div class="auth-card">
            <div class="auth-card-heading">
                <p class="auth-kicker">WELCOME BACK</p>
                <h2 id="auth-card-title">Sign in to your workspace</h2>
                <p>Use your university account to continue securely.</p>
            </div>

            @if ($errors->has('email') && !$errors->has('password'))
                <div class="auth-alert" role="alert"><svg data-lucide="circle-alert" aria-hidden="true"></svg><span>{{ $errors->first('email') }}</span></div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="auth-divider"><span>OR SIGN IN WITH CUT ACCOUNT</span></div>
                <div class="auth-field">
                    <label class="sr-only" for="email">Email address</label>
                    <div class="auth-input-wrap">
                        <svg data-lucide="mail" aria-hidden="true"></svg>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Email address" autocomplete="username" inputmode="email" required autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if ($errors->has('email')) aria-describedby="email-error" @endif>
                    </div>
                    <x-field-error field="email" />
                </div>

                <div class="auth-field">
                    <label class="sr-only" for="password">Password</label>
                    <div class="auth-input-wrap">
                        <svg data-lucide="lock-keyhole" aria-hidden="true"></svg>
                        <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if ($errors->has('password')) aria-describedby="password-error" @endif>
                    </div>
                    <x-field-error field="password" />
                </div>

                <div class="auth-form-options">
                    <label class="auth-remember"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
                    <span></span>
                </div>

                <button type="submit" class="auth-submit">
                    <span>Sign in</span>
                    <svg data-lucide="arrow-right" aria-hidden="true"></svg>
                </button>
            </form>

            <div class="auth-card-footer">
                <svg data-lucide="headset" aria-hidden="true"></svg>
                <p>Need access? Contact your ICT Helpdesk administrator.</p>
            </div>
            <p class="auth-card-secure"><svg data-lucide="lock-keyhole" aria-hidden="true"></svg> Secure university sign-in</p>
        </div>
    </section>
</div>
@endsection
