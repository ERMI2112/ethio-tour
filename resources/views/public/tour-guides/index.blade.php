@extends('layouts.app')

@section('title', 'Verified Tour Guides · Discover Ethiopia')

@section('content')
<div class="destinations-hero-section py-4 py-lg-5 border-bottom" style="background: linear-gradient(180deg, #f4f7f6 0%, #e8f0ec 100%);">
    <div class="container">
        <div class="mb-2">
            <x-ui.back-button :href="route('home')" label="Back to Home" />
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-success-subtle text-success text-uppercase px-2.5 py-1 mb-2 fw-bold border border-success-subtle" style="letter-spacing: 0.08em; font-size: 0.72rem;">
                    Accredited Local Insight &bull; Things to Do
                </span>
                <h1 class="display-6 fw-bold mb-2 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Find an Accredited Tour Guide</h1>
                <p class="text-secondary mb-0" style="max-width: 800px; font-size: 1.05rem; line-height: 1.6;">
                    Connect with verified regional historians, wilderness trekking leaders, and cultural experts licensed by the Tourism Bureau across Ethiopia.
                </p>
            </div>
            <div>
                <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.85rem;">
                    🛡️ Bureau-Verified Guides
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 py-lg-5">
    {{-- Search & Multi-Criteria Filter Card --}}
    <form class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white p-3 p-lg-4" method="GET" action="{{ route('tour-guides.index') }}">
        <div class="row g-3 align-items-end">
            {{-- Search Text --}}
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-bold text-muted" for="q">
                    <i class="bi bi-search me-1"></i> Search Guide, Skill, or Heritage
                </label>
                <input class="form-control rounded-3"
                       id="q"
                       name="q"
                       value="{{ $search }}"
                       placeholder="e.g. Yared, Simien trekking, Lalibela, German, Castles...">
            </div>

            {{-- Destination --}}
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-bold text-muted" for="destination">
                    <i class="bi bi-geo-alt me-1"></i> Destination
                </label>
                <select class="form-select rounded-3" id="destination" name="destination">
                    <option value="">All Destinations</option>
                    @foreach($destinations as $dest)
                        <option value="{{ $dest->destination_id }}" @selected($destinationId == $dest->destination_id || $destinationId === $dest->slug)>
                            {{ $dest->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Spoken Language --}}
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold text-muted" for="language">
                    <i class="bi bi-translate me-1"></i> Language
                </label>
                <select class="form-select rounded-3" id="language" name="language">
                    <option value="">All Languages</option>
                    @foreach(['English', 'Amharic', 'French', 'German', 'Italian', 'Spanish'] as $lang)
                        <option value="{{ $lang }}" @selected($language === $lang)>{{ $lang }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Availability Status --}}
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-bold text-muted" for="availability">
                    <i class="bi bi-calendar-check me-1"></i> Availability
                </label>
                <select class="form-select rounded-3" id="availability" name="availability">
                    <option value="">Any Status</option>
                    <option value="available" @selected($availability === 'available')>🟢 Available</option>
                    <option value="unavailable" @selected($availability === 'unavailable')>⚪ Unavailable</option>
                </select>
            </div>

            {{-- Submit & Clear --}}
            <div class="col-lg-2 col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-vn-red flex-grow-1 fw-bold shadow-sm rounded-3 py-2" type="submit">
                        Filter
                    </button>
                    @if($search || $availability || $destinationId || $language || ($sort && $sort !== 'recommended'))
                        <a class="btn btn-light border rounded-3 py-2 text-muted" href="{{ route('tour-guides.index') }}" title="Clear filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Filter Chips --}}
        <div class="d-flex flex-wrap align-items-center gap-1.5 pt-3 mt-3 border-top">
            <span class="small text-muted fw-bold me-1" style="font-size: 0.75rem;">Quick Topics:</span>
            @foreach(['Simien Trekking', 'Gondar Castles', 'Lalibela Churches', 'Lake Tana Monasteries', 'Birdwatching', 'French', 'German'] as $quickTag)
                <a href="{{ route('tour-guides.index', ['q' => $quickTag]) }}"
                   class="badge {{ $search === $quickTag ? 'bg-primary text-white' : 'bg-light text-dark border' }} text-decoration-none rounded-pill px-2.5 py-1"
                   style="font-size: 0.75rem;">
                    {{ $quickTag }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Result Counter & Sorting Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <span class="fw-bold text-dark fs-6">{{ count($guides) }}</span>
            <span class="text-secondary small">{{ count($guides) === 1 ? 'verified tour guide available' : 'verified tour guides available' }}</span>
        </div>
    </div>

    {{-- Guides Grid --}}
    <div class="row g-4">
        @forelse ($guides as $guide)
            <div class="col-md-6 col-xl-4">
                <article class="vn-experience-card h-100 d-flex flex-column">
                    {{-- Photo Header --}}
                    @php($displayName = $guide->full_name ?: ($guide->user?->email ?? 'Licensed Tour Guide'))
                    <div class="vn-card-media position-relative">
                        <img src="{{ $guide->profileImageUrl() }}"
                             alt="{{ $displayName }}"
                             loading="lazy"
                             decoding="async"
                             style="height: 220px; width: 100%; object-fit: cover;">

                        <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1.5" style="z-index: 5;">
                            <span class="vn-badge-frosted">
                                🛡️ Verified Guide
                            </span>
                            @if ($guide->availability_status === 'available')
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.7rem; font-weight: 700;">
                                    🟢 Available
                                </span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                    Unavailable
                                </span>
                            @endif
                        </div>

                        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(to top, rgba(6, 33, 51, 0.92) 0%, rgba(6, 33, 51, 0.4) 70%, transparent 100%); z-index: 4;">
                            <div class="small fw-semibold text-warning" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Licensed Regional Guide
                            </div>
                            <h2 class="h5 mb-0 text-white fw-bold" style="font-family: var(--font-display);">
                                <a class="text-white text-decoration-none" href="{{ route('tour-guides.show', $guide) }}">
                                    {{ $displayName }}
                                </a>
                            </h2>
                        </div>
                    </div>

                    <div class="vn-card-body d-flex flex-column flex-grow-1 p-3.5">
                        {{-- Location & Experience Badges --}}
                        <div class="d-flex flex-wrap gap-1.5 mb-2">
                            @if($guide->destination)
                                <span class="badge bg-primary-subtle text-primary small rounded-pill px-2.5 py-1">
                                    📍 {{ $guide->destination->name }}
                                </span>
                            @endif
                            @if($guide->years_of_experience)
                                <span class="badge bg-success-subtle text-success small rounded-pill px-2.5 py-1">
                                    {{ $guide->years_of_experience }} yrs experience
                                </span>
                            @endif
                        </div>

                        {{-- Expertise Summary --}}
                        <p class="small text-secondary mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; font-size: 0.85rem;">
                            {{ $guide->expertise }}
                        </p>

                        {{-- Spoken Languages --}}
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach(array_slice($guide->languagesList(), 0, 3) as $lang)
                                <span class="badge bg-white text-muted border small rounded-pill px-2 py-0.5">🗣️ {{ $lang }}</span>
                            @endforeach
                            @if(count($guide->languagesList()) > 3)
                                <span class="badge bg-light text-muted small rounded-pill px-2 py-0.5">+{{ count($guide->languagesList()) - 3 }}</span>
                            @endif
                        </div>

                        {{-- Specialties Badges --}}
                        @if(!empty($guide->specialtiesList()))
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach(array_slice($guide->specialtiesList(), 0, 2) as $spec)
                                    <span class="badge bg-light text-dark small rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                        ● {{ $spec }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Pricing & Action --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <div>
                                <div class="vn-card-price">
                                    {{ $guide->daily_rate === null ? 'Rate on request' : number_format((float) $guide->daily_rate, 2).' ETB' }}
                                    @if($guide->daily_rate !== null)
                                        <small>/ day</small>
                                    @endif
                                </div>
                                <div class="vn-card-rating">
                                    {{ $guide->average_rating === null ? '★ New Guide' : '★ ' . number_format((float) $guide->average_rating, 1) . ' (' . ($guide->review_count ?? 0) . ')' }}
                                </div>
                            </div>
                            <a class="btn btn-vn-emerald btn-sm px-3 fw-bold rounded-pill shadow-sm" href="{{ route('tour-guides.show', $guide) }}">
                                View Profile &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <div class="fs-1 mb-2">🧭</div>
                    <h2 class="h5 fw-bold text-dark mb-1">No tour guides found</h2>
                    <p class="text-muted small mb-3">No verified tour guides match your search criteria. Try removing filters or searching for another language or destination.</p>
                    <div>
                        <a class="btn btn-vn-emerald btn-sm px-4 rounded-pill fw-bold" href="{{ route('tour-guides.index') }}">
                            Reset All Filters
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
