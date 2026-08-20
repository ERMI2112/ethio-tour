@extends('layouts.app')

@section('title', $providerType === 'hotel' ? 'Hotels' : ($providerType === 'restaurant' ? 'Restaurants' : 'Tourism Services'))

@section('content')
    @php($pageTitle = $providerType === 'hotel' ? 'Hotels' : ($providerType === 'restaurant' ? 'Restaurants' : 'Tourism Services'))
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Discover and compare</p>
            <h1 class="display-6 fw-bold mb-2">{{ $pageTitle }}</h1>
            <p class="lead text-secondary mb-0">Explore real local services by destination, category, and provider. Open a listing to see its details and available booking options.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" action="{{ route('tourism-services.index') }}" data-aos="fade-up" data-aos-delay="80">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label" for="service-search">Search</label><input class="form-control" id="service-search" name="q" value="{{ $search }}" placeholder="Service or provider"></div>
                    <div class="col-md-3"><label class="form-label" for="provider_type">Service type</label><select class="form-select" id="provider_type" name="provider_type"><option value="">All public services</option><option value="hotel" @selected($providerType === 'hotel')>Hotels</option><option value="restaurant" @selected($providerType === 'restaurant')>Restaurants</option></select></div>
                    <div class="col-md-2"><label class="form-label" for="category">Category</label><select class="form-select" id="category" name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->category_id }}" @selected($categoryId === $category->category_id)>{{ $category->category_name }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label class="form-label" for="destination">Destination</label><select class="form-select" id="destination" name="destination"><option value="">All destinations</option>@foreach($destinations as $destination)<option value="{{ $destination->destination_id }}" @selected($destinationId === $destination->destination_id)>{{ $destination->name }}</option>@endforeach</select></div>
                    <div class="col-md-2 d-flex gap-2"><x-ui.button class="flex-grow-1" type="submit">Search</x-ui.button><a class="btn btn-outline-secondary" href="{{ route('tourism-services.index') }}">Clear</a></div>
                </div>
            </div>
        </form>

        @if($services->isEmpty())
            <x-ui.empty-state title="No tourism services found" message="Try a different search or filter. New public listings will appear here as they become available." />
        @else
            <div class="row g-4">
                @foreach($services as $service)
                    @php($serviceType = str($service->serviceProvider?->provider_type ?? 'service')->replace('_', ' ')->title())
                    <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                        <article class="public-catalog-card h-100">
                            <div class="public-catalog-card__media public-catalog-card__media--service">
                                <div><div class="public-catalog-card__media-kicker">{{ $serviceType }}</div><div class="public-catalog-card__media-label">{{ $service->destination?->name ?? 'Ethiopia' }}</div></div>
                            </div>
                            <div class="public-catalog-card__body">
                                <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge badge-verified">Verified service</span><span class="public-catalog-card__meta">{{ $service->category?->category_name }}</span></div>
                                <h2 class="mb-2"><a class="public-catalog-card__title" href="{{ route('tourism-services.show', $service) }}">{{ $service->service_name }}</a></h2>
                                <p class="public-catalog-card__meta mb-2">{{ $service->serviceProvider?->business_name ?? 'Local provider' }}</p>
                                <p class="public-catalog-card__description line-clamp-3 mb-3">{{ \Illuminate\Support\Str::limit($service->description, 150) }}</p>
                                <div class="public-catalog-card__footer"><span class="small fw-semibold text-success">{{ number_format((float) $service->price, 2) }} ETB</span><a class="btn btn-outline-success btn-sm" href="{{ route('tourism-services.show', $service) }}">View details <span aria-hidden="true">→</span></a></div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
