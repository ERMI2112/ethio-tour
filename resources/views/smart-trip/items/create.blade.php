@extends('layouts.app')

@section('title', 'Add Stop · '.$trip->title)

@section('content')
<div class="container py-4 py-lg-5">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <a class="small text-muted text-decoration-none" href="{{ route('smart-trip.show', $trip) }}">
                &larr; Back to itinerary
            </a>
            <h1 class="display-6 fw-bold mt-2 mb-1">Add a verified place or experience</h1>
            <p class="text-muted mb-0">Search the same public catalog used by Ethio Tour discovery.</p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            {{ $trip->start_date->format('M d') }} – {{ $trip->end_date->format('M d, Y') }}
        </span>
    </div>

    <!-- Search & Filter Card -->
    <form class="card border-0 shadow-sm rounded-4 mb-4" method="GET" action="{{ route('smart-trip.items.create', $trip) }}">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold" for="item-search">Search Catalog</label>
                    <input class="form-control" id="item-search" name="q" value="{{ $query }}" placeholder="e.g. Castle, Coffee, Safari 4x4, Epiphany, Guide">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold" for="item-type">Filter by Category</label>
                    <select class="form-select" id="item-type" name="type">
                        <option value="">All public tourism content</option>
                        @foreach(['destinations' => 'Destinations', 'heritage' => 'Heritage Sites & Castles', 'museums' => 'Museums', 'hotels' => 'Accommodations & Hotels', 'restaurants' => 'Restaurants & Dining', 'transportation' => 'Transportation & 4x4 Rental', 'events' => 'Cultural Festivals & Events', 'guides' => 'Certified Tour Guides', 'services' => 'All Tourism Services'] as $key => $label)
                            <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success fw-bold w-100" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Search Results Grid -->
    @if($results->isEmpty())
        <x-ui.empty-state title="No public results found" message="Try searching for another place, hotel, restaurant, or festival in Ethiopia." />
    @else
        <div class="row g-4">
            @foreach($results as $result)
                @php
                    $lowerTitle = strtolower($result['title']);
                    $type = $result['trip_item_type'] ?? 'service';
                    $itemImg = asset('images/destinations/gondar-castles.jpg');
                    if (str_contains($lowerTitle, 'coffee') || str_contains($lowerTitle, 'restaurant') || $type === 'restaurant') {
                        $itemImg = asset('images/services/coffee-tasting.jpg');
                    } elseif (str_contains($lowerTitle, '4x4') || str_contains($lowerTitle, 'safari') || $type === 'transportation') {
                        $itemImg = asset('images/services/safari-4x4.jpg');
                    } elseif (str_contains($lowerTitle, 'hotel') || str_contains($lowerTitle, 'suite') || $type === 'hotel') {
                        $itemImg = asset('images/services/hotel-suite.jpg');
                    } elseif (str_contains($lowerTitle, 'timkat') || $type === 'event') {
                        $itemImg = asset('images/events/timkat-festival.jpg');
                    } elseif (str_contains($lowerTitle, 'meskel')) {
                        $itemImg = asset('images/events/meskel-festival.jpg');
                    } elseif ($type === 'guide') {
                        $itemImg = asset('images/guides/tour-guide.jpg');
                    }
                @endphp
                <div class="col-md-6 col-xl-4">
                    <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative" style="height: 170px; background: #0d3824;">
                            <img src="{{ $itemImg }}" alt="{{ $result['title'] }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 text-white border border-white-50">
                                    {{ $result['type_label'] }}
                                </span>
                            </div>
                            @if($result['rating'] !== null)
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-warning text-dark fw-bold shadow-sm">
                                        ★ {{ number_format($result['rating'], 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h5 fw-bold mb-2 text-dark">{{ $result['title'] }}</h2>
                            <p class="small text-secondary flex-grow-1 mb-3">
                                {{ \Illuminate\Support\Str::limit((string) ($result['summary'] ?: 'Public verified tourism information.'), 140) }}
                            </p>
                            <form method="POST" action="{{ route('smart-trip.items.store', $trip) }}" class="pt-3 border-top mt-auto">
                                @csrf
                                <input type="hidden" name="item_type" value="{{ $result['trip_item_type'] }}">
                                <input type="hidden" name="item_id" value="{{ $result['trip_item_id'] }}">
                                <div class="input-group input-group-sm mb-2">
                                    <label class="input-group-text bg-light text-muted fw-semibold" for="date-{{ $result['trip_item_type'] }}-{{ $result['trip_item_id'] }}">
                                        Plan for Day
                                    </label>
                                    <input class="form-control" 
                                           id="date-{{ $result['trip_item_type'] }}-{{ $result['trip_item_id'] }}" 
                                           type="date" 
                                           name="planned_date" 
                                           value="{{ $trip->start_date->toDateString() }}" 
                                           min="{{ $trip->start_date->toDateString() }}" 
                                           max="{{ $trip->end_date->toDateString() }}" 
                                           required>
                                </div>
                                <button class="btn btn-outline-success btn-sm fw-bold w-100" type="submit">
                                    <i class="bi bi-plus-lg me-1"></i> Add to Itinerary
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $results->links() }}
        </div>
    @endif
</div>
@endsection
