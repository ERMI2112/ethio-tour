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
                    Connect with verified regional historians, wilderness trekking guides, and cultural experts licensed by the Tourism Bureau across Ethiopia.
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
    {{-- Search & Filter Card --}}
    <form class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white p-3 p-lg-4" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted" for="q">Search expertise, language, or destination</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input class="form-control border-start-0" id="q" name="q" value="{{ $search }}" placeholder="History, Simien trekking, Lalibela, German...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="availability">Availability Status</label>
                <select class="form-select" id="availability" name="availability">
                    <option value="">Any availability</option>
                    <option value="available" @selected($availability === 'available')>🟢 Available for Bookings</option>
                    <option value="unavailable" @selected($availability === 'unavailable')>⚪ Currently Unavailable</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-vn-red w-100 fw-bold shadow-sm" type="submit">
                    Search
                </button>
            </div>
        </div>
    </form>

    <div class="row g-4">
        @forelse ($guides as $guide)
            <div class="col-md-6 col-xl-4">
                <article class="vn-experience-card">
                    {{-- Photo Header --}}
                    @php($displayName = $guide->full_name ?: ($guide->user?->email ?? 'Licensed Tour Guide'))
                    <div class="vn-card-media position-relative">
                        <img src="{{ $guide->profileImageUrl() }}"
                             alt="{{ $displayName }}"
                             loading="lazy">

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
                            <div class="small fw-semibold text-warning" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Licensed Tour Guide</div>
                            <h2 class="h5 mb-0 text-white fw-bold" style="font-family: var(--font-display);">
                                <a class="text-white text-decoration-none" href="{{ route('tour-guides.show', $guide) }}">
                                    {{ $displayName }}
                                </a>
                            </h2>
                        </div>
                    </div>

                    <div class="vn-card-body">
                        <div class="d-flex flex-wrap gap-1.5 mb-2">
                            <span class="badge bg-light text-dark border small rounded-pill px-2.5 py-1">License: {{ $guide->license_number }}</span>
                            @if($guide->destination)
                                <span class="badge bg-primary-subtle text-primary small rounded-pill px-2.5 py-1">📍 {{ $guide->destination->name }}</span>
                            @endif
                            @if($guide->years_of_experience)
                                <span class="badge bg-success-subtle text-success small rounded-pill px-2.5 py-1">{{ $guide->years_of_experience }} yrs exp</span>
                            @endif
                        </div>

                        <p class="small text-secondary flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; font-size: 0.85rem;">
                            {{ $guide->expertise }}
                        </p>

                        {{-- Languages --}}
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach(array_slice($guide->languagesList(), 0, 3) as $lang)
                                <span class="badge bg-white text-muted border small rounded-pill px-2 py-0.5">{{ $lang }}</span>
                            @endforeach
                            @if(count($guide->languagesList()) > 3)
                                <span class="badge bg-light text-muted small rounded-pill px-2 py-0.5">+{{ count($guide->languagesList()) - 3 }}</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <div>
                                <div class="vn-card-price">
                                    {{ $guide->daily_rate === null ? 'Rate on request' : number_format((float) $guide->daily_rate, 2).' ETB' }}
                                    @if($guide->daily_rate !== null)
                                        <small>/ day</small>
                                    @endif
                                </div>
                                <div class="vn-card-rating">
                                    {{ $guide->average_rating === null ? 'No reviews yet' : '★ ' . number_format((float) $guide->average_rating, 1) . ' (' . ($guide->review_count ?? 0) . ')' }}
                                </div>
                            </div>
                            <a class="btn btn-vn-emerald btn-sm px-3" href="{{ route('tour-guides.show', $guide) }}">
                                View profile &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <x-ui.empty-state title="No guides found" message="Try another expertise search or availability filter. New verified guides will appear here when available." />
            </div>
        @endforelse
    </div>
</div>
@endsection
