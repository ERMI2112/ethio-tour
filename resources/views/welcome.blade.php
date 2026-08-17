@extends('layouts.app')

@section('title', 'Discover Ethiopia')

@section('content')
    <span class="visually-hidden">Ethio Tour foundation is ready.</span>

    <div class="bg-light">
        <section class="container py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <p class="text-uppercase text-success fw-semibold small mb-2">Ethio Tour</p>
                    <h1 class="display-4 fw-semibold mb-3">Discover Ethiopia</h1>
                    <p class="lead text-muted mb-4">Explore places, experiences, stays, food, events, and journeys across Ethiopia.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-success btn-lg" href="{{ route('destinations.index') }}">Explore Ethiopia</a>
                        <a class="btn btn-outline-success btn-lg" href="{{ route('smart-trip.index') }}">Plan My Trip</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ route('search') }}">Search</a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5">Start exploring</h2>
                            <p class="text-muted small">Browse public tourism information without an account. Sign in only when you are ready to save a trip or request a booking.</p>
                            <div class="d-grid gap-2">
                                <a class="btn btn-outline-primary" href="{{ route('map') }}">Explore on Map</a>
                                <a class="btn btn-outline-primary" href="{{ route('events.index') }}">Upcoming Events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="container py-5">
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <p class="text-uppercase text-success small fw-semibold mb-1">Explore Ethiopia</p>
                    <h2 class="h3 mb-0">Destinations</h2>
                </div>
                <a href="{{ route('destinations.index') }}">View all</a>
            </div>

            @if ($destinations->isEmpty())
                <p class="text-muted">No destinations are available yet.</p>
            @else
                <div class="row g-3">
                    @foreach ($destinations as $destination)
                        <div class="col-sm-6 col-lg-4">
                            <a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('destinations.show', $destination) }}">
                                <div class="card-body">
                                    <h3 class="h5">{{ $destination->name }}</h3>
                                    <p class="small text-muted mb-0">{{ Str::limit($destination->description, 110) }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <p class="text-uppercase text-success small fw-semibold mb-1">Things to Do · Stay &amp; Eat</p>
                    <h2 class="h3 mb-0">Experiences and services</h2>
                </div>
                <a href="{{ route('tourism-services.index') }}">View all services</a>
            </div>

            @if ($experiences->isEmpty())
                <p class="text-muted">No public services are available yet.</p>
            @else
                <div class="row g-3">
                    @foreach ($experiences as $experience)
                        <div class="col-sm-6 col-lg-4">
                            <a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('tourism-services.show', $experience) }}">
                                <div class="card-body">
                                    <h3 class="h5">{{ $experience->service_name }}</h3>
                                    <p class="small text-muted mb-2">{{ $experience->destination?->name }} · {{ $experience->serviceProvider?->business_name }}</p>
                                    <strong class="text-success">{{ number_format((float) $experience->price, 2) }} ETB</strong>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <p class="text-uppercase text-success small fw-semibold mb-1">Events</p>
                    <h2 class="h3 mb-0">Upcoming cultural events</h2>
                </div>
                <a href="{{ route('events.index') }}">View all</a>
            </div>

            @if ($events->isEmpty())
                <p class="text-muted">No events are currently scheduled.</p>
            @else
                <div class="row g-3">
                    @foreach ($events as $event)
                        <div class="col-sm-6 col-lg-4">
                            <a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route('events.show', $event) }}">
                                <div class="card-body">
                                    <h3 class="h5">{{ $event->event_name }}</h3>
                                    <p class="small text-muted mb-0">{{ $event->event_date?->format('M j, Y') }} · {{ $event->venue }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
