@extends('layouts.auth')

@section('title', 'Log in')

@section('auth-content')
    <h1 class="h3 mb-4">Log in</h1>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-check mb-3"><input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-primary w-100" type="submit">Log in</button>
    </form>
    <div class="d-flex justify-content-between mt-3 small"><a href="{{ route('password.request') }}">Forgot password?</a><a href="{{ route('register') }}">Create account</a></div>
@endsection
