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

            <div class="auth-provider-actions" aria-label="Other sign-in options">
                <button class="auth-provider-button" type="button" disabled aria-label="Continue with Google (not configured)" title="Google sign-in is not configured">
                    <svg class="auth-provider-logo" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M21.35 12.23c0-.71-.06-1.39-.18-2.05H12v3.88h5.24a4.48 4.48 0 0 1-1.94 2.94v2.52h3.14c1.84-1.69 2.91-4.18 2.91-7.29Z"/>
                        <path fill="#34A853" d="M12 21.75c2.63 0 4.84-.87 6.45-2.36l-3.14-2.52c-.87.58-1.98.93-3.31.93-2.54 0-4.7-1.71-5.47-4.01H3.29v2.6A9.75 9.75 0 0 0 12 21.75Z"/>
                        <path fill="#FBBC05" d="M6.53 13.79a5.87 5.87 0 0 1 0-3.58v-2.6H3.29a9.75 9.75 0 0 0 0 8.78l3.24-2.6Z"/>
                        <path fill="#EA4335" d="M12 6.2c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.84 3.28 14.63 2.25 12 2.25a9.75 9.75 0 0 0-8.71 5.36l3.24 2.6C7.3 7.91 9.46 6.2 12 6.2Z"/>
                    </svg>
                    <span>Continue with Google</span>
                </button>
                <button class="auth-provider-button" type="button" disabled aria-label="Continue with Microsoft (not configured)" title="Microsoft sign-in is not configured">
                    <svg class="auth-provider-logo auth-provider-logo-microsoft" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#F25022" d="M2 2h9.5v9.5H2z"/><path fill="#7FBA00" d="M12.5 2H22v9.5h-9.5z"/>
                        <path fill="#00A4EF" d="M2 12.5h9.5V22H2z"/><path fill="#FFB900" d="M12.5 12.5H22V22h-9.5z"/>
                    </svg>
                    <span>Continue with Microsoft</span>
                </button>
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
                        <button class="auth-password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Show password" title="Show password">
                            <svg data-lucide="eye" aria-hidden="true"></svg>
                        </button>
                    </div>
                    <x-field-error field="password" />
                </div>

                <div class="auth-form-options">
                    <label class="auth-remember"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
                    <span class="auth-forgot" aria-disabled="true" title="Password reset is not enabled">Forgot password?</span>
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
