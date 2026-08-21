@extends('layouts.app')

@section('title', 'Room-Type Services')

@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Room-Type Services</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Room-type services</h1>
            <p class="text-muted mb-0">Each entry is a published room type with a nightly price. Add physical rooms under each type.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('hotel.services.create') }}">Add room type</a>
    </div>

    @if($services->isEmpty())
        <x-ui.empty-state title="No room-type services yet" message="Add your first Standard, Deluxe, or Suite room type to start building inventory." />
    @else
        <div class="row g-3">
            @foreach($services as $service)
                @php
                    $roomType = $service->hotelRoomType;
                    $roomCount = $roomType?->hotelRooms->count() ?? 0;
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <h2 class="h5 mb-1">{{ $service->service_name }}</h2>
                                    <div class="small text-muted">
                                        {{ $service->category->category_name }}
                                        &middot;
                                        {{ $service->destination->name }}
                                    </div>
                                </div>
                                <span class="fs-5 fw-bold text-primary text-nowrap">{{ number_format($service->price, 2) }} <span class="small">ETB</span></span>
                            </div>

                            <p class="small text-muted mt-3 mb-2">{{ Str::limit($service->description, 120) }}</p>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-light text-dark border">Capacity: {{ $roomType?->capacity ?? '—' }} guest(s)</span>
                                <span class="badge bg-light text-dark border">{{ $roomCount }} physical room(s)</span>
                            </div>

                            @if (!empty($roomType?->amenities))
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach($roomType->amenities as $amenity)
                                        <span class="badge bg-secondary-subtle text-secondary border">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hotel.services.edit', $service) }}">Edit</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('hotel.rooms.index') }}">Rooms</a>
                                <form class="d-inline" method="POST" action="{{ route('hotel.services.destroy', $service) }}" data-confirm="Remove this room-type service? Its physical rooms and booking history, if any, cannot be removed.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
