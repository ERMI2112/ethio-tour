@extends('layouts.app')

@section('title', 'Route Map & Driving Distance · '.$trip->title)

@section('content')
<div class="container-fluid py-4 py-lg-5 smart-trip-map-page">
    <div class="container">
        <!-- Breadcrumbs & Navigation -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <a class="text-decoration-none text-muted small" href="{{ route('smart-trip.show', $trip) }}">
                    &larr; Back to Timeline Itinerary
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <span class="badge bg-success text-white">Smart Trip Multi-Stop Navigator</span>
                    <span class="badge bg-light text-dark border">{{ $trip->duration_days }} Days / {{ max(1, $trip->duration_days - 1) }} Nights</span>
                </div>
                <h1 class="h2 fw-bold text-dark mt-1 mb-0">{{ $trip->title }}</h1>
                <p class="text-muted small mb-0">
                    {{ $trip->start_date->format('M d, Y') }} – {{ $trip->end_date->format('M d, Y') }} &bull; {{ $trip->destinations->pluck('name')->join(', ') ?: 'Ethiopia' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('smart-trip.print', $trip) }}" target="_blank" rel="noopener">
                    <i class="bi bi-printer me-1"></i> Print Itinerary
                </a>
                <a class="btn btn-success btn-sm fw-bold" href="{{ route('smart-trip.show', $trip) }}">
                    Manage Stops &rarr;
                </a>
            </div>
        </div>

        @include('layouts.partials.flash-messages')

        <!-- Distance & Time Overview Strip -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Total Route Distance</div>
                    <div class="h3 fw-bold text-success mb-0" id="stat-total-distance">
                        {{ $routeData['formatted_total_distance'] }}
                    </div>
                    <div class="small text-muted mt-1">Estimated highway &amp; mountain routes</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Estimated Travel Time</div>
                    <div class="h3 fw-bold text-primary mb-0" id="stat-total-duration">
                        {{ $routeData['formatted_total_duration'] }}
                    </div>
                    <div class="small text-muted mt-1">Driving duration at ~55 km/h avg</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Mapped Stops</div>
                    <div class="h3 fw-bold text-dark mb-0">
                        {{ count($mappedItems) }} <small class="fs-6 text-muted">waypoint{{ count($mappedItems) === 1 ? '' : 's' }}</small>
                    </div>
                    <div class="small text-muted mt-1">Geo-located itinerary destinations</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Route Legs</div>
                    <div class="h3 fw-bold text-info mb-0">
                        {{ count($routeData['segments']) }} <small class="fs-6 text-muted">connector{{ count($routeData['segments']) === 1 ? '' : 's' }}</small>
                    </div>
                    <div class="small text-muted mt-1">Multi-stop journey sequence</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Waypoint List & Leg Breakdown -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold mb-0 text-dark">
                            <i class="bi bi-signpost-split text-success me-1"></i> Journey Waypoints
                        </h2>
                        <span class="badge bg-light text-muted border">{{ count($mappedItems) }} Stops</span>
                    </div>
                    <div class="card-body p-3 overflow-auto flex-grow-1" style="max-height: 620px;">
                        @if($mappedItems->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">🗺️</div>
                                <h3 class="h6 fw-bold">No mapped stops in this itinerary</h3>
                                <p class="small text-muted mb-3">Add attractions, hotels, or destinations to your trip to view route polylines and driving distances.</p>
                                <a class="btn btn-outline-success btn-sm" href="{{ route('smart-trip.items.create', $trip) }}">
                                    Add Itinerary Items &rarr;
                                </a>
                            </div>
                        @else
                            <div class="timeline-route">
                                @foreach($mappedItems as $idx => $item)
                                    @php($stepNum = $idx + 1)
                                    <div class="card border rounded-3 p-3 mb-2 bg-white shadow-xs position-relative waypoint-card" data-lat="{{ $item['latitude'] }}" data-lng="{{ $item['longitude'] }}">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                {{ $stepNum }}
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                    <span class="badge bg-light text-dark border small">{{ $item['type_label'] }}</span>
                                                    <span class="small text-muted">
                                                        {{ $item['planned_date'] ? \Carbon\Carbon::parse($item['planned_date'])->format('M d') : 'Day '.$stepNum }}
                                                    </span>
                                                </div>
                                                <h3 class="h6 fw-bold text-dark mb-1 text-truncate">{{ $item['title'] }}</h3>
                                                <p class="small text-muted mb-0 text-truncate">📍 {{ $item['destination'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if(isset($routeData['segments'][$idx]))
                                        @php($seg = $routeData['segments'][$idx])
                                        <div class="d-flex align-items-center justify-content-center my-2">
                                            <div class="bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-1 small fw-semibold d-inline-flex align-items-center gap-1 shadow-xs">
                                                <span>🚗 <strong>{{ $seg['formatted_distance'] }}</strong></span>
                                                <span class="text-muted">&bull;</span>
                                                <span>⏱️ {{ $seg['formatted_duration'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Leaflet Map Shell -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div id="map-status" class="alert alert-info d-none m-3" role="status"></div>
                    <div class="map-shell smart-trip-route-map position-relative" style="min-height: 600px; height: 100%;">
                        <div id="tourism-map"
                             class="w-100 h-100 smart-trip-route-map-canvas"
                             data-endpoint="{{ route('smart-trip.map.data', $trip) }}"
                             data-is-route-map="true"
                             aria-label="Smart Trip Multi-Stop Route Map"></div>
                        <div id="map-loading" class="map-overlay badge rounded-pill text-bg-light shadow-sm position-absolute top-50 start-50 translate-middle p-3">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Calculating multi-stop driving routes…
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="map-filters" class="d-none">
            <input type="hidden" name="trip" value="{{ $trip->trip_id }}">
        </form>
        <button id="map-near-me" class="d-none" type="button">Near me</button>
        <p id="map-result-count" class="d-none"></p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/map.js')
@endpush
