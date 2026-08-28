@extends('errors.layout')

@section('title', 'Something went wrong')

@section('body')
    <p class="error-kicker">Unexpected detour</p>
    <h1 class="error-code">500</h1>
    <h2 class="error-title">Something broke on our side.</h2>
    <p class="error-message">
        We hit an unexpected problem while loading this page. Your bookings and account are safe.
        Please try again in a moment — if the problem continues, let the platform team know.
    </p>
    <div class="error-actions">
        <a class="error-btn error-btn--primary" href="{{ url()->current() }}">Try again</a>
        <a class="error-btn error-btn--ghost" href="{{ url('/') }}">Back to home</a>
    </div>
@endsection
