@extends('layouts.app')

@section('title', 'Tour Guides')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Explore with local insight</p>
            <h1 class="display-6 fw-bold mb-2">Find a Tour Guide</h1>
            <p class="lead text-secondary mb-0">Connect with verified local guides for heritage walks, cultural experiences, and memorable days across Ethiopia.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" data-aos="fade-up" data-aos-delay="80">
            <div class="card-body p-3 p-lg-4 row g-3 align-items-end">
                <div class="col-md-7"><label class="form-label" for="q">Search expertise</label><input class="form-control" id="q" name="q" value="{{ $search }}" placeholder="History, heritage, city tours"></div>
                <div class="col-md-3"><label class="form-label" for="availability">Availability</label><select class="form-select" id="availability" name="availability"><option value="">Any availability</option><option value="available" @selected($availability === 'available')>Available</option><option value="unavailable" @selected($availability === 'unavailable')>Currently unavailable</option></select></div>
                <div class="col-md-2"><button class="btn btn-success w-100" type="submit">Search</button></div>
            </div>
        </form>

        <div class="row g-4">
            @forelse ($guides as $guide)
                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                    <article class="public-catalog-card h-100">
                        <div class="public-catalog-card__media public-catalog-card__media--guide"><div><div class="public-catalog-card__media-kicker">Verified local guide</div><div class="public-catalog-card__media-label">{{ $guide->user?->name ?? 'Local guide' }}</div></div></div>
                        <div class="public-catalog-card__body">
                            <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge badge-verified">Verified</span><span class="badge {{ $guide->availability_status === 'available' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($guide->availability_status) }}</span></div>
                            <h2 class="mb-2"><a class="public-catalog-card__title" href="{{ route('tour-guides.show', $guide) }}">{{ $guide->user?->name ?? 'Tour Guide Profile' }}</a></h2>
                            <p class="public-catalog-card__meta mb-2">License {{ $guide->license_number }}</p>
                            <p class="public-catalog-card__description line-clamp-3 mb-3">{{ $guide->expertise }}</p>
                            <div class="d-flex flex-wrap gap-3 public-catalog-card__meta mb-3"><span>Rate: {{ $guide->daily_rate === null ? 'Not configured' : number_format((float) $guide->daily_rate, 2).' ETB/day' }}</span><span>Rating: {{ $guide->average_rating === null ? 'No reviews yet' : number_format((float) $guide->average_rating, 1).' / 5' }}</span></div>
                            <div class="public-catalog-card__footer"><span class="public-catalog-card__meta">Personalized local experience</span><a class="btn btn-outline-success btn-sm" href="{{ route('tour-guides.show', $guide) }}">View profile <span aria-hidden="true">→</span></a></div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12"><x-ui.empty-state title="No guides found" message="Try another expertise search or availability filter. New verified guides will appear here when available." /></div>
            @endforelse
        </div>
    </div>
@endsection
