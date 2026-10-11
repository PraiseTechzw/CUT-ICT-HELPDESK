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

            <p class="auth-kicker">SECURE ICT SUPPORT</p>
            <h1 id="auth-intro-title">Get the help you need without the queue.</h1>
            <p class="auth-intro-copy">Report issues, follow progress, and keep every support request connected to the people who can resolve it.</p>

            <div class="auth-benefits" aria-label="Helpdesk benefits">
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="ticket-check" aria-hidden="true"></svg></span>
                    <span><strong>One place for every request</strong><small>Track tickets from first report to resolution.</small></span>
                </div>
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="shield-check" aria-hidden="true"></svg></span>
                    <span><strong>Built around safe access</strong><small>Your workspace follows your approved role.</small></span>
                </div>
                <div class="auth-benefit">
                    <span class="auth-benefit-icon"><svg data-lucide="clock-3" aria-hidden="true"></svg></span>
                    <span><strong>Clear progress at every step</strong><small>See ownership, status, and next actions.</small></span>
                </div>
            </div>

            <p class="auth-intro-footer">Chinhoyi University of Technology<br><span>ICT Helpdesk System · v1.0.0</span></p>
        </div>
    </section>

    <section class="auth-card-wrap" aria-labelledby="auth-card-title">
        <div class="auth-card">
            <div class="auth-card-heading">
                <div class="auth-card-mark" aria-hidden="true"><svg data-lucide="log-in"></svg></div>
                <p class="auth-kicker">WELCOME BACK</p>
                <h2 id="auth-card-title">Sign in to your workspace</h2>
                <p>Use the CUT ICT account issued by an authorized helpdesk administrator.</p>
            </div>

            @if ($errors->has('email') && !$errors->has('password'))
                <div class="auth-alert" role="alert"><svg data-lucide="circle-alert" aria-hidden="true"></svg><span>{{ $errors->first('email') }}</span></div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="auth-field">
                    <label for="email">Email address</label>
                    <div class="auth-input-wrap">
                        <svg data-lucide="mail" aria-hidden="true"></svg>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" required autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if ($errors->has('email')) aria-describedby="email-error" @endif>
                    </div>
                    <x-field-error field="email" />
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <svg data-lucide="lock-keyhole" aria-hidden="true"></svg>
                        <input id="password" name="password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if ($errors->has('password')) aria-describedby="password-error" @endif>
                    </div>
                    <x-field-error field="password" />
                </div>

                <div class="auth-form-options">
                    <label class="auth-remember"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
                    <span class="auth-secure-note"><svg data-lucide="shield" aria-hidden="true"></svg> Protected sign-in</span>
                </div>

                <button type="submit" class="auth-submit">
                    <span>Sign in securely</span>
                    <svg data-lucide="arrow-right" aria-hidden="true"></svg>
                </button>
            </form>

            <div class="auth-card-footer">
                <svg data-lucide="info" aria-hidden="true"></svg>
                <p>Need an account or access change? Contact your ICT Helpdesk administrator.</p>
            </div>
        </div>
    </section>
</div>
@endsection
