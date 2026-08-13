@extends('layouts.auth')
@section('title', 'Forgot password')
@section('auth-content')
    <h1 class="h3 mb-3">Reset your password</h1><p class="text-muted">Enter your email and we will send a reset link if the account exists.</p>
    <form method="POST" action="{{ route('password.email') }}">@csrf<div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><button class="btn btn-primary w-100" type="submit">Email password reset link</button></form>
@endsection
