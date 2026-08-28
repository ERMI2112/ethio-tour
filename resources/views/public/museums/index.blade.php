@extends('layouts.app')

@section('title', 'Museums')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Collections and living history</p>
            <h1 class="display-6 fw-bold mb-2">Museums</h1>
            <p class="lead text-secondary mb-0">Discover collections, artifacts, and cultural history from across Ethiopia.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" action="{{ route('museums.index') }}" role="search" data-aos="fade-up" data-aos-delay="80"><div class="card-body p-3 p-lg-4"><div class="row g-3 align-items-end"><div class="col-md-9"><label class="form-label" for="museum-search">Search museums</label><input id="museum-search" name="q" value="{{ $search }}" class="form-control" placeholder="Search name or location"></div><div class="col-md-3"><button class="btn btn-success w-100" type="submit">Search</button></div></div></div></form>

        @if ($museums->isEmpty())
            <x-ui.empty-state title="No museums found" message="Try a different search or check back soon for new museum information." />
        @else
            <div class="row g-4">
                @foreach ($museums as $museum)
                    <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                        <article class="public-catalog-card h-100"><div class="public-catalog-card__media public-catalog-card__media--museum"><div><div class="public-catalog-card__media-kicker">Museum collection</div><div class="public-catalog-card__media-label">{{ $museum->location ?: 'Ethiopia' }}</div></div></div><div class="public-catalog-card__body"><span class="badge badge-verified align-self-start mb-2">Cultural information</span><h2 class="mb-2"><a class="public-catalog-card__title" href="{{ route('museums.show', $museum) }}">{{ $museum->museum_name }}</a></h2><p class="public-catalog-card__description line-clamp-3 mb-3">{{ $museum->description }}</p><div class="public-catalog-card__meta mb-3"><div><strong>Hours:</strong> {{ $museum->opening_hours }}</div>@if ($museum->entrance_fee !== null)<div><strong>Entrance:</strong> {{ $museum->entrance_fee }} ETB <span class="text-muted small">(Paid at site)</span></div>@endif</div><div class="public-catalog-card__footer"><span class="public-catalog-card__meta">Plan a cultural visit</span><a class="btn btn-outline-success btn-sm" href="{{ route('museums.show', $museum) }}">View museum <span aria-hidden="true">→</span></a></div></div></article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
