@extends('layouts.auth')
@section('title', 'Reset password')
@section('auth-content')
    <h1 class="h3 mb-3">Choose a new password</h1><form method="POST" action="{{ route('password.update') }}">@csrf<input name="token" type="hidden" value="{{ $request->route('token') }}"><div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required></div><div class="mb-3"><label class="form-label" for="password">New password</label><input class="form-control" id="password" name="password" type="password" required></div><div class="mb-3"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required></div><button class="btn btn-primary w-100" type="submit">Reset password</button></form>
@endsection
