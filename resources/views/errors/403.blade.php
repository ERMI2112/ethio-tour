@extends('errors.layout')

@section('title', 'Access restricted')

@section('body')
    <p class="error-kicker">Restricted area</p>
    <h1 class="error-code">403</h1>
    <h2 class="error-title">This gate is guarded.</h2>
    <p class="error-message">
        Your account doesn't have permission to view this page. If you believe this is a mistake,
        check that you are signed in with the right account or contact platform support.
    </p>
    <div class="error-actions">
        @auth
            <a class="error-btn error-btn--primary" href="{{ route('account') }}">My workspace</a>
        @else
            <a class="error-btn error-btn--primary" href="{{ route('login') }}">Log in</a>
        @endauth
        <a class="error-btn error-btn--ghost" href="{{ url('/') }}">Back to home</a>
    </div>
@endsection
