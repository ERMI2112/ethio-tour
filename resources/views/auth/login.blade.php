@extends('layouts.auth')

@section('title', 'Log in · Ethio Tour')

@section('auth-content')
<div class="auth-split-card auth-split-card--login shadow-lg" data-aos="fade-up">
    {{-- Left Side: Form --}}
    <div class="auth-form-side">
        <div class="auth-form-header">
            <h1 class="auth-form-title">Log in</h1>
            <div class="auth-title-bar" aria-hidden="true"></div>
            <p class="auth-form-subtext">Access your personal itineraries, reservations, and verified tourism services.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 border-0" style="background: rgba(16, 185, 129, 0.12); color: #065f46;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf

            {{-- Email Input --}}
            <div class="auth-field-group mb-3">
                <label class="auth-label" for="email">Username or email</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           placeholder="name@example.com"
                           autocomplete="username"
                           required
                           autofocus>
                    <span class="auth-field-icon" aria-hidden="true">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                        </svg>
                    </span>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password Input with Visibility Toggle --}}
            <div class="auth-field-group mb-3">
                <label class="auth-label" for="password">Password</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           type="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           required>
                    <button type="button"
                            class="auth-password-toggle"
                            aria-label="Toggle password visibility"
                            onclick="toggleAuthPassword('password', this)">
                        <svg class="toggle-icon-show" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check custom-auth-check mb-0">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <label class="form-check-label small" for="remember">Keep me signed in</label>
                </div>
                <a class="auth-link small" href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <button class="btn auth-submit-btn mb-4" type="submit">Log in</button>
        </form>

        <div class="auth-footer-text">
            <span>New to ETHIO TOUR?</span>
            <a class="auth-link ms-1" href="{{ route('register') }}">Create an account</a>
        </div>
    </div>

    {{-- Right Side: Deep Emerald Showcase Banner --}}
    <div class="auth-showcase-side">
        <div class="auth-showcase-content">
            <span class="auth-brand-kicker">ETHIO TOUR</span>
            <h2 class="auth-showcase-title">Welcome<br><em>back.</em></h2>
            <p class="auth-showcase-desc">Your journeys, saved itineraries, and verified bookings are exactly where you left them.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAuthPassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword
        ? '<svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 4.5c4.289 0 7.39 3.018 7.915 3.5-.327.302-1.976 1.774-4.055 2.74l.5.498zm-3.255-3.255A3 3 0 0 0 8 5.5a3 3 0 0 0-2.74 1.765l.77.77A2 2 0 0 1 8 6.5a2 2 0 0 1 2 2c0 .356-.094.689-.255.978l.76.76z"/><path d="M.146.146a.5.5 0 0 1 .708 0l15 15a.5.5 0 0 1-.708.708l-1.5-1.5A8.5 8.5 0 0 1 8 16C3 16 0 8 0 8a13 13 0 0 1 3.14-3.832L.146.854a.5.5 0 0 1 0-.708M3.92 5.334l1.19 1.19A4.5 4.5 0 0 0 8 13.5c3.275 0 5.875-1.895 7.155-3.5-.327-.302-1.976-1.774-4.055-2.74l-.794-.794A3 3 0 0 1 8 7.5a3 3 0 0 1-2.89-2.166z"/></svg>'
        : '<svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>';
}
</script>
@endpush
@endsection
