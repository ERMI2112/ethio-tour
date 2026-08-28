@extends('errors.layout')

@section('title', 'Page not found')

@section('body')
    <p class="error-kicker">Lost trail</p>
    <h1 class="error-code">404</h1>
    <h2 class="error-title">This path doesn't lead anywhere yet.</h2>
    <p class="error-message">
        The page you are looking for may have moved, or the link is incomplete.
        Ethiopia's castles, monasteries, and festivals are still right where you left them.
    </p>
    <div class="error-actions">
        <a class="error-btn error-btn--primary" href="{{ url('/') }}">Back to home</a>
        <a class="error-btn error-btn--ghost" href="{{ route('destinations.index') }}">Browse destinations</a>
        @auth
            <a class="error-btn error-btn--ghost" href="{{ route('account') }}">My workspace</a>
        @endauth
    </div>
@endsection
