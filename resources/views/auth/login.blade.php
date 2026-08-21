@extends('layouts.auth')

@section('title', 'Log in')

@section('auth-content')
    <p class="landing-eyebrow mb-2">Ethio Tour</p>
    <h1 class="h3 mb-2">Log in to your workspace</h1>
    <p class="text-muted mb-4">Travelers, licensed guides, and verified operators each enter a dedicated portal. Bureau and administrator accounts are provisioned, not self-registered.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-check mb-3"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-success w-100" type="submit">Log in</button>
    </form>
    <div class="d-flex justify-content-between mt-3 small"><a href="{{ route('password.request') }}">Forgot password?</a><a href="{{ route('register') }}">Create account</a></div>
    <p class="small text-muted mt-4 mb-0">Looking for inspiration? <a href="{{ route('home') }}">Explore Ethiopia</a>.</p>
@endsection
