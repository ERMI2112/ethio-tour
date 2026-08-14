@extends('layouts.app')

@section('title', 'Guide Availability')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">@include('tour-guide.partials.sidebar')</div>
        <div class="col-lg-9">
            <nav aria-label="breadcrumb"><ol class="breadcrumb small"><li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Availability</li></ol></nav>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="text-muted small text-uppercase mb-1">Tour Guide Portal</p><h1 class="h2 mb-0">Availability</h1></div><span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }} fs-6">{{ ucfirst($guide->availability_status) }}</span></div>

            <div class="alert alert-info small">Reserved periods are derived from accepted, payment-pending, and confirmed guide bookings. Pending requests are shown separately and do not block your calendar.</div>

            <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Reserved periods</h2></div><div class="card-body p-0">
                @forelse ($blockingReservations as $reservation)
                    <div class="d-flex flex-wrap justify-content-between gap-2 p-3 border-bottom"><div><p class="mb-1 fw-semibold">{{ $reservation->start_date->format('M j, Y') }} – {{ $reservation->end_date->format('M j, Y') }}</p><p class="mb-0 text-muted small">{{ $reservation->number_of_tourists }} tourist(s) · {{ $reservation->booking->tourist?->full_name }}</p></div><x-ui.status-badge :status="$reservation->booking->status" /></div>
                @empty
                    <p class="text-muted p-4 mb-0">No confirmed or accepted periods are currently blocking your availability.</p>
                @endforelse
            </div></div>

            <div class="card border-0 shadow-sm"><div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"><h2 class="h5 mb-0">Pending requests</h2><a class="small text-decoration-none" href="{{ route('tour-guide.requests.index', ['status' => 'pending']) }}">View all requests</a></div><div class="card-body p-0">
                @forelse ($pendingRequests as $booking)
                    <div class="d-flex flex-wrap justify-content-between gap-2 p-3 border-bottom"><div><p class="mb-1 fw-semibold">{{ $booking->tourGuideReservation->start_date->format('M j, Y') }} – {{ $booking->tourGuideReservation->end_date->format('M j, Y') }}</p><p class="mb-0 text-muted small">{{ $booking->tourGuideReservation->number_of_tourists }} tourist(s) · {{ $booking->tourist?->full_name }}</p></div><x-ui.status-badge :status="$booking->status" /></div>
                @empty
                    <p class="text-muted p-4 mb-0">No pending requests.</p>
                @endforelse
            </div></div>
        </div>
    </div>
</div>
@endsection
