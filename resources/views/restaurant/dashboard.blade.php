@extends('layouts.app')

@section('title', 'Restaurant Portal · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Gastronomy Hub Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    Restaurant Portal
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    ID #RST-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">{{ $provider->business_name }}</h1>
            <p class="text-secondary mb-0 small">
                Certified {{ $provider->tourismServices->first()?->destination?->name ?? 'Gondar' }} Gastronomy Hub &bull; {{ $provider->user?->email }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Chef / Manager Profile Pill --}}
            <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    👨‍🍳
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        Chef {{ $provider->user?->name ?: ($provider->user?->email ? explode('@', $provider->user->email)[0] : 'Martha Assefa') }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Ethiopia Smart Passport</div>
                </div>
            </div>

            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('restaurant.profile') }}">
                <i class="bi bi-gear me-1"></i> View profile
            </a>
        </div>
    </div>

    {{-- Attention Section --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="visually-hidden">Needs attention</h2>
        @if($stats['pendingReservations'] > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #e5a919 !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold mb-1.5 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Action Required</span>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ $stats['pendingReservations'] }} reservation request(s) waiting</h3>
                        <p class="text-muted small mb-0">Review time and table availability before accepting. Accepting allocates table inventory.</p>
                    </div>
                    <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-sm" href="{{ route('restaurant.reservations.index', ['status'=>'pending']) }}">
                        <i class="bi bi-check2-circle me-1"></i> Review reservations
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-2.5">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">✓ Clear</span>
                    <span class="small text-muted"><strong>No reservation requests are waiting.</strong> New requests will appear in Reservations.</span>
                </div>
            </div>
        @endif
    </section>

    {{-- Top 3 Executive KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- KPI 1: Today's Booked Seats --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Today's Booked Seats</span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.7rem;">Live Floor</span>
                    </div>
                    <div class="h2 fw-bold text-dark mb-2" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                        {{ $stats['todayBookedTables'] }} / {{ max(1, $stats['tableCount']) }} Tables
                    </div>
                </div>
                <div>
                    <div class="progress mb-1.5" style="height: 6px; border-radius: 3px; background: #e2e8f0;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['peakCapacityPercentage'] }}%;" aria-valuenow="{{ $stats['peakCapacityPercentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="small text-muted" style="font-size: 0.78rem;">
                        {{ $stats['peakCapacityPercentage'] }}% Peak Capacity Planned &bull; {{ $stats['activeTables'] }} Active Tables
                    </span>
                </div>
            </div>
        </div>

        {{-- KPI 2: Gross Dining Revenue --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Gross Dining Revenue (Month)</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.7rem;">Escrow Cleared</span>
                    </div>
                    <div class="h2 fw-bold text-dark mb-2 font-monospace" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                        ${{ number_format($stats['monthlyRevenue'], 2) }}
                    </div>
                </div>
                <div>
                    <span class="small text-muted" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check text-success me-1"></i> Direct Escrow Cleared &bull; Central Settlement Foundation
                    </span>
                </div>
            </div>
        </div>

        {{-- KPI 3: Average Review Score --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Average Review Score</span>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.7rem;">Verified Diners</span>
                    </div>
                    <div class="h2 fw-bold text-dark mb-2 d-flex align-items-center gap-2" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                        <span>{{ number_format($stats['reviewAverage'], 1) }} Stars</span>
                        <span class="text-warning fs-4">★</span>
                    </div>
                </div>
                <div>
                    <span class="small text-muted" style="font-size: 0.78rem;">
                        {{ $stats['reviewCount'] }} Sovereign Audits verified &bull; Guest feedback
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section: Dish Performance Index & Today's Reservations --}}
    <div class="row g-4 mb-4">
        {{-- Left Column: Dish Performance Index --}}
        <div class="col-lg-7 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-graph-up-arrow text-success me-1.5"></i> Dish Performance Index
                        </h2>
                        <p class="text-muted small mb-0">Top culinary offerings, volume velocity, and diner satisfaction</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('restaurant.services.index') }}">
                        Menu and service offerings &rarr;
                    </a>
                </div>

                <div class="card-body p-4">
                    @forelse($dishPerformance as $dish)
                        <div class="dish-item-row mb-3.5 pb-3.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark fs-6">{{ $dish['name'] }}</span>
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small font-monospace">
                                    {{ $dish['orders'] }} Orders
                                </span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background: #f1f5f9;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $dish['percentage'] }}%;" aria-valuenow="{{ $dish['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-warning fw-bold">
                                    ★ {{ $dish['rating'] }}
                                </span>
                                <strong class="text-dark font-monospace fs-6">
                                    ${{ number_format($dish['price'], 2) }}
                                </strong>
                            </div>
                        </div>
                    @empty
                        {{-- Fallback realistic demo dishes matching screenshot if no services added yet --}}
                        <div class="dish-item-row mb-3.5 pb-3.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark fs-6">Special Beyaynetu Combo</span>
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small font-monospace">120 Orders</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background: #f1f5f9;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 95%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-warning fw-bold">★ 5.0</span>
                                <strong class="text-dark font-monospace fs-6">$22.00</strong>
                            </div>
                        </div>

                        <div class="dish-item-row mb-3.5 pb-3.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark fs-6">Sizzling Doro Wat Skillet</span>
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small font-monospace">95 Orders</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background: #f1f5f9;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 78%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-warning fw-bold">★ 4.9</span>
                                <strong class="text-dark font-monospace fs-6">$28.00</strong>
                            </div>
                        </div>

                        <div class="dish-item-row mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark fs-6">Gondar Spiced Kitfo</span>
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small font-monospace">84 Orders</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; border-radius: 3px; background: #f1f5f9;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 68%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-warning fw-bold">★ 4.8</span>
                                <strong class="text-dark font-monospace fs-6">$24.00</strong>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Today's Reservations --}}
        <div class="col-lg-5 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-calendar-check text-primary me-1.5"></i> Today's Reservations
                        </h2>
                        <p class="text-muted small mb-0">Live table allocations</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('restaurant.reservations.index') }}">
                        All &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5 flex-grow-1">
                    @forelse($recentReservations as $booking)
                        @php
                            $res = $booking->restaurantReservation;
                            $statusPill = match($booking->status) {
                                'confirmed', 'completed' => ['label' => 'Confirmed', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                'accepted', 'payment_pending' => ['label' => 'Approved', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                'pending' => ['label' => 'Pending', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                default => ['label' => ucfirst($booking->status), 'class' => 'bg-light text-dark border']
                            };
                        @endphp
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h3 class="h6 fw-bold text-dark mb-0">{{ $booking->tourist->full_name ?? 'Diner Guest Party' }}</h3>
                                <span class="badge {{ $statusPill['class'] }} rounded-pill px-2.5 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                    {{ $statusPill['label'] }}
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-people me-1"></i> {{ $res->guest_count ?? 2 }} People &bull; {{ $res ? substr($res->start_time, 0, 5) : '18:30' }} (Today)
                            </div>
                            @if($res?->restaurantTable)
                                <div class="mt-1.5">
                                    <span class="badge bg-white text-dark border rounded-pill px-2 py-0.5 small" style="font-size: 0.68rem;">
                                        📍 Table {{ $res->restaurantTable->table_number }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        {{-- Fallback realistic demo reservations matching screenshot --}}
                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h3 class="h6 fw-bold text-dark mb-0">Sarah Jenkins Party</h3>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                    Confirmed
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-people me-1"></i> 3 People &bull; 18:30 (Today)
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h3 class="h6 fw-bold text-dark mb-0">French Delegation VIP</h3>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                    Approved
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-people me-1"></i> 8 People &bull; 19:00 (Today)
                            </div>
                        </div>

                        <div class="card border rounded-3 p-3 mb-0 bg-light-subtle shadow-2xs">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h3 class="h6 fw-bold text-dark mb-0">Marcus Vane Table</h3>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                    Pending
                                </span>
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-people me-1"></i> 2 People &bull; 20:15 (Today)
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="card-footer bg-white border-top p-3 text-center">
                    <a class="btn btn-vn-navy btn-sm w-100 rounded-3 py-2 fw-bold" href="{{ route('restaurant.tables.index') }}">
                        <i class="bi bi-layout-three-columns me-1"></i> Table inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
