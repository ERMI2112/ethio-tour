@extends('layouts.auth')
@section('title', 'Confirm password')
@section('auth-content')
    <h1 class="h3 mb-3">Confirm your password</h1><p class="text-muted">This protected action requires password confirmation.</p><form method="POST" action="{{ route('password.confirm.store') }}">@csrf<div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><button class="btn btn-primary w-100" type="submit">Confirm</button></form>
@endsection
