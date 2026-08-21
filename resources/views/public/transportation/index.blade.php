@extends('layouts.app')

@section('title', 'Transportation & Car Rental')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Move around with confidence</p>
            <h1 class="display-6 fw-bold mb-2">Transportation &amp; Car Rental</h1>
            <p class="lead text-secondary mb-0">Compare operational vehicles and rental services for city transfers, day trips, and longer journeys.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" action="{{ route('transportation.index') }}" data-aos="fade-up" data-aos-delay="80">
            <div class="card-body p-3 p-lg-4"><div class="row g-3 align-items-end"><div class="col-md-9"><label class="form-label" for="transport-search">Search services</label><input id="transport-search" name="q" value="{{ $search }}" class="form-control" placeholder="Search vehicle or service"></div><div class="col-md-3"><button class="btn btn-success w-100" type="submit">Search</button></div></div></div>
        </form>

        @if ($services->isEmpty())
            <x-ui.empty-state title="No transportation services found" message="Try another search or check back as new rental services become available." />
        @else
            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                        <article class="public-catalog-card h-100 overflow-hidden">
                            <div class="public-catalog-card__media position-relative" style="height: 180px; overflow: hidden; background: #0d3824;">
                                <img src="{{ asset('images/services/safari-4x4.jpg') }}" alt="{{ $service->service_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);"></div>
                                <div class="position-absolute bottom-0 start-0 m-3 text-white">
                                    <div class="public-catalog-card__media-kicker text-warning">Car rental &amp; transport</div>
                                    <div class="public-catalog-card__media-label fw-bold text-white">{{ $service->destination?->name ?? 'Ethiopia' }}</div>
                                </div>
                            </div>
                            <div class="public-catalog-card__body">
                                <span class="badge badge-verified align-self-start mb-2">Verified service</span>
                                <h2 class="mb-2"><a class="public-catalog-card__title text-dark text-decoration-none" href="{{ route('transportation.show', $service) }}">{{ $service->service_name }}</a></h2>
                                <p class="public-catalog-card__meta mb-2">{{ $service->serviceProvider?->business_name ?? 'Local provider' }}</p>
                                <p class="public-catalog-card__description line-clamp-3 mb-3 text-secondary">{{ $service->description }}</p>
                                <div class="public-catalog-card__footer mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-success fs-6">{{ number_format((float) $service->price, 2) }} ETB/day</span>
                                    <a class="btn btn-outline-success btn-sm" href="{{ route('transportation.show', $service) }}">View vehicles <span aria-hidden="true">→</span></a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
