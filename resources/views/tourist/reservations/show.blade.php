@extends('layouts.app')

@section('title', 'Reservation Details #BK-' . sprintf('%05d', $booking->booking_id))

@section('content')
<div class="container py-5">
    <a href="{{ route('tourist.reservations.index') }}" class="text-decoration-none small">&larr; Back to My Reservations</a>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Reservation #BK-{{ sprintf('%05d', $booking->booking_id) }}</h1>
            <p class="text-muted small mb-0">Requested on {{ $booking->booking_date ? $booking->booking_date->format('F d, Y at H:i') : $booking->created_at->format('F d, Y') }}</p>
        </div>
        <div>
            @switch($booking->status)
                @case('pending')
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">Pending Review</span>
                    @break
                @case('accepted')
                    <span class="badge bg-info text-dark fs-6 px-3 py-2">Accepted</span>
                    @break
                @case('payment_pending')
                    <span class="badge bg-primary fs-6 px-3 py-2">Payment Pending</span>
                    @break
                @case('confirmed')
                    <span class="badge bg-success fs-6 px-3 py-2">Confirmed</span>
                    @break
                @case('rejected')
                    <span class="badge bg-danger fs-6 px-3 py-2">Rejected</span>
                    @break
                @case('cancelled')
                    <span class="badge bg-secondary fs-6 px-3 py-2">Cancelled</span>
                    @break
                @default
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">{{ ucfirst($booking->status) }}</span>
            @endswitch
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Hotel & Room Details</h2>
                </div>
                <div class="card-body p-4">
                    <h3 class="h4 text-primary">{{ $booking->tourismService->service_name ?? 'N/A' }}</h3>
                    <p class="text-muted mb-3">Provided by <strong>{{ $booking->tourismService->serviceProvider->business_name ?? 'N/A' }}</strong></p>
                    <p class="mb-4">{{ $booking->tourismService->description ?? '' }}</p>

                    @php
                        $res = $booking->hotelRoomReservation;
                        $nights = $res ? max(1, (int) $res->check_in_date->diffInDays($res->check_out_date)) : 1;
                        $nightlyPrice = $booking->tourismService->price ?? 0;
                        $totalCost = $nights * $nightlyPrice;
                    @endphp

                    <div class="row g-3 p-3 bg-light rounded border">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Check-in Date</span>
                            <strong class="fs-6">{{ $res ? $res->check_in_date->format('F d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Check-out Date</span>
                            <strong class="fs-6">{{ $res ? $res->check_out_date->format('F d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Duration</span>
                            <strong>{{ $nights }} night(s)</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Guests</span>
                            <strong>{{ $res->guest_count ?? 1 }} guest(s)</strong>
                        </div>
                        @if ($res && $res->hotelRoom)
                            <div class="col-12 pt-2 border-top">
                                <span class="text-muted small d-block">Allocated Room Number</span>
                                <strong class="text-success fs-6">Room {{ $res->hotelRoom->room_number }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Pricing Summary</h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Nightly Rate</span>
                        <span>{{ number_format($nightlyPrice, 2) }} ETB</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                        <span>Duration</span>
                        <span>{{ $nights }} night(s)</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-primary mb-4">
                        <span>Total Cost</span>
                        <span>{{ number_format($totalCost, 2) }} ETB</span>
                    </div>

                    @if ($booking->status === 'pending')
                        <form method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this reservation request?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger w-100">Cancel Request</button>
                        </form>
                    @elseif ($booking->status === 'payment_pending')
                        <div class="alert alert-info small mb-0">
                            <strong>Acceptance Confirmed!</strong> Payment integration will be enabled in Phase 6E.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
