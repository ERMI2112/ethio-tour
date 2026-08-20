@extends('layouts.app')

@section('title', $destination->name . ($destination->tagline ? ' — ' . $destination->tagline : ''))

@section('content')
@php
    $destinationMediaKey = $destination->slug ?: \Illuminate\Support\Str::slug($destination->name);
    $destinationHeroPath = $destination->hero_image ?: '/images/destinations/'.$destinationMediaKey.'-hero.jpg';
    $destinationHeroExists = is_string($destinationHeroPath) && $destinationHeroPath !== '' && file_exists(public_path(ltrim($destinationHeroPath, '/')));
    $destinationSections = collect([
        ['id' => 'attractions', 'label' => 'Highlights', 'count' => $attractions->count()],
        ['id' => 'heritage-sites', 'label' => 'Heritage', 'count' => $destination->heritageSites->count()],
        ['id' => 'accommodations', 'label' => 'Stay', 'count' => $hotels->count()],
        ['id' => 'dining', 'label' => 'Eat', 'count' => $restaurants->count()],
        ['id' => 'transportation', 'label' => 'Move around', 'count' => $transportation->count()],
        ['id' => 'cultural-events', 'label' => 'Events', 'count' => $destination->culturalEvents->count()],
    ])->filter(fn ($item) => $item['count'] > 0);
@endphp

