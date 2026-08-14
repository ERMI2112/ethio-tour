@extends('layouts.app')

@section('title', $tourismService->service_name)

@section('content')
<div class="container py-5">
    <a class="text-decoration-none small" href="{{ route('tourism-services.index') }}">&larr; All tourism services</a>

    <div class="row g-4 mt-1">
        <div class="{{ $tourismService->hotelRoomType ? 'col-lg-7' : 'col-12' }}">
            <article class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 p-md-5">
                    <p class="text-uppercase text-muted small mb-2">
                        <a href="{{ route('categories.index') }}" class="text-decoration-none">{{ $tourismService->category->category_name }}</a>
                        ·
                        <a href="{{ route('destinations.show', $tourismService->destination) }}" class="text-decoration-none">{{ $tourismService->destination->name }}</a>
                    </p>
                    <h1 class="h2">{{ $tourismService->service_name }}</h1>
                    <p class="text-muted">Provided by <strong>{{ $tourismService->serviceProvider->business_name }}</strong></p>
                    <p class="mt-3">{{ $tourismService->description }}</p>

                    <div class="border-top pt-3 mt-4">
                        <span class="fs-4 fw-bold text-primary">{{ number_format($tourismService->price, 2) }} ETB</span>
                        <span class="text-muted small">/ {{ $tourismService->hotelRoomType ? 'night' : 'service' }}</span>
                    </div>

                    @if ($tourismService->hotelRoomType)
                        <div class="mt-4 pt-3 border-top">
                            <h5 class="h6 text-muted text-uppercase">Room Type Information</h5>
                            <div class="d-flex align-items-center gap-3 my-2">
                                <span class="badge bg-light text-dark border">
                                    Capacity: {{ $tourismService->hotelRoomType->capacity }} Guest(s)
                                </span>
                            </div>
                            @if (!empty($tourismService->hotelRoomType->amenities))
                                <div class="mt-3">
                                    <h6 class="small text-muted mb-2">Amenities:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($tourismService->hotelRoomType->amenities as $amenity)
                                            <span class="badge bg-secondary-subtle text-secondary border">{{ $amenity }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </article>
        </div>

        @if ($tourismService->hotelRoomType)
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white p-3">
                        <h2 class="h5 mb-0">Check Availability & Book</h2>
                    </div>
                    <div class="card-body p-4">
                        @include('layouts.partials.flash-messages')

                        <form method="POST" action="{{ route('tourism-services.check-availability', $tourismService) }}" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label for="check_in_date" class="form-label small fw-bold">Check-in Date</label>
                                <input type="date" class="form-control @error('check_in_date') is-invalid @enderror" id="check_in_date" name="check_in_date" value="{{ old('check_in_date', session('check_in_date')) }}" min="{{ date('Y-m-d') }}" required>
                                @error('check_in_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="check_out_date" class="form-label small fw-bold">Check-out Date</label>
                                <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" id="check_out_date" name="check_out_date" value="{{ old('check_out_date', session('check_out_date')) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                @error('check_out_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="guest_count" class="form-label small fw-bold">Number of Guests</label>
                                <input type="number" class="form-control @error('guest_count') is-invalid @enderror" id="guest_count" name="guest_count" value="{{ old('guest_count', session('guest_count', 1)) }}" min="1" max="{{ $tourismService->hotelRoomType->capacity }}" required>
                                @error('guest_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Maximum capacity: {{ $tourismService->hotelRoomType->capacity }} guest(s)</div>
                            </div>

                            <button type="submit" class="btn btn-outline-primary w-100">Check Availability</button>
                        </form>

                        @auth
                            @if (auth()->user()->role === 'tourist')
                                <form method="POST" action="{{ route('tourist.reservations.store', $tourismService) }}">
                                    @csrf
                                    <input type="hidden" name="check_in_date" value="{{ session('check_in_date', old('check_in_date')) }}">
                                    <input type="hidden" name="check_out_date" value="{{ session('check_out_date', old('check_out_date')) }}">
                                    <input type="hidden" name="guest_count" value="{{ session('guest_count', old('guest_count', 1)) }}">

                                    @if (session('available_count'))
                                        <div class="p-3 bg-success-subtle border border-success rounded mb-3">
                                            <p class="small text-success mb-1 fw-bold">Room available!</p>
                                            <p class="small mb-0">Total cost: <strong>{{ number_format((strtotime(session('check_out_date')) - strtotime(session('check_in_date'))) / 86400 * $tourismService->price, 2) }} ETB</strong> for {{ (strtotime(session('check_out_date')) - strtotime(session('check_in_date'))) / 86400 }} night(s).</p>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100 btn-lg">Request Reservation</button>
                                    @else
                                        <p class="small text-muted text-center mb-2">Check availability above to enable reservation request.</p>
                                    @endif
                                </form>
                            @else
                                <div class="alert alert-info small mb-0">
                                    Logged in as {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}. Only tourists can make reservations.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-secondary small mb-0 text-center">
                                Please <a href="{{ route('login') }}" class="alert-link">log in as a tourist</a> to book this room.
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
