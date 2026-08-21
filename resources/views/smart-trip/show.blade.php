@extends('layouts.app')

@section('title', $trip->title.' · Smart Trip Itinerary')

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a class="text-decoration-none text-muted small" href="{{ route('smart-trip.index') }}">
            &larr; Back to My Trips
        </a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('smart-trip.print', $trip) }}" target="_blank">
                <i class="bi bi-printer me-1"></i> Print / Save PDF
            </a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('smart-trip.edit', $trip) }}">
                <i class="bi bi-gear me-1"></i> Edit Trip
            </a>
        </div>
    </div>

    <!-- Executive Hero Banner -->
    <header class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0d3824 0%, #155737 100%);">
        <div class="card-body p-4 p-md-5 text-white position-relative">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 text-uppercase">
                            {{ ucfirst($trip->status) }} Itinerary
                        </span>
                        <span class="text-white-50 small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $trip->start_date->format('M d, Y') }} – {{ $trip->end_date->format('M d, Y') }}
                            ({{ $trip->start_date->diffInDays($trip->end_date) + 1 }} Days)
                        </span>
                    </div>
                    <h1 class="display-6 fw-bold mb-2">{{ $trip->title }}</h1>
                    <p class="lead text-white-50 mb-0">
                        <i class="bi bi-geo-alt-fill text-warning me-1"></i>
                        {{ $trip->destinations->pluck('name')->join(', ') ?: 'Ethiopian Destinations' }}
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex flex-column gap-2 w-100 w-lg-auto">
                        <a class="btn btn-warning fw-bold text-dark px-4 shadow-sm" href="{{ route('smart-trip.ai.create', $trip) }}">
                            ✨ Plan with Smart Trip AI
                        </a>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-light btn-sm flex-grow-1" href="{{ route('smart-trip.items.create', $trip) }}">
                                <i class="bi bi-plus-circle me-1"></i> Add Place
                            </a>
                            <a class="btn btn-outline-light btn-sm flex-grow-1" href="{{ route('smart-trip.map', $trip) }}">
                                <i class="bi bi-map me-1"></i> Route Map
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @include('layouts.partials.flash-messages')

    @php($groupedItems = collect($items)->groupBy(fn ($item) => $item['planned_date'] ?: 'Flexible'))

    <div class="row g-4">
        <!-- Main Day-by-Day Timeline -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-1">Itinerary Timeline</h2>
                    <p class="text-muted small mb-0">{{ count($items) }} planned stops across {{ count($groupedItems) }} travel days</p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('smart-trip.suggest', $trip) }}">
                        @csrf
                        <button class="btn btn-outline-primary btn-sm" type="submit" title="Refresh deterministic recommendations">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh suggestions
                        </button>
                    </form>
                    <a class="btn btn-success btn-sm" href="{{ route('smart-trip.items.create', $trip) }}">
                        <i class="bi bi-plus-lg me-1"></i> Add stop
                    </a>
                </div>
            </div>

            @forelse($groupedItems as $day => $dayItems)
                <section class="mb-5">
                    <!-- Day Header Banner -->
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3" style="background: #eef7f2; border-left: 5px solid #198754;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                {{ $loop->iteration }}
                            </div>
                            <h3 class="h5 fw-bold mb-0 text-success">
                                {{ $day === 'Flexible' ? 'Flexible Schedule' : date('l, F j, Y', strtotime($day)) }}
                            </h3>
                        </div>
                        <span class="badge bg-white text-dark border px-2 py-1">
                            {{ count($dayItems) }} {{ \Illuminate\Support\Str::plural('experience', count($dayItems)) }}
                        </span>
                    </div>

                    <!-- Stops in this Day -->
                    <div class="d-flex flex-column gap-3">
                        @foreach($dayItems as $item)
                            <article class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                <div class="row g-0">
                                    <!-- Photo Thumbnail Column -->
                                    <div class="col-md-4 position-relative" style="min-height: 160px; background: #0d3824;">
                                        <img src="{{ $item['image_url'] ?? asset('images/destinations/gondar-castles.jpg') }}" 
                                             alt="{{ $item['title'] }}" 
                                             style="width: 100%; height: 100%; object-fit: cover;" 
                                             loading="lazy">
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-dark bg-opacity-75 text-white border border-white-50">
                                                <i class="bi {{ $item['icon'] ?? 'bi-pin-map' }} me-1"></i>
                                                {{ $item['type_label'] }}
                                            </span>
                                        </div>
                                        @if(!empty($item['price_hint']))
                                            <div class="position-absolute bottom-0 start-0 m-2">
                                                <span class="badge bg-warning text-dark fw-bold shadow-sm">
                                                    {{ $item['price_hint'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Content & Actions Column -->
                                    <div class="col-md-8 d-flex flex-column">
                                        <div class="card-body p-3 p-md-4 flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                <h4 class="h5 fw-bold mb-0">
                                                    <a href="{{ $item['detail_url'] }}" class="text-dark text-decoration-none">
                                                        {{ $item['title'] }}
                                                    </a>
                                                </h4>
                                                @if($item['rating'] !== null)
                                                    <span class="small fw-bold text-warning d-flex align-items-center gap-1">
                                                        ★ {{ number_format($item['rating'], 1) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="small text-muted mb-2">
                                                <i class="bi bi-geo-alt me-1"></i> {{ $item['destination'] ?: 'Ethiopia' }}
                                            </p>

                                            <p class="small text-secondary mb-3">
                                                {{ \Illuminate\Support\Str::limit((string) ($item['summary'] ?: 'Public verified tourism information.'), 140) }}
                                            </p>

                                            <!-- Direct Links -->
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @if($item['detail_url'])
                                                    <a class="btn btn-outline-success btn-sm" href="{{ $item['detail_url'] }}">
                                                        View details <span aria-hidden="true">&rarr;</span>
                                                    </a>
                                                @endif
                                                @if($item['map_url'])
                                                    <a class="btn btn-outline-secondary btn-sm" href="{{ $item['map_url'] }}">
                                                        <i class="bi bi-geo-alt me-1"></i> Map
                                                    </a>
                                                @endif
                                                @if($item['booking_url'])
                                                    <a class="btn btn-primary btn-sm fw-semibold" href="{{ $item['booking_url'] }}">
                                                        <i class="bi bi-credit-card me-1"></i> Book now
                                                    </a>
                                                @endif
                                            </div>

                                            @if(!empty($item['notes']))
                                                <div class="p-2 bg-light rounded small border-start border-3 border-warning mb-3">
                                                    <strong>Note:</strong> {{ $item['notes'] }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Item Controls Toolbar -->
                                        <div class="card-footer bg-light bg-opacity-50 border-top p-2 px-3">
                                            <div class="row g-2 align-items-center">
                                                <!-- Move Day Form -->
                                                <div class="col-md-5">
                                                    <form method="POST" action="{{ route('smart-trip.items.move', [$trip, $item['trip_item_id']]) }}">
                                                        @csrf @method('PATCH')
                                                        <div class="input-group input-group-sm">
                                                            <input class="form-control" type="date" name="planned_date" value="{{ $item['planned_date'] }}" min="{{ $trip->start_date->toDateString() }}" max="{{ $trip->end_date->toDateString() }}" required>
                                                            <button class="btn btn-outline-secondary" type="submit">Move</button>
                                                        </div>
                                                    </form>
                                                </div>

                                                <!-- Notes Form -->
                                                <div class="col-md-4">
                                                    <form method="POST" action="{{ route('smart-trip.items.notes', [$trip, $item['trip_item_id']]) }}">
                                                        @csrf @method('PATCH')
                                                        <div class="input-group input-group-sm">
                                                            <input class="form-control" name="notes" value="{{ $item['notes'] }}" placeholder="Add note">
                                                            <button class="btn btn-outline-secondary" type="submit">Save</button>
                                                        </div>
                                                    </form>
                                                </div>

                                                <!-- Position & Delete -->
                                                <div class="col-md-3 d-flex justify-content-end gap-1">
                                                    <form method="POST" action="{{ route('smart-trip.items.position', [$trip, $item['trip_item_id']]) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="direction" value="up">
                                                        <button class="btn btn-outline-secondary btn-sm" title="Move earlier" type="submit">↑</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('smart-trip.items.position', [$trip, $item['trip_item_id']]) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="direction" value="down">
                                                        <button class="btn btn-outline-secondary btn-sm" title="Move later" type="submit">↓</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('smart-trip.items.destroy', [$trip, $item['trip_item_id']]) }}" onsubmit="return confirm('Remove this stop from your itinerary?');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm" title="Remove stop" type="submit">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <div class="mb-3 fs-1">🗺️</div>
                    <h3 class="h4 fw-bold mb-2">Your itinerary is empty</h3>
                    <p class="text-muted mb-4">Start discovering verified attractions, hotels, local tour guides, and festivals to build your dream Ethiopian journey.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a class="btn btn-warning fw-bold text-dark px-4" href="{{ route('smart-trip.ai.create', $trip) }}">
                            ✨ Generate with Smart Trip AI
                        </a>
                        <a class="btn btn-success px-4" href="{{ route('smart-trip.items.create', $trip) }}">
                            <i class="bi bi-plus-lg me-1"></i> Add from Catalog
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Right Sidebar Stats & Quick Links -->
        <div class="col-lg-4">
            <!-- Trip Quick Overview -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">Trip Overview</h3>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted small">Duration</span>
                            <strong class="small">{{ $trip->start_date->diffInDays($trip->end_date) + 1 }} Days / {{ $trip->start_date->diffInDays($trip->end_date) }} Nights</strong>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted small">Total Stops</span>
                            <strong class="small text-success">{{ count($items) }} Stops</strong>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted small">Dates</span>
                            <strong class="small">{{ $trip->start_date->format('M d') }} – {{ $trip->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Interests &amp; Preferences</span>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($trip->preferences ?? [] as $pref)
                                    <span class="badge bg-light text-success border">{{ ucfirst($pref) }}</span>
                                @empty
                                    <span class="small text-muted">No specific preferences selected</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Destinations Included -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">Destinations</h3>
                    <div class="d-flex flex-column gap-2">
                        @foreach($trip->destinations as $destination)
                            <a href="{{ route('destinations.show', $destination) }}" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none bg-light hover-shadow">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-success fw-bold">📍 {{ $destination->name }}</span>
                                </div>
                                <span class="small text-muted">&rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- AI Planning Callout -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #155737 0%, #0d3824 100%);">
                <h4 class="h5 fw-bold mb-2">Need Inspiration?</h4>
                <p class="small text-white-50 mb-3">Smart Trip AI can suggest daily stops, verified cultural events, and certified local guides tailored to your exact pace.</p>
                <a class="btn btn-warning fw-bold text-dark w-100" href="{{ route('smart-trip.ai.create', $trip) }}">
                    ✨ Open Smart Trip AI
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
