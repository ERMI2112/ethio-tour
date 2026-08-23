@extends('layouts.app')

@section('title', 'Transportation Dashboard · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Transportation Fleet Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    Transportation Portal
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    License #TRN-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Selam, Transport Office!
            </h1>
            <p class="text-secondary mb-0 small">
                {{ $provider->business_name }} &bull; Fasil Co-op Fleet Admin License #TRN-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Fleet Manager Profile Pill --}}
            <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    🚐
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        {{ $provider->user?->name ?: 'Sarah Jenkins' }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Ethiopia Smart Passport</div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm" href="{{ route('transportation.vehicles.create') }}">
                    <i class="bi bi-plus-lg me-1"></i> Add Vehicle
                </a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('transportation.profile') }}">
                    <i class="bi bi-gear me-1"></i> View profile
                </a>
            </div>
        </div>
    </div>

    {{-- Needs Attention Section --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="visually-hidden">Needs attention</h2>
        @if($stats['pendingReservations'] > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #e5a919 !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold mb-1.5 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Action Required</span>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ $stats['pendingReservations'] }} rental request(s) waiting</h3>
                        <p class="text-muted small mb-0">Review dates, route specifics, and vehicle availability before deciding.</p>
                    </div>
                    <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-sm" href="{{ route('transportation.reservations.index', ['status'=>'pending']) }}">
                        <i class="bi bi-inbox me-1"></i> Review reservations
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-2.5">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">✓ Active</span>
                    <span class="small text-muted"><strong>No rental requests are waiting.</strong> New requests will appear in Reservations.</span>
                </div>
            </div>
        @endif
    </section>

    {{-- 4-Card Fleet Metric Matrix (Page 5 Layout) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Active Fleet Count</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        {{ $stats['activeVehicles'] }} / {{ $stats['vehicleCount'] }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">87% Available</span>
                    <span class="small text-muted">6 in maintenance</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Scheduled Trips Today</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        {{ $stats['scheduledTripsToday'] }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small">On Track</span>
                    <span class="small text-muted">Zero delays</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Drivers Online</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        {{ $stats['driversOnline'] }} Active
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Fully Staffed</span>
                    <span class="small text-muted">4 on stand-by</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Escrow Secured Revenue</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        ${{ number_format($stats['escrowRevenue'], 2) }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small">Pending check-in</span>
                    <span class="small text-muted">Locked in vault</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section: Fleet Management & Compliance / Advisories --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Active Fleet Management & Escrow Supervised Requests --}}
        <div class="col-lg-7">
            {{-- Active Fleet Management --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-truck text-success me-1.5"></i> Active Fleet Management
                        </h2>
                        <p class="text-muted small mb-0">Live vehicle statuses and driver assignments</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('transportation.vehicles.index') }}">
                        Vehicles &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5">
                    @forelse($vehicles as $vehicle)
                        @php
                            $statusPill = match($vehicle->status) {
                                'active' => ['label' => 'Available', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-danger-subtle text-danger border border-danger-subtle'],
                                default => ['label' => 'On Trip', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle']
                            };
                        @endphp
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white border rounded-3 p-2 text-center" style="min-width: 44px;">
                                        <i class="bi bi-car-front fs-5 text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border font-monospace">{{ $vehicle->license_plate ?: 'ETH-902-GDR' }}</span>
                                            <strong class="text-dark">{{ $vehicle->make_model ?: 'Toyota Land Cruiser' }}</strong>
                                        </div>
                                        <span class="small text-muted">Driver: <strong>Ato Belay Tekle</strong></span>
                                    </div>
                                </div>
                                <span class="badge {{ $statusPill['class'] }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                    {{ $statusPill['label'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        {{-- Fallback realistic demo vehicles matching Page 5 of PDF --}}
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white border rounded-3 p-2 text-center" style="min-width: 44px;">
                                        <i class="bi bi-car-front fs-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border font-monospace">ETH-902-GDR</span>
                                            <strong class="text-dark">Toyota Land Cruiser</strong>
                                        </div>
                                        <span class="small text-muted">Driver: <strong>Ato Belay Tekle</strong></span>
                                    </div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">On Trip</span>
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white border rounded-3 p-2 text-center" style="min-width: 44px;">
                                        <i class="bi bi-bus-front fs-5 text-success"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border font-monospace">ETH-441-GDR</span>
                                            <strong class="text-dark">Coaster Luxury Bus</strong>
                                        </div>
                                        <span class="small text-muted">Driver: <strong>W/ro Tigist</strong></span>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">Available</span>
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-0 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white border rounded-3 p-2 text-center" style="min-width: 44px;">
                                        <i class="bi bi-tools fs-5 text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border font-monospace">ETH-112-LAL</span>
                                            <strong class="text-dark">HiAce High Roof Van</strong>
                                        </div>
                                        <span class="small text-muted">Driver: <strong>Workshop</strong></span>
                                    </div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">Maintenance</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Escrow Supervised Booking Requests --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-shield-lock-fill text-warning me-1.5"></i> Escrow Supervised Booking Requests
                        </h2>
                        <p class="text-muted small mb-0">Incoming tourist transfer and rental bookings</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('transportation.reservations.index') }}">
                        View all &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5">
                    @forelse($recentRequests as $booking)
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-2">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">
                                    {{ $booking->tourismService->service_name ?? 'Airport Transfer Route' }}
                                </h3>
                                <span class="small text-muted font-monospace">ID: TX-{{ sprintf('%03d', $booking->booking_id) }} &bull; {{ $booking->tourist->full_name ?? '3 Adults' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">Escrow Locked</span>
                                <a class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" href="{{ route('transportation.reservations.show', $booking) }}">Assign Driver</a>
                            </div>
                        </div>
                    @empty
                        {{-- Fallback realistic demo requests matching Page 5 of PDF --}}
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-2">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Gondar Airport &rarr; Fasil Ghebbi</h3>
                                <span class="small text-muted font-monospace">ID: TX-990 &bull; 3 Adults</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">Escrow Locked</span>
                                <button class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">Assign Driver</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-0">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Lalibela Town &rarr; Simien National Park</h3>
                                <span class="small text-muted font-monospace">ID: TX-992 &bull; 2 Adults, 1 Child</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Escrow Released</span>
                                <button class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">Assign Driver</button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Driver Compliance & Route Weather Advisories --}}
        <div class="col-lg-5">
            {{-- Driver Compliance Index --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%); color: #fff;">
                <div class="p-4">
                    <h2 class="h6 fw-bold text-white mb-1" style="font-family: var(--font-display);">
                        Driver Compliance Index
                    </h2>
                    <p class="text-white-50 small mb-3">Government certified security credentials required.</p>

                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2.5 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20">
                            <div>
                                <strong class="text-white small d-block">Kassa Abebe</strong>
                                <span class="text-white-50 small font-monospace">GDR-HIST-11</span>
                            </div>
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-0.5">★ 4.9</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2.5 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20">
                            <div>
                                <strong class="text-white small d-block">Yared Kidane</strong>
                                <span class="text-white-50 small font-monospace">GDR-TRST-32</span>
                            </div>
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-0.5">★ 5.0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Route Advisories & Weather Card --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-geo-alt-fill text-danger me-1.5"></i> Route Advisories
                    </h2>
                    <span class="text-warning fs-5">☀️</span>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="display-5 fw-bold text-dark mb-1" style="font-family: var(--font-display);">
                        24°C
                    </div>
                    <p class="small text-muted mb-0">
                        Sunny skies over Northern Route. Road to Simien Mountains clear and verified secure.
                    </p>
                </div>
            </div>

            {{-- Fleet Overview Summary Card --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h2 class="h6 fw-bold text-dark mb-1" style="font-family: var(--font-display);">
                    Fleet overview
                </h2>
                <p class="text-muted small mb-3">
                    Active and inactive vehicles are date-specific inventory. Availability is checked when a rental request is accepted.
                </p>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a class="small text-muted text-decoration-none" href="{{ route('provider.reports') }}">Financial reports</a>
                    <a class="small text-muted text-decoration-none" href="{{ route('notifications.index') }}">Notifications</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
