@extends('layouts.app')

@section('title', $destination->name)

@section('content')
{{-- Destination Hero --}}
<header class="bg-light py-5 border-bottom mb-4">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-primary text-white text-uppercase px-2 py-1">Destination</span>
                    <span class="text-muted small fw-semibold">&bull; {{ $destination->location }}</span>
                    @if($destination->hasCoordinates())
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            Geocoded {{ number_format((float) $destination->latitude, 3) }}°, {{ number_format((float) $destination->longitude, 3) }}°
                        </span>
                    @endif
                </div>
                <h1 class="display-5 fw-bold text-dark mb-3">{{ $destination->name }}</h1>
                <p class="lead text-secondary mb-4">{{ $destination->description }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary" href="{{ auth()->user()?->role === 'tourist' ? route('smart-trip.create') : route('smart-trip.index') }}">
                        Plan a Trip to {{ $destination->name }}
                    </a>
                    @if($destination->hasCoordinates())
                        <a class="btn btn-outline-success" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}">
                            Explore on Map &nearr;
                        </a>
                    @endif
                    <a class="btn btn-outline-secondary" href="{{ route('search', ['destination' => $destination->name]) }}">
                        Search All in {{ $destination->name }}
                    </a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-white p-4">
                    <h2 class="h6 text-uppercase text-muted fw-bold mb-3">Quick Navigation</h2>
                    <ul class="nav flex-column gap-2 small">
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#heritage-sites">
                                &bull; Heritage &amp; Landmarks ({{ $destination->heritageSites->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#accommodations">
                                &bull; Verified Stays &amp; Hotels ({{ $hotels->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#dining">
                                &bull; Dining &amp; Restaurants ({{ $restaurants->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#transportation">
                                &bull; Transportation &amp; Rentals ({{ $transportation->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#cultural-events">
                                &bull; Upcoming Events &amp; Festivals ({{ $destination->culturalEvents->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 text-dark" href="#tour-guides">
                                &bull; Verified Tour Guides
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- 1. Heritage Sites & Historical Landmarks --}}
            <section id="heritage-sites" class="mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Heritage &amp; Historical Landmarks</h2>
                        <p class="small text-muted mb-0">Key monuments and historical attractions in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('heritage-sites.index', ['q' => $destination->name]) }}">All Heritage &rarr;</a>
                </div>
                @if($destination->heritageSites->isEmpty())
                    <x-ui.empty-state title="No heritage sites listed yet" message="Heritage and cultural information for {{ $destination->name }} will be published by the Tourism Bureau." />
                @else
                    <div class="row g-3">
                        @foreach($destination->heritageSites as $site)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <span class="badge bg-secondary-subtle text-secondary small align-self-start mb-2">Heritage Site</span>
                                        <h3 class="h5 mb-2">
                                            <a class="text-dark text-decoration-none" href="{{ route('heritage-sites.show', $site) }}">{{ $site->heritage_type }}</a>
                                        </h3>
                                        <div class="small text-muted mb-3 flex-grow-1">
                                            <div><strong>Hours:</strong> {{ $site->opening_hours }}</div>
                                            @if($site->entrance_fee > 0)
                                                <div><strong>Fee:</strong> {{ number_format((float) $site->entrance_fee, 2) }} ETB</div>
                                            @else
                                                <div class="text-success"><strong>Free Admission</strong></div>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <a class="btn btn-outline-primary btn-sm flex-grow-1" href="{{ route('heritage-sites.show', $site) }}">View Details</a>
                                            @if($site->hasCoordinates())
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('map', ['category' => 'heritage', 'q' => $site->heritage_type]) }}">Map</a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 2. Museum & Bureau Cultural Institutions --}}
            <section class="mb-5">
                <div class="card border-0 bg-primary-subtle p-4 shadow-sm">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="badge bg-primary text-white text-uppercase small mb-1">Bureau Curation</span>
                            <h3 class="h5 mb-1">Museums &amp; Cultural Centers</h3>
                            <p class="small text-secondary mb-0">Explore collections, artifacts, and regional history documented by the Tourism Bureau.</p>
                        </div>
                        <a class="btn btn-primary btn-sm text-nowrap" href="{{ route('museums.index', ['q' => $destination->name]) }}">
                            Explore Museums in {{ $destination->name }} &rarr;
                        </a>
                    </div>
                </div>
            </section>

            {{-- 3. Stays & Accommodations --}}
            <section id="accommodations" class="mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Verified Hotels &amp; Lodging</h2>
                        <p class="small text-muted mb-0">Licensed, bureau-approved accommodations in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('tourism-services.index', ['provider_type' => 'hotel', 'destination' => $destination->name]) }}">All Hotels &rarr;</a>
                </div>

                @if($hotels->isEmpty())
                    <x-ui.empty-state title="No hotels listed yet" message="Approved hotel properties in {{ $destination->name }} will appear here once verified." />
                @else
                    <div class="row g-3">
                        @foreach($hotels as $hotel)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-success-subtle text-success small">{{ $hotel->serviceProvider->business_name }}</span>
                                            @if(isset($serviceRatings[$hotel->service_id]))
                                                <span class="small text-warning fw-bold">
                                                    ★ {{ $serviceRatings[$hotel->service_id]['avg'] }} <span class="text-muted fw-normal">({{ $serviceRatings[$hotel->service_id]['count'] }})</span>
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="h5 mb-1">
                                            <a class="text-dark text-decoration-none" href="{{ route('tourism-services.show', $hotel) }}">{{ $hotel->service_name }}</a>
                                        </h3>
                                        <p class="small text-muted flex-grow-1 mb-3">
                                            {{ \Illuminate\Support\Str::limit($hotel->description, 120) }}
                                        </p>
                                        @if($hotel->hotelRoomType && !empty($hotel->hotelRoomType->amenities))
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                @foreach(array_slice($hotel->hotelRoomType->amenities, 0, 3) as $amenity)
                                                    <span class="badge bg-light text-secondary border small">{{ $amenity }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                            <div>
                                                <span class="fs-6 fw-bold text-dark">{{ number_format((float) $hotel->price, 2) }} ETB</span>
                                                <span class="small text-muted">/ night</span>
                                            </div>
                                            <a class="btn btn-primary btn-sm" href="{{ route('tourism-services.show', $hotel) }}">View &amp; Book</a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 4. Local Dining & Restaurants --}}
            <section id="dining" class="mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Dining &amp; Local Cuisine</h2>
                        <p class="small text-muted mb-0">Experience traditional Ethiopian hospitality and dining in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('tourism-services.index', ['provider_type' => 'restaurant', 'destination' => $destination->name]) }}">All Restaurants &rarr;</a>
                </div>

                @if($restaurants->isEmpty())
                    <x-ui.empty-state title="No restaurants listed yet" message="Approved dining and restaurant services in {{ $destination->name }} will appear here once verified." />
                @else
                    <div class="row g-3">
                        @foreach($restaurants as $restaurant)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-warning-subtle text-dark small">{{ $restaurant->serviceProvider->business_name }}</span>
                                            @if(isset($serviceRatings[$restaurant->service_id]))
                                                <span class="small text-warning fw-bold">
                                                    ★ {{ $serviceRatings[$restaurant->service_id]['avg'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="h5 mb-1">
                                            <a class="text-dark text-decoration-none" href="{{ route('tourism-services.show', $restaurant) }}">{{ $restaurant->service_name }}</a>
                                        </h3>
                                        <p class="small text-muted flex-grow-1 mb-3">
                                            {{ \Illuminate\Support\Str::limit($restaurant->description, 120) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                            <div>
                                                <span class="fs-6 fw-bold text-dark">{{ number_format((float) $restaurant->price, 2) }} ETB</span>
                                                <span class="small text-muted">/ res.</span>
                                            </div>
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('tourism-services.show', $restaurant) }}">View Menu &amp; Book</a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 5. Transportation & Car Rental --}}
            <section id="transportation" class="mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Transportation &amp; Car Rental</h2>
                        <p class="small text-muted mb-0">Reliable local transport options for exploring {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('transportation.index') }}">All Transport &rarr;</a>
                </div>

                @if($transportation->isEmpty())
                    <div class="card border-0 bg-light p-4 text-center">
                        <p class="text-muted mb-2">Explore vehicle rental and transportation providers operating across Ethiopia.</p>
                        <div>
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('transportation.index') }}">Browse Transport Directory</a>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($transportation as $transport)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <span class="badge bg-info-subtle text-info-emphasis small align-self-start mb-2">{{ $transport->serviceProvider->business_name }}</span>
                                        <h3 class="h5 mb-1">
                                            <a class="text-dark text-decoration-none" href="{{ route('tourism-services.show', $transport) }}">{{ $transport->service_name }}</a>
                                        </h3>
                                        <p class="small text-muted flex-grow-1 mb-3">
                                            {{ \Illuminate\Support\Str::limit($transport->description, 120) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                            <span class="fs-6 fw-bold text-dark">{{ number_format((float) $transport->price, 2) }} ETB <span class="small text-muted fw-normal">/ day</span></span>
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('tourism-services.show', $transport) }}">View Vehicles</a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- 6. Cultural Events & Festivals --}}
            <section id="cultural-events" class="mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Upcoming Events &amp; Festivals</h2>
                        <p class="small text-muted mb-0">Cultural festivals and celebrations in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('events.index', ['destination' => $destination->name]) }}">All Events &rarr;</a>
                </div>

                @if($destination->culturalEvents->isEmpty())
                    <x-ui.empty-state title="No upcoming events scheduled" message="Upcoming cultural festivals and community events in {{ $destination->name }} will appear here." />
                @else
                    <div class="row g-3">
                        @foreach($destination->culturalEvents as $event)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm">
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-danger-subtle text-danger small">{{ $event->event_date->format('M d, Y') }}</span>
                                            <span class="small text-muted">{{ $event->start_time }}</span>
                                        </div>
                                        <h3 class="h5 mb-1">
                                            <a class="text-dark text-decoration-none" href="{{ route('events.show', $event) }}">{{ $event->event_name }}</a>
                                        </h3>
                                        <p class="small text-muted mb-2"><strong>Venue:</strong> {{ $event->venue }}</p>
                                        <p class="small text-muted flex-grow-1 mb-3">
                                            {{ \Illuminate\Support\Str::limit($event->description, 120) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                            @if($event->ticketTypes->isNotEmpty())
                                                <span class="small text-muted">From <strong>{{ number_format((float) $event->ticketTypes->min('price'), 2) }} ETB</strong></span>
                                            @else
                                                <span class="small text-success fw-semibold">Public Event</span>
                                            @endif
                                            <a class="btn btn-outline-danger btn-sm" href="{{ route('events.show', $event) }}">View Event</a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- Sidebar --}}
        <aside class="col-lg-4">
            {{-- Tour Guides Advisory Banner --}}
            <section id="tour-guides" class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <span class="badge bg-success text-white small text-uppercase mb-2">Certified Guides</span>
                    <h2 class="h5 mb-2">Bureau-Verified Tour Guides</h2>
                    <p class="small text-muted mb-3">
                        Make the most of your visit to {{ $destination->name }} by booking an accredited local tour guide. Guides provide licensed historical interpretation, safety, and personalized cultural excursions.
                    </p>
                    <a class="btn btn-outline-success btn-sm w-100" href="{{ route('tour-guides.index') }}">
                        Browse Verified Tour Guides &rarr;
                    </a>
                </div>
            </section>

            {{-- Interactive Map Preview Card --}}
            @if($destination->hasCoordinates())
                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-2">Geographic Location</h2>
                        <p class="small text-muted mb-3">
                            Coordinates: {{ number_format((float) $destination->latitude, 4) }}° N, {{ number_format((float) $destination->longitude, 4) }}° E
                        </p>
                        <a class="btn btn-outline-primary btn-sm w-100" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}">
                            Open in Interactive Map &nearr;
                        </a>
                    </div>
                </section>
            @endif

            {{-- Smart Trip Builder Card --}}
            <section class="card border-0 shadow-sm bg-light mb-4">
                <div class="card-body p-4">
                    <span class="badge bg-primary text-white small text-uppercase mb-2">Smart Trip</span>
                    <h2 class="h5 mb-2">Build a Custom Itinerary</h2>
                    <p class="small text-muted mb-3">
                        Add heritage sites, stays, dining, and activities in {{ $destination->name }} into an organized day-by-day travel plan.
                    </p>
                    <a class="btn btn-primary btn-sm w-100" href="{{ auth()->user()?->role === 'tourist' ? route('smart-trip.create') : route('smart-trip.index') }}">
                        Start Planning Trip &rarr;
                    </a>
                </div>
            </section>

            {{-- Other Destinations --}}
            @if($otherDestinations->isNotEmpty())
                <section class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 text-uppercase text-muted fw-bold mb-3">Other Destinations</h2>
                        <div class="d-flex flex-column gap-3">
                            @foreach($otherDestinations as $other)
                                <div class="border-bottom pb-2">
                                    <h3 class="h6 mb-1">
                                        <a class="text-dark text-decoration-none" href="{{ route('destinations.show', $other) }}">{{ $other->name }}</a>
                                    </h3>
                                    <p class="small text-muted mb-0">{{ $other->location }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a class="small text-primary text-decoration-none fw-semibold" href="{{ route('destinations.index') }}">View All Destinations &rarr;</a>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
