@extends('layouts.auth')

@section('title', 'Reset password · Ethio Tour')

@section('auth-content')
<div class="auth-split-card auth-split-card--login shadow-lg" data-aos="fade-up">
    {{-- Left Side: Form --}}
    <div class="auth-form-side">
        <div class="auth-form-header">
            <h1 class="auth-form-title">Choose a new password</h1>
            <div class="auth-title-bar" aria-hidden="true"></div>
            <p class="auth-form-subtext">Enter your email and create a new secure password for your account.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            <input name="token" type="hidden" value="{{ $request->route('token') }}">

            <div class="auth-field-group mb-3">
                <label class="auth-label" for="email">Email address</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           type="email"
                           value="{{ old('email', $request->email) }}"
                           required
                           autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field-group mb-3">
                <label class="auth-label" for="password">New password</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           type="password"
                           placeholder="••••••••"
                           required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-field-group mb-4">
                <label class="auth-label" for="password_confirmation">Confirm password</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('password_confirmation') is-invalid @enderror"
                           id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           placeholder="••••••••"
                           required>
                </div>
                @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn auth-submit-btn mb-4" type="submit">Reset password</button>
        </form>
    </div>

    {{-- Right Side: Deep Emerald Showcase Banner --}}
    <div class="auth-showcase-side">
        <div class="auth-showcase-content">
            <span class="auth-brand-kicker">ETHIO TOUR</span>
            <h2 class="auth-showcase-title">Security<br><em>first.</em></h2>
            <p class="auth-showcase-desc">Update your password to keep your bookings, payments, and private trip itineraries protected.</p>
        </div>
    </div>
</div>
@endsection
