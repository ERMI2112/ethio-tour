@extends('layouts.app')

@section('title', $service->service_name)

@section('content')
<div class="container py-4">
    <a class="link-secondary d-inline-block mb-3" href="{{ route('transportation.index') }}">&larr; Back to transportation</a>
    <div class="row g-4">
        <div class="col-lg-7" data-aos="fade-up">
            <div class="public-catalog-card overflow-hidden">
                <div class="public-catalog-card__media position-relative" style="height: 240px; overflow: hidden; background: #0d3824;">
                    <img src="{{ asset('images/services/safari-4x4.jpg') }}" alt="{{ $service->service_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);"></div>
                    <div class="position-absolute bottom-0 start-0 m-4 text-white">
                        <div class="public-catalog-card__media-kicker text-warning">Transportation &amp; Car Rental</div>
                        <div class="public-catalog-card__media-label fw-bold text-white fs-5">📍 {{ $service->destination->name }}</div>
                    </div>
                </div>
                <div class="public-catalog-card__body p-4 p-md-5">
                    <span class="badge badge-verified align-self-start mb-2">Verified service</span>
                    <h1 class="h2 mb-2 fw-bold">{{ $service->service_name }}</h1>
                    <p class="public-catalog-card__meta mb-3 text-muted">Provided by <strong>{{ $service->serviceProvider->business_name }}</strong></p>
                    <p class="public-catalog-card__description leading-relaxed mb-4 text-secondary">{{ $service->description }}</p>
                    <div class="alert alert-light border mb-0">Daily rental rate: <strong class="text-success fs-5">{{ number_format($service->price, 2) }} ETB</strong>. Your final booking amount is calculated from the requested rental duration and is payable after acceptance.</div>
                </div>
            </div>
            <div class="public-catalog-card mt-4" data-aos="fade-up" data-aos-delay="80">
                <div class="card-header bg-transparent border-0 pt-4 px-4"><h2 class="h5 mb-0 fw-bold">Available vehicle inventory</h2></div>
                <div class="card-body px-4">
                    @forelse ($service->transportationVehicles as $vehicle)
                        <div class="border-bottom py-2">
                            <strong>{{ $vehicle->vehicle_identifier }}</strong> · {{ $vehicle->vehicle_type }} · capacity {{ $vehicle->capacity }}
                            @if($vehicle->make || $vehicle->model) · {{ trim($vehicle->make.' '.$vehicle->model) }}@endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No active vehicles are currently published.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">Request transportation</h2>
                    <p class="small text-muted mb-3">Enter your route and rental window. A provider will review the request before payment.</p>
                    <form method="POST" action="{{ route('tourist.transportation-reservations.store', $service) }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Pickup location</label><input name="pickup_location" value="{{ old('pickup_location') }}" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Drop-off location</label><input name="dropoff_location" value="{{ old('dropoff_location') }}" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Pickup</label><input type="datetime-local" name="pickup_at" value="{{ old('pickup_at') }}" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Drop-off</label><input type="datetime-local" name="dropoff_at" value="{{ old('dropoff_at') }}" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Passengers</label><input type="number" name="passenger_count" min="1" value="{{ old('passenger_count', 1) }}" class="form-control" required></div>
                        @guest
                            <p class="small text-muted">Please log in as a tourist to submit a request.</p>
                        @endguest
                        <button class="btn btn-success fw-bold w-100" @guest disabled @endguest>Submit request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
