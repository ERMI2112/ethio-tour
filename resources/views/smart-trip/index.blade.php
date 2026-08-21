@extends('layouts.app')

@section('title', 'Smart Trip · AI Travel Itinerary Planner')

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Hero Banner -->
    <header class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: linear-gradient(135deg, #0d3824 0%, #155737 100%);">
        <div class="card-body p-4 p-md-5 text-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <p class="text-uppercase text-warning small fw-bold mb-2">✨ AI-Powered Trip Planner</p>
                    <h1 class="display-5 fw-bold mb-3">Build a trip that feels like yours.</h1>
                    <p class="lead text-white-50 mb-4">
                        Combine real UNESCO heritage sites, boutique hotels, certified local tour guides, and cultural festivals into an organized day-by-day itinerary.
                    </p>
                    @auth
                        @if(auth()->user()->role === 'tourist')
                            <a class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow-sm" href="{{ route('smart-trip.create') }}">
                                <i class="bi bi-plus-circle me-1"></i> Plan a New Smart Trip
                            </a>
                        @else
                            <a class="btn btn-outline-light btn-lg px-4" href="{{ route('search') }}">
                                Explore Tourism Catalog
                            </a>
                        @endif
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow-sm" href="{{ route('register') }}">
                                Create Free Account to Plan
                            </a>
                            <a class="btn btn-outline-light btn-lg px-4" href="{{ route('login') }}">
                                Log In
                            </a>
                        </div>
                    @endauth
                </div>
                <div class="col-lg-5">
                    <div class="p-4 bg-white bg-opacity-10 rounded-3 border border-white-25 backdrop-blur">
                        <h2 class="h5 fw-bold text-white mb-2">Verified &amp; Deterministic</h2>
                        <p class="small text-white-50 mb-0">
                            Unlike generic AI chat, Smart Trip only references active Ethiopian tourism databases, verified pricing, authentic coordinates, and operating providers.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Saved Trips (Tourist Only) -->
    @auth
        @if(auth()->user()->role === 'tourist')
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1">Your Saved Trips</h2>
                        <p class="text-muted small mb-0">Manage and customize your private itineraries</p>
                    </div>
                    <a href="{{ route('smart-trip.create') }}" class="btn btn-outline-success btn-sm fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> New Trip
                    </a>
                </div>

                <div class="row g-4">
                    @forelse($trips as $trip)
                        <div class="col-md-6 col-xl-4">
                            <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column hover-shadow">
                                <div class="position-relative" style="height: 160px; background: #0d3824;">
                                    <img src="{{ asset('images/destinations/gondar-castles.jpg') }}" alt="{{ $trip->title }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1 text-uppercase">
                                            {{ ucfirst($trip->status) }}
                                        </span>
                                    </div>
                                    <div class="position-absolute bottom-0 start-0 m-3 text-white">
                                        <div class="small fw-semibold text-warning">
                                            {{ $trip->start_date->format('M d') }} – {{ $trip->end_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <h3 class="h5 fw-bold mb-2 text-dark">{{ $trip->title }}</h3>
                                    <p class="small text-muted mb-3 flex-grow-1">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $trip->destinations->pluck('name')->join(', ') ?: 'Ethiopian Destinations' }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                        <span class="small text-muted">
                                            {{ $trip->items()->count() }} {{ \Illuminate\Support\Str::plural('stop', $trip->items()->count()) }}
                                        </span>
                                        <a class="btn btn-success btn-sm fw-bold px-3" href="{{ route('smart-trip.show', $trip) }}">
                                            Open Itinerary &rarr;
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                                <div class="mb-3 fs-1">🧳</div>
                                <h3 class="h5 fw-bold mb-2">No saved trips yet</h3>
                                <p class="text-muted mb-3">Create your first Smart Trip and start curating your custom itinerary.</p>
                                <div>
                                    <a class="btn btn-success fw-bold px-4" href="{{ route('smart-trip.create') }}">
                                        Plan your first trip
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    @endauth

    <!-- Travel Inspiration & Featured Destinations -->
    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">Explore Top Destinations</h2>
                <p class="text-muted small mb-0">Select a region to view historical sites, local hotels, and certified tour guides</p>
            </div>
            <a href="{{ route('destinations.index') }}" class="btn btn-outline-secondary btn-sm">
                View All Destinations &rarr;
            </a>
        </div>

        <div class="row g-4">
            @foreach($destinations as $destination)
                <div class="col-md-6 col-xl-4">
                    <a class="card border-0 shadow-sm rounded-4 h-100 text-decoration-none overflow-hidden hover-shadow" href="{{ route('destinations.show', $destination) }}">
                        <div class="position-relative" style="height: 180px; background: #0d3824;">
                            <img src="{{ $destination->hero_image ? asset($destination->hero_image) : asset('images/destinations/gondar-castles.jpg') }}" alt="{{ $destination->name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);"></div>
                            <div class="position-absolute bottom-0 start-0 m-3 text-white">
                                <h3 class="h5 fw-bold text-white mb-0">{{ $destination->name }}</h3>
                                <p class="small text-white-50 mb-0">{{ $destination->location }}</p>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-secondary mb-0">
                                {{ \Illuminate\Support\Str::limit($destination->description, 110) }}
                            </p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
