@extends('layouts.app')

@section('title', 'Attractions & Heritage Sites · Tourism Bureau')

@section('content')
<div class="container-fluid py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Regional Heritage Governance</span>
            <h1 class="ws-title">Attractions &amp; Heritage Sites</h1>
            <p class="ws-lead">Curate and publish official visitor information, opening hours, admission tariffs, GPS coordinates, and historical photography for your regional destinations.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('bureau.dashboard') }}">
                &larr; Bureau Dashboard
            </a>
            <a class="btn btn-success fw-bold" href="{{ route('bureau.attractions.create') }}">
                <i class="bi bi-plus-lg me-1"></i> Add New Attraction
            </a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <!-- Search & Filter Bar -->
    <form class="card card-body border-0 shadow-sm rounded-4 mb-4" method="GET" action="{{ route('bureau.attractions.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold" for="search-q">Search Attractions</label>
                <input id="search-q" class="form-control" name="q" value="{{ $search }}" placeholder="Search by name, address, keywords...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="filter-category">Category</label>
                <select id="filter-category" class="form-select" name="category">
                    <option value="">All categories</option>
                    @foreach(\App\Models\Attraction::CATEGORIES as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold" for="filter-dest">Destination</label>
                <select id="filter-dest" class="form-select" name="destination_id">
                    <option value="">All assigned destinations</option>
                    @foreach($destinations as $d)
                        <option value="{{ $d->destination_id }}" @selected((string)$selectedDestination === (string)$d->destination_id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary fw-bold flex-grow-1" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('bureau.attractions.index') }}">Clear</a>
            </div>
        </div>
    </form>

    <!-- Attractions Grid -->
    @if($attractions->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="display-4 mb-3">🏛️</div>
            <h2 class="h4 fw-bold">No attractions found</h2>
            <p class="text-muted mb-4">No heritage sites or attractions match your current filter criteria in your assigned destinations.</p>
            <div>
                <a class="btn btn-success fw-bold" href="{{ route('bureau.attractions.create') }}">
                    <i class="bi bi-plus-circle me-1"></i> Add Your First Attraction
                </a>
            </div>
        </div>
    @else
        <div class="row g-4 mb-4">
            @foreach($attractions as $attraction)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative" style="height: 180px; background: #0c1e14;">
                            <img src="{{ $attraction->primaryImageUrl() }}" alt="{{ $attraction->name }}" class="w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-3 d-flex gap-1">
                                <span class="badge bg-dark bg-opacity-75 text-white border border-white-50">
                                    {{ $attraction->categoryLabel() }}
                                </span>
                                @if($attraction->is_featured)
                                    <span class="badge bg-warning text-dark border">
                                        ★ Featured
                                    </span>
                                @endif
                            </div>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-success text-white">
                                    📍 {{ $attraction->destination?->name ?? 'Ethiopia' }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h5 fw-bold mb-1 text-dark">{{ $attraction->name }}</h2>
                            @if($attraction->location_address)
                                <p class="small text-muted mb-2">
                                    <i class="bi bi-geo-alt me-1"></i> {{ $attraction->location_address }}
                                </p>
                            @endif

                            <p class="small text-secondary flex-grow-1 mb-3">
                                {{ \Illuminate\Support\Str::limit($attraction->description, 130) }}
                            </p>

                            <div class="p-3 bg-light rounded-3 mb-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Opening Hours:</span>
                                    <strong>{{ $attraction->opening_hours ?: 'Standard Daytime' }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Entry Tariff:</span>
                                    <strong class="text-success">{{ $attraction->entry_fee ? number_format((float)$attraction->entry_fee, 2).' ETB' : 'Free / Included' }}</strong>
                                </div>
                                @if($attraction->hasCoordinates())
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Coordinates:</span>
                                        <a href="{{ $attraction->googleMapsUrl() }}" target="_blank" class="text-decoration-none">
                                            {{ round((float)$attraction->latitude, 4) }}, {{ round((float)$attraction->longitude, 4) }} &nearr;
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                <a class="btn btn-outline-primary btn-sm fw-semibold" href="{{ route('bureau.attractions.edit', $attraction) }}">
                                    Edit Details
                                </a>
                                <form method="POST" action="{{ route('bureau.attractions.destroy', $attraction) }}" onsubmit="return confirm('Remove this attraction from publication?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-3 border-top">
            {{ $attractions->links() }}
        </div>
    @endif
</div>
@endsection
