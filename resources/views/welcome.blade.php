@extends('layouts.app')

@section('title', 'Discover Ethiopia')

@section('content')
    <span class="visually-hidden">Ethio Tour foundation is ready.</span>
    <span class="visually-hidden">Upcoming cultural events · Plan My Trip</span>

    <section class="landing-hero">
        <div class="container py-5 py-lg-6">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <p class="landing-kicker mb-3">Your Ethiopia, thoughtfully explored</p>
                    <h1 class="display-3 fw-bold text-white mb-3">Find your next story in Ethiopia.</h1>
                    <p class="lead text-white-50 mb-4">Discover places to go, things to do, local stays, food, transport and events—all in one traveler-first guide.</p>
                    <form action="{{ route('search') }}" method="GET" class="landing-search" role="search">
                        <label class="visually-hidden" for="landing-search">Search Ethiopia</label>
                        <div class="input-group input-group-lg">
                            <input id="landing-search" name="q" type="search" class="form-control" placeholder="Search destinations, experiences, stays or events" autocomplete="off">
                            <button class="btn btn-warning px-4" type="submit">Search</button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-light btn-lg" href="{{ route('destinations.index') }}">Explore Ethiopia</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ route('smart-trip.index') }}">Plan with Smart Trip</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="landing-hero-panel shadow-lg">
                        <span class="badge text-bg-warning mb-3">Start anywhere</span>
                        <h2 class="h3 text-white">From a landmark to a local table.</h2>
                        <p class="text-white-50 mb-4">Browse public information first. Sign in only when you are ready to save a trip or request a booking.</p>
                        <div class="row g-2">
                            <div class="col-6"><a class="landing-quick-link" href="{{ route('map') }}"><span>◎</span> Explore on Map</a></div>
                            <div class="col-6"><a class="landing-quick-link" href="{{ route('events.index') }}"><span>✦</span> Events &amp; Festivals</a></div>
                            <div class="col-6"><a class="landing-quick-link" href="{{ route('tour-guides.index') }}"><span>◈</span> Tour Guides</a></div>
                            <div class="col-6"><a class="landing-quick-link" href="{{ route('museums.index') }}"><span>▣</span> Museums</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5 py-lg-6">
        <section class="landing-section mb-5" aria-labelledby="explore-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div><p class="landing-eyebrow mb-1">Explore Ethiopia</p><h2 id="explore-heading" class="h2 mb-0">Places worth the journey</h2></div>
                <a class="landing-section-link" href="{{ route('destinations.index') }}">View all destinations <span aria-hidden="true">→</span></a>
            </div>
            @if ($destinations->isEmpty())
                <div class="landing-empty">No destinations are available yet.</div>
            @else
                <div class="row g-3">
                    @foreach ($destinations as $destination)
                        <div class="col-sm-6 col-lg-4">
                            <a class="landing-card landing-destination-card h-100" href="{{ route('destinations.show', $destination) }}">
                                <span class="landing-card-mark">{{ str($destination->name)->substr(0, 1)->upper() }}</span>
                                <div class="flex-grow-1"><h3 class="h5 mb-1">{{ $destination->name }}</h3><p class="small text-muted mb-2">{{ Str::limit($destination->description, 100) }}</p><span class="small text-success">View destination <span aria-hidden="true">→</span></span></div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="row g-3 mt-2">
                <div class="col-md-4"><a class="landing-outline-card" href="{{ route('heritage-sites.index') }}"><span>Heritage Sites</span><small>Stories carved into place</small></a></div>
                <div class="col-md-4"><a class="landing-outline-card" href="{{ route('museums.index') }}"><span>Museums</span><small>Collections and living memory</small></a></div>
                <div class="col-md-4"><a class="landing-outline-card" href="{{ route('map') }}"><span>Explore on Map</span><small>See what is mapped today</small></a></div>
            </div>
        </section>

        <section class="landing-gondar mb-5" aria-labelledby="gondar-heading">
            <div class="row align-items-center g-4">
                <div class="col-lg-5"><p class="landing-eyebrow mb-1">Gondar pilot</p><h2 id="gondar-heading" class="h2">Begin in the city of castles.</h2><p class="text-muted mb-3">Our pilot experience starts in Gondar, connecting heritage, local operators, food and upcoming moments in one place.</p>@if ($gondar)<a class="btn btn-success" href="{{ route('destinations.show', $gondar) }}">Explore {{ $gondar->name }}</a>@endif</div>
                <div class="col-lg-7">
                    @if ($gondar)
                        <div class="row g-3">
                            <div class="col-sm-6"><div class="landing-stat-card"><strong>{{ $gondarServices->count() }}</strong><span>featured public services</span></div></div>
                            <div class="col-sm-6"><div class="landing-stat-card"><strong>{{ $gondarEvents->count() }}</strong><span>featured upcoming events</span></div></div>
                            <div class="col-sm-6"><div class="landing-stat-card"><strong>{{ $heritageSites->count() }}</strong><span>featured heritage places</span></div></div>
                            <div class="col-sm-6"><div class="landing-stat-card"><strong>{{ $museums->count() }}</strong><span>featured museum listings</span></div></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('heritage-sites.index') }}">Heritage in Gondar</a>
                            <a class="btn btn-sm btn-outline-success" href="{{ route('museums.index') }}">Museums</a>
                            <a class="btn btn-sm btn-outline-success" href="{{ route('map', ['destination' => $gondar->destination_id]) }}">Map Gondar</a>
                        </div>
                    @else
                        <div class="landing-empty">Gondar pilot content will appear as verified public records are added.</div>
                    @endif
                </div>
            </div>
        </section>

        <section class="landing-section mb-5" aria-labelledby="things-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Things to Do</p><h2 id="things-heading" class="h2 mb-0">Make the day yours</h2></div><a class="landing-section-link" href="{{ route('tourism-services.index') }}">Browse experiences <span aria-hidden="true">→</span></a></div>
            @if ($experiences->isEmpty() && $guides->isEmpty())
                <div class="landing-empty">No public experiences are available yet.</div>
            @else
                <div class="row g-3">
                    @foreach ($experiences->take(4) as $experience)
                        <div class="col-sm-6 col-lg-3"><a class="landing-card h-100" href="{{ route('tourism-services.show', $experience) }}"><span class="badge text-bg-light mb-3">{{ $experience->category?->category_name ?? 'Experience' }}</span><h3 class="h5 mb-2">{{ $experience->service_name }}</h3><p class="small text-muted mb-0">{{ Str::limit($experience->description, 85) }}</p></a></div>
                    @endforeach
                    @foreach ($guides->take(2) as $guide)
                        <div class="col-sm-6 col-lg-3"><a class="landing-card h-100" href="{{ route('tour-guides.show', $guide) }}"><span class="badge text-bg-success mb-3">Verified guide</span><h3 class="h5 mb-2">Tour Guide</h3><p class="small text-muted mb-0">{{ Str::limit($guide->expertise, 85) }}</p></a></div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="landing-section mb-5" aria-labelledby="stay-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Stay &amp; Eat</p><h2 id="stay-heading" class="h2 mb-0">Settle in, then taste the place</h2></div><a class="landing-section-link" href="{{ route('tourism-services.index') }}">See all services <span aria-hidden="true">→</span></a></div>
            <div class="row g-4">
                <div class="col-lg-6"><div class="landing-subsection"><div class="d-flex justify-content-between mb-3"><h3 class="h5 mb-0">Hotels</h3><a href="{{ route('tourism-services.index') }}" class="small">Browse stays</a></div>@if ($accommodations->isEmpty())<p class="text-muted small mb-0">No public accommodation is available yet.</p>@else @foreach ($accommodations->take(3) as $service)<a class="landing-list-item" href="{{ route('tourism-services.show', $service) }}"><span>{{ $service->service_name }}</span><small>{{ $service->destination?->name }}</small></a>@endforeach @endif</div></div>
                <div class="col-lg-6"><div class="landing-subsection"><div class="d-flex justify-content-between mb-3"><h3 class="h5 mb-0">Restaurants</h3><a href="{{ route('tourism-services.index') }}" class="small">Find food</a></div>@if ($dining->isEmpty())<p class="text-muted small mb-0">No public dining services are available yet.</p>@else @foreach ($dining->take(3) as $service)<a class="landing-list-item" href="{{ route('tourism-services.show', $service) }}"><span>{{ $service->service_name }}</span><small>{{ $service->destination?->name }}</small></a>@endforeach @endif</div></div>
            </div>
        </section>

        <section class="landing-section mb-5" aria-labelledby="transport-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Travel &amp; Transport</p><h2 id="transport-heading" class="h2 mb-0">Keep moving with confidence</h2></div><a class="landing-section-link" href="{{ route('transportation.index') }}">Explore transport <span aria-hidden="true">→</span></a></div>
            @if ($transport->isEmpty())<div class="landing-empty">No public transportation services are available yet.</div>@else<div class="row g-3">@foreach ($transport as $service)<div class="col-sm-6 col-lg-3"><a class="landing-card h-100" href="{{ route('transportation.show', $service) }}"><span class="landing-icon-circle">↗</span><h3 class="h5 mt-3 mb-1">{{ $service->service_name }}</h3><p class="small text-muted mb-0">{{ $service->destination?->name }}</p></a></div>@endforeach</div>@endif
        </section>

        <section class="landing-section mb-5" aria-labelledby="events-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><p class="landing-eyebrow mb-1">Events &amp; Festivals</p><h2 id="events-heading" class="h2 mb-0">Something to look forward to</h2></div><a class="landing-section-link" href="{{ route('events.index') }}">View all events <span aria-hidden="true">→</span></a></div>
            @if ($events->isEmpty())<div class="landing-empty">No upcoming public events are scheduled yet.</div>@else<div class="row g-3">@foreach ($events as $event)<div class="col-sm-6 col-lg-4"><a class="landing-card h-100" href="{{ route('events.show', $event) }}"><span class="badge text-bg-warning mb-3">{{ $event->event_date?->format('M j, Y') }}</span><h3 class="h5 mb-2">{{ $event->event_name }}</h3><p class="small text-muted mb-0">{{ $event->destination?->name }} · {{ $event->venue }}</p></a></div>@endforeach</div>@endif
        </section>

        <section class="landing-map-cta mb-5" aria-labelledby="map-heading">
            <div class="row align-items-center g-4">
                <div class="col-lg-8"><p class="landing-eyebrow mb-1">Explore on Map</p><h2 id="map-heading" class="h2 mb-2">See what is around you.</h2><p class="text-muted mb-0">Browse real mapped destinations, heritage, museums, services and events. The map stays honest when a place has not been geolocated yet.</p></div>
                <div class="col-lg-4 d-flex justify-content-lg-end"><a class="btn btn-success btn-lg" href="{{ route('map') }}">Open the map <span aria-hidden="true">→</span></a></div>
            </div>
        </section>

        <section class="landing-trip-cta mb-5" aria-labelledby="smart-trip-heading"><div class="row align-items-center g-4"><div class="col-lg-8"><p class="landing-eyebrow mb-1">Smart Trip</p><h2 id="smart-trip-heading" class="h2 mb-2">Turn inspiration into a plan.</h2><p class="text-muted mb-0">Save places, arrange a route and build an itinerary from the public information you discover.</p></div><div class="col-lg-4 d-flex flex-wrap justify-content-lg-end gap-2"><a class="btn btn-success" href="{{ route('smart-trip.index') }}">Open Smart Trip</a><a class="btn btn-outline-success" href="{{ route('map') }}">Open the Map</a></div></div></section>

        <section class="landing-section mb-5" aria-labelledby="reviews-heading"><div class="d-flex justify-content-between align-items-end mb-4"><div><p class="landing-eyebrow mb-1">Traveler voices</p><h2 id="reviews-heading" class="h2 mb-0">Real reviews from completed journeys</h2></div></div>@if ($reviews->isEmpty())<div class="landing-empty">No reviews are available yet. Feedback will appear here after completed journeys.</div>@else<div class="row g-3">@foreach ($reviews as $review)<div class="col-md-4"><article class="landing-review h-100"><div class="text-warning mb-2" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', (int) $review->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - (int) $review->rating) }}</span></div><p class="mb-3">“{{ Str::limit($review->comment, 180) }}”</p><footer class="small text-muted">{{ Str::of($review->tourist?->full_name ?? 'Traveler')->before(' ') }} · {{ $review->review_date?->format('M j, Y') }}</footer></article></div>@endforeach</div>@endif</section>

        <section class="landing-final-cta text-center"><p class="landing-eyebrow mb-2">Your next chapter starts here</p><h2 class="h2 mb-3">Ready to see Ethiopia differently?</h2><a class="btn btn-success btn-lg" href="{{ route('search') }}">Start exploring</a></section>
    </div>
@endsection
