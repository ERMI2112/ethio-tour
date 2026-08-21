@extends('layouts.app')

@section('title', 'Tour Guide Earnings & Financial Overview')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Earnings</li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <p class="text-muted small text-uppercase mb-1">Financial Overview</p>
                    <h1 class="h2 mb-0 fw-bold">Earnings &amp; Payouts</h1>
                </div>
                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                    Daily Rate: {{ $guide->daily_rate ? number_format((float) $guide->daily_rate, 2) . ' ETB' : 'Not set' }}
                </div>
            </div>

            {{-- Metric Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <span class="text-muted small mb-1">Lifetime Revenue</span>
                        <div class="h3 fw-bold text-success mb-0">{{ number_format((float) $lifetimeEarnings, 2) }}</div>
                        <span class="small text-muted">ETB completed</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <span class="text-muted small mb-1">Pending / Upcoming</span>
                        <div class="h3 fw-bold text-primary mb-0">{{ number_format((float) $pendingEarnings, 2) }}</div>
                        <span class="small text-muted">ETB reserved</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <span class="text-muted small mb-1">Completed Tours</span>
                        <div class="h3 fw-bold text-dark mb-0">{{ $completedCount }}</div>
                        <span class="small text-muted">Successful excursions</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-white">
                        <span class="text-muted small mb-1">Average / Tour</span>
                        <div class="h3 fw-bold text-dark mb-0">{{ number_format((float) $averagePerTour, 2) }}</div>
                        <span class="small text-muted">ETB average booking</span>
                    </div>
                </div>
            </div>

            {{-- Completed Bookings Ledger --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold">Completed Tours Ledger</h2>
                    <span class="badge bg-light text-dark border">{{ $completedBookings->count() }} records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Tourist</th>
                                <th>Tour Date(s)</th>
                                <th>Amount (ETB)</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedBookings as $booking)
                                <tr>
                                    <td><span class="fw-semibold">#{{ $booking->booking_id }}</span></td>
                                    <td>{{ $booking->tourist?->full_name ?? 'Tourist' }}</td>
                                    <td>
                                        @if($booking->tourGuideReservation)
                                            {{ $booking->tourGuideReservation->start_date?->format('M j, Y') }} &ndash; {{ $booking->tourGuideReservation->end_date?->format('M j, Y') }}
                                        @else
                                            {{ $booking->booking_date?->format('M j, Y') }}
                                        @endif
                                    </td>
                                    <td><span class="fw-bold text-success">{{ number_format((float) $booking->total_amount, 2) }} ETB</span></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Paid &amp; Settled
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('tour-guide.requests.show', $booking) }}">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No completed tours yet. Completed bookings will be logged here with revenue settlement records.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pending / Upcoming Payouts Ledger --}}
            @if($pendingBookings->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0 fw-bold text-dark">Upcoming Reserved Tours</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Tourist</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingBookings as $booking)
                                    <tr>
                                        <td><span class="fw-semibold">#{{ $booking->booking_id }}</span></td>
                                        <td>{{ $booking->tourist?->full_name ?? 'Tourist' }}</td>
                                        <td><span class="badge bg-warning-subtle text-dark">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                                        <td><span class="fw-bold">{{ number_format((float) $booking->total_amount, 2) }} ETB</span></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('tour-guide.requests.show', $booking) }}">
                                                View Request
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