{{-- Editorial destination hero: image, context, real inventory and clear next actions. --}}
<header class="destination-editorial-hero mb-5" data-aos="fade-in">
    <div class="destination-editorial-hero__media" aria-hidden="true">
        @if($destinationHeroExists)
            <img src="{{ asset(ltrim($destinationHeroPath, '/')) }}" alt="" loading="eager">
        @endif
    </div>
    <div class="destination-editorial-hero__veil" aria-hidden="true"></div>
    <div class="container position-relative py-4 py-lg-5">
        <div class="mb-4">
            <x-ui.back-button :href="route('destinations.index')" label="Back to destinations" />
        </div>

        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 destination-eyebrow">
                    <span class="badge rounded-pill text-bg-warning">Explore Ethiopia</span>
                    <span class="destination-hero-location">{{ $destination->location }}</span>
                    @if($destination->hasCoordinates())
                        <span class="destination-location-badge">Mapped location</span>
                    @endif
                </div>
                <h1 class="display-3 fw-bold mb-3">{{ $destination->name }}</h1>
                @if($destination->tagline)
                    <p class="destination-hero-tagline mb-3">{{ $destination->tagline }}</p>
                @endif
                <p class="destination-hero-description mb-4">{{ $destination->description }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-warning btn-lg px-4" href="{{ auth()->user()?->role === 'tourist' ? route('smart-trip.create') : route('smart-trip.index') }}">
                        Plan a Trip to {{ $destination->name }}
                    </a>
                    @if($destination->hasCoordinates())
                        <a class="btn btn-outline-light btn-lg" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}">
                            Explore on Map <span aria-hidden="true">↗</span>
                        </a>
                    @endif
                    <a class="btn btn-outline-light btn-lg" href="{{ route('search', ['destination' => $destination->name]) }}">
                        Find everything here
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="destination-hero-panel">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h2 class="h5 mb-0">Plan your visit</h2>
                        <span class="destination-panel-mark" aria-hidden="true">✦</span>
                    </div>
                    <p class="small mb-3">Start with the places, stays and experiences that are available today.</p>
                    <div class="destination-stat-grid mb-3">
                        <div><strong>{{ $attractions->count() }}</strong><span>highlights</span></div>
                        <div><strong>{{ $hotels->count() }}</strong><span>stays</span></div>
                        <div><strong>{{ $restaurants->count() }}</strong><span>dining</span></div>
                        <div><strong>{{ $destination->culturalEvents->count() }}</strong><span>events</span></div>
                    </div>
                    <a class="btn btn-light w-100" href="#destination-content">Browse what’s here <span aria-hidden="true">↓</span></a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container destination-section-nav-wrap" id="destination-content" data-aos="fade-up">
    <nav class="destination-section-nav" aria-label="Destination sections">
        <span class="destination-section-nav__label">Quick Navigation</span>
        @foreach($destinationSections as $section)
            <a href="#{{ $section['id'] }}">{{ $section['label'] }} <span>{{ $section['count'] }}</span></a>
        @endforeach
        <a href="#tour-guides">Tour guides <span aria-hidden="true">→</span></a>
    </nav>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-8">

            {{-- 1. Key Attractions & Points of Interest --}}
            @if($attractions->isNotEmpty())
                <section id="attractions" class="mb-5" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <div>
                            <h2 class="h4 mb-0">Iconic Attractions &amp; Landmarks</h2>
                            <p class="small text-muted mb-0">Must-see historical sites, architectural treasures, and monuments in {{ $destination->name }}</p>
                        </div>
                        <span class="badge bg-primary text-white">{{ $attractions->count() }} {{ \Illuminate\Support\Str::plural('Attraction', $attractions->count()) }}</span>
                    </div>

                    <div class="row g-4">
                        @foreach($attractions as $attraction)
                            <div class="col-12">
                                <article class="card border-0 shadow-sm attraction-card destination-detail-card {{ $attraction->is_featured ? 'attraction-card--featured' : '' }}" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                                    {{ $attraction->categoryLabel() }}
                                                </span>
                                                @if($attraction->is_featured)
                                                    <span class="badge bg-warning text-dark fw-bold">
                                                        ★ Featured Highlight
                                                    </span>
                                                @endif
                                            </div>
                                            @if($attraction->entry_fee !== null)
                                                <div class="text-end">
                                                    @if((float) $attraction->entry_fee > 0)
                                                        <span class="fw-bold text-dark fs-6">{{ number_format((float) $attraction->entry_fee, 2) }} ETB</span>
                                                        <span class="text-muted small d-block">Est. Admission</span>
                                                    @else
                                                        <span class="badge bg-success text-white">Free Admission</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @php
                                            $primaryImg = $attraction->primaryImage();
                                            $imgPath = $primaryImg['path'] ?? null;
                                            $hasImg = is_string($imgPath) && $imgPath !== '' && file_exists(public_path(ltrim($imgPath, '/')));
                                        @endphp
                                        @if($hasImg)
                                            <div class="attraction-photo mb-3 rounded-3 overflow-hidden shadow-sm" style="height: 260px; background: #0c1e14;">
                                                <img src="{{ asset(ltrim($imgPath, '/')) }}" alt="{{ $primaryImg['alt'] ?? $attraction->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @endif

                                        <h3 class="h4 mb-2 text-dark">{{ $attraction->name }}</h3>

                                        @if($attraction->location_address)
                                            <p class="small text-muted mb-3 d-flex align-items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                                </svg>
                                                <span>{{ $attraction->location_address }}</span>
                                            </p>
                                        @endif

                                        {{-- Full Non-Truncated Description --}}
                                        <p class="text-secondary mb-3 attraction-description leading-relaxed">
                                            {{ $attraction->description }}
                                        </p>

                                        @if($attraction->opening_hours)
                                            <div class="p-2.5 rounded bg-light border mb-3 small d-flex flex-wrap align-items-center gap-2">
                                                <strong class="text-dark">Visiting Hours:</strong>
                                                <span class="text-muted">{{ $attraction->opening_hours }}</span>
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-2 pt-2 border-top mt-3">
                                            @if($attraction->hasCoordinates())
                                                <a class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5"
                                                   href="{{ $attraction->googleMapsUrl() }}"
                                                   target="_blank"
                                                   rel="noopener noreferrer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                                                        <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                                                    </svg>
                                                    Get Directions &nearr;
                                                </a>
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('map', ['category' => 'destinations', 'q' => $attraction->name]) }}">
                                                    View on Platform Map
                                                </a>
                                            @endif
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('search', ['destination' => $destination->name, 'q' => $attraction->name]) }}">
                                                Nearby Services &amp; Tours
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 2. Heritage Sites & Historical Landmarks --}}
            <section id="heritage-sites" class="mb-5" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Heritage &amp; Historical Landmarks</h2>
                        <p class="small text-muted mb-0">Registered historical monuments in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('heritage-sites.index', ['q' => $destination->name]) }}">All Heritage &rarr;</a>
                </div>
                @if($destination->heritageSites->isEmpty())
                    <x-ui.empty-state title="No heritage sites listed yet" message="New heritage listings for {{ $destination->name }} will appear here as they are published." />
                @else
                    <div class="row g-3">
                        @foreach($destination->heritageSites as $site)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm destination-detail-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
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

            {{-- 3. Museum & Cultural Institutions --}}
            <section class="mb-5" data-aos="fade-up">
                <div class="card border-0 bg-primary-subtle p-4 shadow-sm destination-detail-card">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="badge bg-primary text-white text-uppercase small mb-1">Culture &amp; History</span>
                            <h3 class="h5 mb-1">Museums &amp; Cultural Centers</h3>
                            <p class="small text-secondary mb-0">Explore collections, artifacts, and regional history from {{ $destination->name }} and beyond.</p>
                        </div>
                        <a class="btn btn-primary btn-sm text-nowrap" href="{{ route('museums.index', ['q' => $destination->name]) }}">
                            Explore Museums in {{ $destination->name }} &rarr;
                        </a>
                    </div>
                </div>
            </section>

            {{-- 4. Stays & Accommodations --}}
            <section id="accommodations" class="mb-5" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Verified Hotels &amp; Lodging</h2>
                        <p class="small text-muted mb-0">Comfortable stays and accommodations in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('tourism-services.index', ['provider_type' => 'hotel', 'destination' => $destination->name]) }}">All Hotels &rarr;</a>
                </div>

                @if($hotels->isEmpty())
                    <x-ui.empty-state title="No hotels listed yet" message="No hotel properties are listed in {{ $destination->name }} yet. Check back as local stays become available." />
                @else
                    <div class="row g-3">
                        @foreach($hotels as $hotel)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm destination-detail-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
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

            {{-- 5. Local Dining & Restaurants --}}
            <section id="dining" class="mb-5" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Dining &amp; Local Cuisine</h2>
                        <p class="small text-muted mb-0">Experience traditional Ethiopian hospitality and dining in {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('tourism-services.index', ['provider_type' => 'restaurant', 'destination' => $destination->name]) }}">All Restaurants &rarr;</a>
                </div>

                @if($restaurants->isEmpty())
                    <x-ui.empty-state title="No restaurants listed yet" message="No dining services are listed in {{ $destination->name }} yet. Check back for new local options." />
                @else
                    <div class="row g-3">
                        @foreach($restaurants as $restaurant)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm destination-detail-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
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

            {{-- 6. Transportation & Car Rental --}}
            <section id="transportation" class="mb-5" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Transportation &amp; Car Rental</h2>
                        <p class="small text-muted mb-0">Reliable local transport options for exploring {{ $destination->name }}</p>
                    </div>
                    <a class="small text-decoration-none fw-semibold" href="{{ route('transportation.index') }}">All Transport &rarr;</a>
                </div>

                @if($transportation->isEmpty())
                    <div class="card border-0 bg-light p-4 text-center destination-detail-card">
                        <p class="text-muted mb-2">Explore vehicle rental and transportation providers operating across Ethiopia.</p>
                        <div>
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('transportation.index') }}">Browse Transport Directory</a>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($transportation as $transport)
                            <div class="col-md-6">
                                <article class="card h-100 border-0 shadow-sm destination-detail-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
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

            {{-- 7. Cultural Events & Festivals --}}
            <section id="cultural-events" class="mb-5" data-aos="fade-up">
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
                                <article class="card h-100 border-0 shadow-sm destination-detail-card" data-aos="fade-up" data-aos-delay="{{ min($loop->index * 60, 240) }}">
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
            <section id="tour-guides" class="card border-0 shadow-sm mb-4 destination-sidebar-card" data-aos="fade-up">
                <div class="card-body p-4">
                    <span class="badge bg-success text-white small text-uppercase mb-2">Verified Guides</span>
                    <h2 class="h5 mb-2">Local Tour Guides</h2>
                    <p class="small text-muted mb-3">
                        Make the most of your visit to {{ $destination->name }} with experienced local guides for heritage walks, cultural excursions, and personalized day trips.
                    </p>
                    <a class="btn btn-outline-success btn-sm w-100" href="{{ route('tour-guides.index') }}">
                        Browse Verified Guides &rarr;
                    </a>
                </div>
            </section>

            {{-- Geographic Location / Map Preview Card --}}
            @if($destination->hasCoordinates())
                <section class="card border-0 shadow-sm mb-4 destination-sidebar-card" data-aos="fade-up" data-aos-delay="80">
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
            <section class="card border-0 shadow-sm bg-light mb-4 destination-sidebar-card" data-aos="fade-up" data-aos-delay="160">
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
