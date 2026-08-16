@extends('layouts.app')
@section('title', 'Smart Trip')
@section('content')
<div class="container py-4 py-lg-5">
    <div class="row align-items-center g-4 mb-5">
        <div class="col-lg-7">
            <p class="text-uppercase text-success small fw-semibold mb-2">Plan your trip</p>
            <h1 class="display-5 fw-semibold">Build a trip that feels like yours.</h1>
            <p class="lead text-muted">Save destinations, discover real local experiences, and shape a flexible itinerary for Ethiopia.</p>
            @auth
                @if(auth()->user()->role === 'tourist')
                    <a class="btn btn-success btn-lg" href="{{ route('smart-trip.create') }}">Create a Smart Trip</a>
                @else
                    <a class="btn btn-outline-success" href="{{ route('search') }}">Explore tourism content</a>
                @endif
            @else
                <a class="btn btn-success btn-lg" href="{{ route('register') }}">Create an account to save a trip</a>
                <a class="btn btn-link" href="{{ route('login') }}">Log in</a>
            @endauth
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-white"><div class="card-body p-4"><h2 class="h5">Deterministic trip suggestions</h2><p class="text-muted small mb-0">Suggestions use verified public places, dates, ratings, interests, and coordinates where available. Nothing is booked automatically.</p></div></div>
        </div>
    </div>

    @auth
        @if(auth()->user()->role === 'tourist')
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Your saved trips</h2><a href="{{ route('smart-trip.create') }}" class="small">New trip</a></div>
            <div class="row g-4 mb-5">
                @forelse($trips as $trip)
                    <div class="col-md-6 col-xl-4"><article class="card border-0 shadow-sm h-100"><div class="card-body d-flex flex-column"><span class="badge text-bg-light text-success align-self-start mb-2">{{ ucfirst($trip->status) }}</span><h3 class="h5">{{ $trip->title }}</h3><p class="small text-muted">{{ $trip->start_date->format('M d, Y') }} – {{ $trip->end_date->format('M d, Y') }}</p><p class="small flex-grow-1">{{ $trip->destinations->pluck('name')->join(', ') }}</p><a class="btn btn-outline-success btn-sm" href="{{ route('smart-trip.show', $trip) }}">Open itinerary</a></div></article></div>
                @empty
                    <div class="col-12"><x-ui.empty-state title="No saved trips yet" message="Create a trip and start arranging real Ethiopian tourism experiences." /></div>
                @endforelse
            </div>
        @endif
    @endauth

    <section><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Explore destinations</h2><a href="{{ route('destinations.index') }}" class="small">View all</a></div><div class="row g-3">@foreach($destinations as $destination)<div class="col-6 col-md-4 col-xl-3"><a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('destinations.show', $destination) }}"><div class="card-body"><h3 class="h6 text-dark">{{ $destination->name }}</h3><p class="small text-muted mb-0">{{ $destination->location }}</p></div></a></div>@endforeach</div></section>
</div>
@endsection
