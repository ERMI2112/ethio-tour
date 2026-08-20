@extends('layouts.app')

@section('title', 'Destinations')

@section('content')
<div class="bg-light py-5 border-bottom mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="text-uppercase text-primary fw-semibold small mb-2">Regional Discovery</p>
                <h1 class="display-6 fw-bold mb-3">Discover Ethiopia's Iconic Destinations</h1>
                <p class="lead text-muted mb-0">From the historic imperial castles of Gondar to ancient heritage and breathtaking landscapes, explore regional destinations and connect with verified local services.</p>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
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

<div class="container pb-5">
    @if ($destinations->isEmpty())
        <x-ui.empty-state title="No destinations found" message="Try a different search term or return later for new tourism information." />
    @else
        <div class="row g-4">
            @foreach ($destinations as $destination)
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm border-0 d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $destination->location }}</span>
                                @if($destination->hasCoordinates())
                                    <a class="badge bg-light text-secondary text-decoration-none border" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}" title="View on interactive map">
                                        Map &nearr;
                                    </a>
                                @endif
                            </div>
                            <h2 class="h4 mb-2">
                                <a class="text-dark text-decoration-none" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
                            </h2>
                            <p class="text-muted small flex-grow-1 mb-3">
                                {{ \Illuminate\Support\Str::limit($destination->description, 180) }}
                            </p>
                            <div class="border-top pt-3 mt-auto">
                                <div class="d-flex flex-wrap gap-2 text-muted small mb-3">
                                    @if($destination->heritage_sites_count > 0)
                                        <span><strong>{{ $destination->heritage_sites_count }}</strong> heritage {{ \Illuminate\Support\Str::plural('site', $destination->heritage_sites_count) }}</span>
                                    @endif
                                    @if($destination->public_services_count > 0)
                                        <span>&bull; <strong>{{ $destination->public_services_count }}</strong> verified {{ \Illuminate\Support\Str::plural('service', $destination->public_services_count) }}</span>
                                    @endif
                                    @if($destination->upcoming_events_count > 0)
                                        <span>&bull; <strong>{{ $destination->upcoming_events_count }}</strong> {{ \Illuminate\Support\Str::plural('event', $destination->upcoming_events_count) }}</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm flex-grow-1" href="{{ route('destinations.show', $destination) }}">Explore {{ $destination->name }}</a>
                                    @if($destination->hasCoordinates())
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}">Map</a>
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
