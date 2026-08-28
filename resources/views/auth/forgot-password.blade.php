@extends('layouts.auth')

@section('title', 'Forgot password · Ethio Tour')

@section('auth-content')
<div class="auth-split-card auth-split-card--login shadow-lg" data-aos="fade-up">
    {{-- Left Side: Form --}}
    <div class="auth-form-side">
        <div class="auth-form-header">
            <h1 class="auth-form-title">Reset your password</h1>
            <div class="auth-title-bar" aria-hidden="true"></div>
            <p class="auth-form-subtext">Enter your verified email address and we will send a secure password reset link.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 border-0" style="background: rgba(16, 185, 129, 0.12); color: #065f46;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="auth-field-group mb-4">
                <label class="auth-label" for="email">Email address</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           placeholder="name@example.com"
                           required
                           autofocus>
                    <span class="auth-field-icon" aria-hidden="true">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                        </svg>
                    </span>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn auth-submit-btn mb-4" type="submit">Email password reset link</button>
        </form>

        <div class="auth-footer-text">
            <span>Remembered your credentials?</span>
            <a class="auth-link ms-1" href="{{ route('login') }}">Back to log in</a>
        </div>
    </div>

    {{-- Right Side: Deep Emerald Showcase Banner --}}
    <div class="auth-showcase-side">
        <div class="auth-showcase-content">
            <span class="auth-brand-kicker">ETHIO TOUR</span>
            <h2 class="auth-showcase-title">Account<br><em>recovery.</em></h2>
            <p class="auth-showcase-desc">We’ll help you safely regain access to your Ethiopian travel dashboard and reservations.</p>
        </div>
    </div>
</div>
@endsection
