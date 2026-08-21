@extends('layouts.app')

@section('title', 'Destinations in Ethiopia')

@section('content')
<div class="destinations-hero-section py-5 border-bottom mb-4">
    <div class="container">
        <div class="mb-2">
            <x-ui.back-button :href="route('home')" label="Back to Home" />
        </div>
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary text-uppercase px-2.5 py-1 mb-2 fw-semibold">Regional Discovery</span>
                <h1 class="display-6 fw-bold mb-3">Discover Ethiopia's Iconic Destinations</h1>
                <p class="lead text-secondary mb-0">From the imperial castle compounds of Gondar to ancient rock-hewn sanctuaries, vibrant lakes, and dramatic highlands, explore regional destinations and connect with verified local services.</p>
            </div>
            <div class="col-lg-4">
                <div class="destination-search-box p-3 rounded-3 shadow-sm">
                    <form class="d-flex gap-2" method="GET" action="{{ route('destinations.index') }}">
                        <label class="visually-hidden" for="destination-search">Search destinations</label>
                        <input class="form-control" id="destination-search" name="q" placeholder="Search destination or region..." value="{{ $search }}">
                        <x-ui.button variant="primary" type="submit">Search</x-ui.button>
                    </form>
                    @if($search)
                        <div class="mt-2 text-end">
                            <a class="small text-muted text-decoration-none" href="{{ route('destinations.index') }}">&times; Clear search</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    @if ($destinations->isEmpty())
        <x-ui.empty-state title="No destinations found" message="Try a different search term or check back later as new regional destinations are published." />
    @else
        <div class="row g-4">
            @foreach ($destinations as $destination)
                @php
                    $mediaKey = $destination->slug ?: str($destination->name)->slug();
                    $heroPath = $destination->hero_image ?: ('/images/destinations/' . $mediaKey . '-hero.jpg');
                    $hasHeroFile = is_string($heroPath) && $heroPath !== '' && file_exists(public_path(ltrim($heroPath, '/')));
                @endphp
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0 d-flex flex-column destination-index-card overflow-hidden">
                        <div class="destination-card-media position-relative" style="height: 200px; overflow: hidden; background: #132a1f;">
                            @if ($hasHeroFile)
                                <img src="{{ asset(ltrim($heroPath, '/')) }}" alt="{{ $destination->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary-subtle">
                                    <span class="fs-1 fw-bold text-muted">{{ str($destination->name)->substr(0, 1)->upper() }}</span>
                                </div>
                            @endif
                            <span class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white border border-secondary">{{ $destination->location }}</span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h4 mb-1">
                                <a class="text-dark text-decoration-none stretched-link-target" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
                            </h2>

                            @if($destination->tagline)
                                <p class="text-muted small fw-medium mb-2">{{ $destination->tagline }}</p>
                            @endif

                            <p class="text-secondary small flex-grow-1 mb-3">
                                {{ \Illuminate\Support\Str::limit($destination->description, 175) }}
                            </p>

                            <div class="border-top pt-3 mt-auto">
                                <div class="d-flex flex-wrap gap-2 text-muted small mb-3">
                                    @if($destination->attractions_count > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <strong>{{ $destination->attractions_count }}</strong> {{ \Illuminate\Support\Str::plural('attraction', $destination->attractions_count) }}
                                        </span>
                                    @endif
                                    @if($destination->heritage_sites_count > 0 && $destination->attractions_count === 0)
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            <strong>{{ $destination->heritage_sites_count }}</strong> heritage {{ \Illuminate\Support\Str::plural('site', $destination->heritage_sites_count) }}
                                        </span>
                                    @endif
                                    @if($destination->public_services_count > 0)
                                        <span class="badge bg-light text-dark border">
                                            <strong>{{ $destination->public_services_count }}</strong> verified {{ \Illuminate\Support\Str::plural('service', $destination->public_services_count) }}
                                        </span>
                                    @endif
                                    @if($destination->upcoming_events_count > 0)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                            <strong>{{ $destination->upcoming_events_count }}</strong> {{ \Illuminate\Support\Str::plural('event', $destination->upcoming_events_count) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm flex-grow-1" href="{{ route('destinations.show', $destination) }}">
                                        Explore {{ $destination->name }}
                                    </a>
                                    @if($destination->hasCoordinates())
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}" aria-label="View {{ $destination->name }} on map">
                                            Map
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
