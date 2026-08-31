@extends('layouts.app')

@section('title', 'Discover Ethiopia | Ethio Tour')

@section('meta_description', 'Discover Ethiopia through verified destinations, heritage, local services, cultural events, and practical trip planning with Ethio Tour.')

@section('content')
    <main class="landing-page">
        <section class="landing-hero landing-hero--image" style="--landing-hero-image: url('{{ asset('images/destinations/hero-ethiopia.jpg') }}');">
            <div class="container py-5 position-relative" style="z-index: 1;">
                <div class="row align-items-center g-4 g-xl-5 py-lg-4">
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge bg-warning text-dark fw-bold px-3 py-2 text-uppercase">Land of Origins</span>
                            <span class="landing-kicker mb-0">A practical guide to Ethiopia</span>
                        </div>
                        <h1 class="display-3 fw-bold text-white mb-3 landing-hero-title" data-text-reveal>Find your next story in Ethiopia.</h1>
                        <p class="lead text-white-50 mb-4 landing-hero-copy">Discover Ethiopia beyond the itinerary: find the places, people, food, stays, transport, and events that make each journey unforgettable.</p>
                        <form action="{{ route('search') }}" method="GET" class="landing-search mb-4" role="search">
                            <label class="visually-hidden" for="landing-search">Search Ethiopia</label>
                            <div class="input-group input-group-lg shadow-lg"><input id="landing-search" name="q" type="search" class="form-control py-3 ps-3" placeholder="Search destinations, experiences, stays, or events" autocomplete="off"><button class="btn btn-warning px-4 fw-bold text-dark" type="submit">Search</button></div>
                        </form>
                        <div class="d-flex flex-wrap gap-3 align-items-center"><a class="btn btn-warning btn-lg fw-bold text-dark px-4" href="{{ route('destinations.index') }}">Explore destinations</a><a class="btn btn-outline-light btn-lg px-4" href="{{ route('smart-trip.index') }}">Plan with Smart Trip</a></div>
                        <p class="small text-white-50 mt-3 mb-0">Public discovery first. Reservations, payments, and reviews stay in one secure account.</p>
                    </div>
                    <div class="col-lg-5">
                        <aside class="landing-quick-panel shadow-lg" aria-label="Ethio Tour at a glance">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Start here</p><h2 class="h3 text-white mb-0">Build a trip that feels like yours.</h2></div><span class="landing-quick-icon" aria-hidden="true">✦</span></div>
                            <div class="row g-3 mb-4"><div class="col-6"><div class="landing-quick-stat"><strong>{{ $publishedDestinationCount }}</strong><span>destinations</span></div></div><div class="col-6"><div class="landing-quick-stat"><strong>{{ $operationalProviderCount }}</strong><span>public operators</span></div></div><div class="col-6"><div class="landing-quick-stat"><strong>{{ $verifiedGuideCount }}</strong><span>verified guides</span></div></div><div class="col-6"><div class="landing-quick-stat"><strong>{{ $publishedEventCount }}</strong><span>upcoming events</span></div></div></div>
                            <div class="border-top border-light border-opacity-25 pt-3"><p class="small text-white-50 mb-2">Popular starting points</p><div class="d-flex flex-wrap gap-2"><a class="landing-quick-link" href="{{ route('destinations.index') }}">Destinations</a><a class="landing-quick-link" href="{{ route('tour-guides.index') }}">Tour guides</a><a class="landing-quick-link" href="{{ route('events.index') }}">Events</a><a class="landing-quick-link" href="{{ route('map') }}">Map</a></div></div>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-trust" aria-label="Why travelers trust Ethio Tour"><div class="container"><div class="row g-3 text-center text-md-start"><div class="col-6 col-lg-3"><div class="landing-trust-item"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>{{ $operationalProviderCount }}</strong><span>Bureau-verified operators</span></div></div><div class="col-6 col-lg-3"><div class="landing-trust-item"><i class="bi bi-star-fill" aria-hidden="true"></i><strong>@if ($reviewAverage) {{ number_format($reviewAverage, 1) }}/5 @else &mdash; @endif</strong><span>@if ($reviewCount) From {{ $reviewCount }} verified traveler {{ Str::plural('review', $reviewCount) }} @else Verified traveler reviews @endif</span></div></div><div class="col-6 col-lg-3"><div class="landing-trust-item"><i class="bi bi-lock-fill" aria-hidden="true"></i><strong>Secure</strong><span>Chapa-protected payments</span></div></div><div class="col-6 col-lg-3"><div class="landing-trust-item"><i class="bi bi-bank" aria-hidden="true"></i><strong>Official</strong><span>Tourism Bureau oversight</span></div></div></div></div></section>

        <div class="container">
            <section class="landing-section" aria-labelledby="destinations-heading">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Explore Ethiopia</p><h2 id="destinations-heading" class="landing-section-title mb-1">Choose your next place</h2><p class="text-secondary mb-0">Start with a destination, then discover what is nearby.</p></div><a class="landing-section-link" href="{{ route('destinations.index') }}">View all destinations <span aria-hidden="true">→</span></a></div>
                @if ($destinations->isEmpty())
                    <div class="landing-empty">No destinations are available yet. Destinations will appear here as they are published.</div>
                @else
                    <div class="row g-4">
                        @foreach ($destinations->take(3) as $destination)
                            @php $mediaKey = $destination->slug ?: str($destination->name)->slug(); $heroPath = $destination->hero_image ?: ('/images/destinations/' . $mediaKey . '-hero.jpg'); $hasHeroFile = is_string($heroPath) && $heroPath !== '' && file_exists(public_path(ltrim($heroPath, '/'))); @endphp
                            <div class="col-md-6 col-lg-4"><article class="landing-card landing-destination-card h-100 overflow-hidden p-0"><div class="landing-card-media position-relative">@if ($hasHeroFile)<img src="{{ asset(ltrim($heroPath, '/')) }}" alt="{{ $destination->name }}" loading="lazy">@else<div class="landing-card-placeholder" aria-hidden="true">{{ str($destination->name)->substr(0, 1)->upper() }}</div>@endif @if ($destination->location)<span class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white">{{ $destination->location }}</span>@endif</div><div class="p-4 d-flex flex-column flex-grow-1"><h3 class="h4 mb-2"><a class="landing-card-title" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a></h3>@if ($destination->tagline)<p class="text-muted small fw-semibold mb-2">{{ $destination->tagline }}</p>@endif<p class="text-secondary small flex-grow-1 mb-3 line-clamp-3">{{ \Illuminate\Support\Str::words($destination->description, 26, '...') }}</p><a class="landing-section-link small" href="{{ route('destinations.show', $destination) }}">Explore {{ $destination->name }} <span aria-hidden="true">→</span></a></div></article></div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="landing-section landing-section-alt rounded-4 px-3 px-lg-5" aria-labelledby="plan-heading">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Plan your trip</p><h2 id="plan-heading" class="landing-section-title mb-1">Everything you need, in one place</h2><p class="text-secondary mb-0">Stay &amp; Eat, Things to Do, and Travel &amp; Transport — all linked to real public listings.</p></div><a class="landing-section-link" href="{{ route('search') }}">Search everything <span aria-hidden="true">→</span></a></div>
                <div class="row g-3">
                    @foreach ([['Hotels', 'Comfortable stays from trusted local providers.', route('tourism-services.index', ['provider_type' => 'hotel']), '⌂'], ['Tour guides', 'Local knowledge for heritage, nature, and culture.', route('tour-guides.index'), '◎'], ['Restaurants', 'Find dining and reservation offerings.', route('tourism-services.index', ['provider_type' => 'restaurant']), '◇'], ['Transportation & car rental', 'Compare public transport and rental services.', route('transportation.index'), '↗'], ['Cultural events', 'See published events and upcoming dates.', route('events.index'), '✦'], ['Museums', 'Explore collections, artifacts, and local history.', route('museums.index'), '▣']] as [$label, $description, $href, $icon])
                        <div class="col-sm-6 col-xl-4"><a class="landing-category-card h-100" href="{{ $href }}"><span class="landing-category-icon" aria-hidden="true">{{ $icon }}</span><span><strong>{{ $label }}</strong><small>{{ $description }}</small></span><span class="ms-auto landing-arrow" aria-hidden="true">→</span></a></div>
                    @endforeach
                </div>
            </section>

            <section class="landing-section" aria-labelledby="gondar-heading"><div class="landing-feature overflow-hidden"><div class="row g-0 align-items-stretch"><div class="col-lg-5"><div class="landing-feature-media h-100" style="background-image: url('{{ asset('images/attractions/fasil-ghebbi.jpg') }}');" role="img" aria-label="Fasil Ghebbi royal enclosure in Gondar"></div></div><div class="col-lg-7 p-4 p-lg-5"><p class="landing-eyebrow mb-1">Gondar pilot</p><h2 id="gondar-heading" class="landing-section-title mb-2">Begin in the city of castles.</h2><p class="text-secondary mb-4">{{ $gondar?->description ? \Illuminate\Support\Str::words($gondar->description, 46, '...') : 'Explore imperial heritage, living culture, and practical local services in Gondar.' }}</p><div class="row g-3 mb-4"><div class="col-6"><div class="landing-mini-stat"><strong>{{ $attractions->count() }}</strong><span>featured landmarks</span></div></div><div class="col-6"><div class="landing-mini-stat"><strong>{{ $gondarServices->count() }}</strong><span>public services</span></div></div><div class="col-6"><div class="landing-mini-stat"><strong>{{ $gondarEvents->count() }}</strong><span>upcoming events</span></div></div><div class="col-6"><div class="landing-mini-stat"><strong>{{ $museums->count() }}</strong><span>nearby museums</span></div></div></div>@if ($gondar)<a class="btn btn-success px-4" href="{{ route('destinations.show', $gondar) }}">Explore {{ $gondar->name }}</a>@endif <a class="btn btn-outline-success ms-2" href="{{ route('map') }}">Open the map</a></div></div></div></section>

            <section class="landing-section pt-0" aria-labelledby="services-heading">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <p class="landing-eyebrow mb-1">Real public listings</p>
                        <h2 id="services-heading" class="landing-section-title mb-1">Plan with verified local services</h2>
                        <p class="text-secondary mb-0">Service information comes from operational providers; prices and availability are never fabricated.</p>
                    </div>
                    <a class="landing-section-link" href="{{ route('tourism-services.index') }}">Browse services <span aria-hidden="true">→</span></a>
                </div>
                @if ($experiences->isEmpty())
                    <div class="landing-empty">No public experiences are available yet. Check back as providers complete activation.</div>
                @else
                    <div class="row g-3">
                        @foreach ($experiences->take(4) as $service)
                            @php
                                $type = $service->serviceProvider?->provider_type ?? 'hotel';
                                $serviceImg = \App\Support\ServiceImage::assetFor($service);
                            @endphp
                            <div class="col-md-6 col-xl-3">
                                <article class="landing-card landing-destination-card h-100 overflow-hidden p-0 d-flex flex-column">
                                    <div class="position-relative" style="height: 140px; overflow: hidden; background: #0c1e14;">
                                        <img src="{{ $serviceImg }}" alt="{{ $service->service_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                        <div class="position-absolute top-0 start-0 m-2 d-flex justify-content-between align-items-center w-100 pe-4">
                                            <span class="badge bg-dark bg-opacity-75 text-white small">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                            <span class="badge badge-verified">Verified</span>
                                        </div>
                                    </div>
                                    <div class="p-3 d-flex flex-column flex-grow-1">
                                        <h3 class="h6 fw-bold mb-1"><a class="landing-card-title text-dark text-decoration-none" href="{{ route('tourism-services.show', $service) }}">{{ $service->service_name }}</a></h3>
                                        <p class="small text-muted mb-2">{{ $service->serviceProvider?->business_name ?? 'Operational tourism service' }}</p>
                                        <p class="small text-secondary line-clamp-2 mb-3 flex-grow-1">{{ \Illuminate\Support\Str::words($service->description, 16, '...') }}</p>
                                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center gap-2">
                                            @if ($service->price !== null && (float) $service->price > 0)
                                                <span class="small fw-bold text-success">{{ number_format((float) $service->price, 2) }} ETB</span>
                                            @else
                                                <span class="small text-muted">See details</span>
                                            @endif
                                            <a class="small landing-section-link fw-semibold" href="{{ route('tourism-services.show', $service) }}">View <span aria-hidden="true">→</span></a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="landing-section landing-section-alt rounded-4 px-3 px-lg-5" aria-labelledby="events-heading">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <p class="landing-eyebrow mb-1">What’s happening</p>
                        <h2 id="events-heading" class="landing-section-title mb-1">Events &amp; Festivals</h2>
                        <p class="text-secondary mb-0">Published events with real dates and public booking paths.</p>
                    </div>
                    <a class="landing-section-link" href="{{ route('events.index') }}">View all events <span aria-hidden="true">→</span></a>
                </div>
                @if ($events->isEmpty())
                    <div class="landing-empty bg-transparent">No upcoming public events are available yet.</div>
                @else
                    <div class="row g-4">
                        @foreach ($events->take(3) as $event)
                            @php
                                $lowerEvent = strtolower($event->event_name);
                                $eventImg = str_contains($lowerEvent, 'meskel')
                                    ? asset('images/events/meskel-festival.jpg')
                                    : asset('images/events/timkat-festival.jpg');
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <article class="landing-card landing-destination-card h-100 overflow-hidden p-0 d-flex flex-column bg-white">
                                    <div class="position-relative" style="height: 160px; overflow: hidden; background: #0c1e14;">
                                        <img src="{{ $eventImg }}" alt="{{ $event->event_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <div class="badge bg-white text-dark shadow-sm px-2.5 py-1.5 text-center">
                                                <div class="fw-bold fs-6 leading-none text-success">{{ optional($event->event_date)->format('d') }}</div>
                                                <div class="small text-uppercase text-muted" style="font-size: 0.65rem;">{{ optional($event->event_date)->format('M Y') }}</div>
                                            </div>
                                        </div>
                                        @if($event->destination)
                                            <span class="position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white">
                                                📍 {{ $event->destination->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="p-3 p-lg-4 d-flex flex-column flex-grow-1">
                                        <h3 class="h5 fw-bold mb-2"><a class="landing-card-title text-dark text-decoration-none" href="{{ route('events.show', $event) }}">{{ $event->event_name }}</a></h3>
                                        <p class="small text-secondary mb-3 line-clamp-2">{{ $event->description ?: ($event->destination?->name ? 'Join authentic cultural celebrations in ' . $event->destination->name . '.' : 'Authentic cultural festival.') }}</p>
                                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                            <span class="small text-muted">{{ $event->venue ?: ($event->destination?->name ?? 'Ethiopia') }}</span>
                                            <a class="landing-section-link small fw-semibold" href="{{ route('events.show', $event) }}">Event details <span aria-hidden="true">→</span></a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="landing-section" aria-labelledby="tools-heading"><div class="row g-4"><div class="col-lg-6"><div class="landing-tool-card landing-tool-card--smart h-100"><p class="landing-eyebrow mb-1">Plan with context</p><h2 id="tools-heading" class="h3 fw-bold mb-2">Keep your trip ideas together.</h2><p class="text-secondary mb-4">Save destinations and experiences into a Smart Trip itinerary, then refine it as your plans take shape.</p><a class="btn btn-success" href="{{ route('smart-trip.index') }}">Plan with Smart Trip</a></div></div><div class="col-lg-6"><div class="landing-tool-card landing-tool-card--map h-100"><p class="landing-eyebrow mb-1">Explore spatially</p><h2 class="h3 fw-bold mb-2">See what is around you.</h2><p class="text-secondary mb-4">Use the public map to compare destinations, heritage, museums, events, and public tourism services with verified coordinates.</p><a class="btn btn-outline-success" href="{{ route('map') }}">Explore on Map</a></div></div></div></section>

            @if ($reviews->isNotEmpty())<section class="landing-section pt-0" aria-labelledby="reviews-heading"><div class="d-flex justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">From recent travelers</p><h2 id="reviews-heading" class="landing-section-title mb-0">Experiences worth sharing</h2></div><a class="landing-section-link" href="{{ route('search') }}">Keep exploring <span aria-hidden="true">→</span></a></div><div class="row g-3">@foreach ($reviews as $review)@php $target = $review->booking?->tourismService?->service_name ?? ($review->booking?->tourGuide?->full_name ? 'Tour with ' . $review->booking->tourGuide->full_name : 'A verified experience'); $reviewer = $review->tourist?->full_name ?? 'Traveler'; @endphp<div class="col-md-4"><article class="landing-review h-100"><div class="d-flex justify-content-between gap-2 mb-3"><span class="small fw-semibold text-success">{{ $target }}</span><span class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', (int) $review->rating) }}</span></div><p class="mb-3 line-clamp-3">“{{ $review->comment }}”</p><footer class="small text-muted mt-auto">{{ $reviewer }} · {{ optional($review->review_date)->format('M Y') }}</footer></article></div>@endforeach</div></section>@else<div class="container pb-5"><div class="landing-empty">No reviews are available yet. Reviews from completed experiences will appear here.</div></div>@endif
        </div>

        <section class="landing-final-cta"><div class="container text-center"><p class="landing-eyebrow mb-2">Your Ethiopia starts here</p><h2 class="h2 fw-bold mb-3">Discover something you can actually do.</h2><p class="text-secondary mx-auto mb-4" style="max-width: 42rem;">Browse public places and services today. Create an account when you are ready to save a trip, request a booking, or keep your reservations together.</p><div class="d-flex flex-wrap justify-content-center gap-3"><a class="btn btn-success btn-lg px-4" href="{{ route('destinations.index') }}">Explore Ethiopia</a><a class="btn btn-outline-success btn-lg px-4" href="{{ route('register') }}">Create an account</a></div></div></section>
    </main>
@endsection
