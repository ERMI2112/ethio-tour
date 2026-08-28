@extends('layouts.auth')

@section('title', 'Confirm password · Ethio Tour')

@section('auth-content')
<div class="auth-split-card auth-split-card--login shadow-lg" data-aos="fade-up">
    {{-- Left Side: Form --}}
    <div class="auth-form-side">
        <div class="auth-form-header">
            <h1 class="auth-form-title">Confirm your password</h1>
            <div class="auth-title-bar" aria-hidden="true"></div>
            <p class="auth-form-subtext">This protected action requires password verification for your security.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm.store') }}" class="auth-form">
            @csrf

            <div class="auth-field-group mb-4">
                <label class="auth-label" for="password">Current password</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           type="password"
                           placeholder="••••••••"
                           required
                           autofocus>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn auth-submit-btn mb-4" type="submit">Confirm password</button>
        </form>
    </div>

    {{-- Right Side: Deep Emerald Showcase Banner --}}
    <div class="auth-showcase-side">
        <div class="auth-showcase-content">
            <span class="auth-brand-kicker">ETHIO TOUR</span>
            <h2 class="auth-showcase-title">Protected<br><em>area.</em></h2>
            <p class="auth-showcase-desc">Confirm your identity to continue with this administrative or sensitive operation.</p>
        </div>
    </div>
</div>
@endsection
