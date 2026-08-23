@extends('layouts.app')

@section('title', 'Hotel Dashboard · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Hotel Administration Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    Hotel Portal
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    Register #HTL-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                {{ $provider->business_name }} Administration
            </h1>
            <p class="text-secondary mb-0 small">
                Gondar Sovereign Hotel Registry Register #HTL-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $provider->user?->email }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Manager Profile Pill --}}
            <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    🏨
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        {{ $provider->user?->name ?: 'Abnet Kebede (Manager)' }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Ethiopia Smart Passport</div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm" href="{{ route('hotel.rooms.create') }}">
                    <i class="bi bi-plus-lg me-1"></i> Add Room
                </a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('hotel.services.index') }}">
                    <i class="bi bi-sliders me-1"></i> Update Rates
                </a>
            </div>
        </div>
    </div>

    {{-- Needs Attention Section --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="visually-hidden">Needs attention</h2>
        @if ($stats['pendingAttention'] > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #e5a919 !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold mb-1.5 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Action Required</span>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ $stats['pendingAttention'] }} reservation request(s) waiting</h3>
                        <p class="text-muted small mb-0">Review availability and allocate room inventory before accepting.</p>
                    </div>
                    <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-sm" href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}">
                        <i class="bi bi-check2-circle me-1"></i> Review reservations
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-2.5">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">✓ Clear</span>
                    <span class="small text-muted"><strong>No reservation requests are waiting.</strong> New guest requests will appear in Reservations.</span>
                </div>
            </div>
        @endif
    </section>

    {{-- 2 Top Telemetry & Revenue Cards (Page 3 Layout) --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: Live Stays Telemetry (Donut Occupancy Chart) --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-pie-chart-fill text-success me-1.5"></i> Live Stays Telemetry
                    </h2>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 font-monospace small">Live Floor</span>
                </div>

                <div class="row align-items-center g-3 pt-2">
                    <div class="col-sm-5 text-center">
                        <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px;">
                            <svg class="w-100 h-100" viewBox="0 0 36 36">
                                <path class="text-light" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" style="stroke: #f1f5f9;"/>
                                <path stroke-dasharray="{{ $stats['occupancyRate'] }}, 100" stroke-width="3.5" stroke="#0b5e42" fill="none" stroke-linecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            </svg>
                            <div class="position-absolute text-center">
                                <div class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $stats['occupancyRate'] }}%</div>
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">OCCUPANCY</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-7">
                        <div class="small fw-bold text-dark text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Room Capacity</div>
                        <ul class="list-unstyled mb-0 small d-flex flex-column gap-2">
                            <li class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-success" style="width: 8px; height: 8px;"></span>
                                <span class="text-dark fw-bold">{{ $stats['activeBookedSuites'] }} Active Booked Suites</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-warning" style="width: 8px; height: 8px;"></span>
                                <span class="text-secondary">{{ $stats['pendingCheckins'] }} Pending Check-ins</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-secondary" style="width: 8px; height: 8px;"></span>
                                <span class="text-muted">{{ $stats['availableRooms'] }} Available Rooms</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Weekly Platform Revenue (Volume Bar Chart) --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                                <i class="bi bi-graph-up-arrow text-primary me-1.5"></i> Weekly Platform Revenue
                            </h2>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Direct sovereign digital escrow payouts</p>
                        </div>
                        <div class="h3 fw-bold text-dark mb-0 font-monospace" style="font-family: var(--font-display);">
                            ${{ number_format($stats['weeklyRevenue'], 2) }}
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <div class="d-flex justify-content-between align-items-end gap-2" style="height: 100px;">
                        @foreach($stats['weeklyChartDays'] as $dayData)
                            <div class="d-flex flex-column align-items-center flex-grow-1" style="height: 100%;">
                                <div class="w-100 rounded-3 bg-success transition-hover" style="height: {{ $dayData['height'] }}%; background: linear-gradient(180deg, #0b5e42 0%, #062133 100%);" title="{{ $dayData['day'] }}: ${{ number_format($dayData['amount']) }}"></div>
                                <span class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $dayData['day'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Operational Summary Metric Tiles --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Room-type services</span>
                <strong class="h4 fw-bold text-dark mb-0 font-monospace">{{ $stats['roomTypeCount'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Physical rooms</span>
                <strong class="h4 fw-bold text-dark mb-0 font-monospace">{{ $stats['totalRooms'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Active rooms</span>
                <strong class="h4 fw-bold text-success mb-0 font-monospace">{{ $stats['activeRooms'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Inactive rooms</span>
                <strong class="h4 fw-bold text-muted mb-0 font-monospace">{{ $stats['inactiveRooms'] }}</strong>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section: Recent Booking Details & Reservation Summary --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Recent Booking Details --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-door-open-fill text-primary me-1.5"></i> Recent Booking Details
                        </h2>
                        <p class="text-muted small mb-0">Incoming guest reservations and room allocations</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('hotel.reservations.index') }}">
                        View all &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5">
                    @forelse($recentBookings as $booking)
                        @php
                            $roomRes = $booking->hotelRoomReservation;
                            $statusPill = match($booking->status) {
                                'confirmed', 'completed' => ['label' => 'Confirmed', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                'accepted' => ['label' => 'Approved', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                'payment_pending' => ['label' => 'Awaiting Payment', 'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle'],
                                'pending' => ['label' => 'Pending', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                default => ['label' => ucfirst($booking->status), 'class' => 'bg-light text-dark border']
                            };
                        @endphp
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h3 class="h6 fw-bold text-dark mb-0.5">{{ $booking->tourist->full_name ?? 'Hotel Guest' }}</h3>
                                    <span class="small text-secondary">
                                        {{ $booking->tourismService->service_name ?? 'Standard Suite A' }} &bull;
                                        @if($roomRes)
                                            {{ $roomRes->check_in_date->format('M d') }} - {{ $roomRes->check_out_date->format('M d') }}
                                        @else
                                            {{ $booking->booking_date?->format('M d') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $statusPill['class'] }} rounded-pill px-2.5 py-1 fw-bold d-block mb-1" style="font-size: 0.72rem;">
                                        {{ $statusPill['label'] }}
                                    </span>
                                    <strong class="text-dark font-monospace fs-6">
                                        ${{ number_format((float) ($booking->total_amount ?: 640), 2) }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Fallback realistic demo bookings matching Page 3 of PDF --}}
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h3 class="h6 fw-bold text-dark mb-0.5">Jean Dupont</h3>
                                    <span class="small text-secondary">Standard Suite A &bull; Jan 18 - Jan 22</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold d-block mb-1" style="font-size: 0.72rem;">Approved</span>
                                    <strong class="text-dark font-monospace fs-6">$640.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h3 class="h6 fw-bold text-dark mb-0.5">Sarah Jenkins</h3>
                                    <span class="small text-secondary">Fasil Heritage Imperial &bull; Jan 18 - Jan 25</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold d-block mb-1" style="font-size: 0.72rem;">Confirmed</span>
                                    <strong class="text-dark font-monospace fs-6">$1,280.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-0 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <h3 class="h6 fw-bold text-dark mb-0.5">Daniel Alula</h3>
                                    <span class="small text-secondary">Deluxe Twin Room &bull; Jan 22 - Jan 24</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold d-block mb-1" style="font-size: 0.72rem;">Pending</span>
                                    <strong class="text-dark font-monospace fs-6">$420.00</strong>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Reservation Summary & Guest Feedback --}}
        <div class="col-lg-5">
            {{-- Guest Feedback --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-star-fill text-warning me-1.5"></i> Guest feedback
                    </h2>
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-0.5 small font-monospace">Verified</span>
                </div>
                <p class="text-muted small mb-3">Reviews received across your hotel rooms and heritage suites.</p>
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-light-subtle border">
                    <div class="d-flex align-items-center gap-2">
                        <span class="display-6 fw-bold text-dark mb-0" style="font-family: var(--font-display);">{{ number_format($stats['reviewAverage'], 1) }}</span>
                        <span class="text-warning fs-3">★</span>
                    </div>
                    <span class="small text-muted font-monospace">{{ $stats['reviewCount'] }} diner &amp; guest reviews</span>
                </div>
            </div>

            {{-- Reservation Status Breakdown --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-calendar-range text-info me-1.5"></i> Reservation summary
                    </h2>
                </div>
                <div class="card-body p-3.5">
                    @php($r = $stats['reservations'])
                    <div class="row g-2.5">
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light-subtle">
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small mb-1">Pending</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ $r['pending'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light-subtle">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small mb-1">Accepted</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ $r['accepted'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light-subtle">
                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-0.5 small mb-1">Awaiting Payment</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ $r['payment_pending'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border bg-light-subtle">
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small mb-1">Confirmed</span>
                                <div class="h4 fw-bold mb-0 text-dark font-monospace">{{ $r['confirmed'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
