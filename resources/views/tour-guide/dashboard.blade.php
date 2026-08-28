@extends('layouts.app')

@section('title', 'Tour Guide Dashboard · ' . ($guide->full_name ?: 'Tour Guide'))

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        {{-- Left Sidebar Column --}}
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>

        {{-- Right Main Column --}}
        <div class="col-lg-9">
            {{-- Supervised Historian Executive Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                            <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                            Tour Guide Portal
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                            ID #GDR-{{ str_pad($guide->guide_id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                        Selam, Guide {{ $guide->full_name ?: 'Tour Guide' }}!
                    </h1>
                    <p class="text-secondary mb-0 small">
                        Gondar City Supervised Historian ID #GDR-{{ str_pad($guide->guide_id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $guide->user?->email }}
                    </p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    {{-- Guide Profile Identity Pill --}}
                    <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                        <img src="{{ $guide->profileImageUrl() }}" alt="{{ $guide->full_name }}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                        <div class="text-start">
                            <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                                {{ $guide->full_name ?: ($guide->user?->email ?? 'Tour guide') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">Tour guide account</div>
                        </div>
                    </div>

                    <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('tour-guide.profile.edit') }}">
                        <i class="bi bi-person-gear me-1"></i> Edit profile
                    </a>
                </div>
            </div>

            @php
                $guideStatus = $guide->verification_status === 'verified' && $guide->admin_approval_status === 'pending'
                    ? 'Bureau verified · awaiting Administrator approval'
                    : ucfirst($guide->verification_status);
            @endphp
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" style="border-left: 4px solid #0b5e42 !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check fs-5"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold text-dark mb-0.5">Sovereign Registry Status</h2>
                            <p class="text-muted small mb-0">Your profile, verification documents, and booking availability are shown from current account data.</p>
                        </div>
                    </div>
                    <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm" style="font-size: 0.78rem;">
                        {{ $guideStatus }}
                    </span>
                </div>
            </div>

            {{-- Needs Attention Section --}}
            <section aria-labelledby="attention-heading" class="mb-4">
                <h2 id="attention-heading" class="visually-hidden">Needs attention</h2>
                @if($stats['pendingRequests'] > 0)
                    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #e5a919 !important;">
                        <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-1.5 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Action Required</span>
                                <h3 class="h6 fw-bold text-dark mb-1">{{ $stats['pendingRequests'] }} booking request(s) waiting</h3>
                                <p class="text-muted small mb-0">Review dates and itinerary requirements before accepting.</p>
                            </div>
                            <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-sm" href="{{ route('tour-guide.requests.index', ['status'=>'pending']) }}">
                                <i class="bi bi-inbox me-1"></i> Review requests
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #10b981 !important;">
                        <div class="card-body p-3 d-flex align-items-center gap-2.5">
                            <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">✓ Ready</span>
                            <span class="small text-muted"><strong>No booking requests are waiting.</strong> Check Availability before accepting new tours.</span>
                        </div>
                    </div>
                @endif
            </section>

            {{-- 3-Card Financial Cockpit (Page 2 Layout) --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">This Month's Earnings</span>
                            <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                                {{ number_format($stats['monthlyEarnings'], 2) }} ETB
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">{{ ucfirst($guide->availability_status ?? 'unavailable') }}</span>
                            <span class="small text-muted">{{ $stats['completedBookings'] }} completed tours</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Pending Escrow</span>
                            <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                                {{ number_format($stats['pendingEscrow'], 2) }} ETB
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small">Pending</span>
                            <span class="small text-muted">{{ $stats['activeBookings'] }} active reservations</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Total Lifetime Payout</span>
                            <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                                {{ number_format($stats['lifetimePayout'], 2) }} ETB
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small">Approved</span>
                            <span class="small text-muted">Completed tours recorded</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main 2-Column Section: Upcoming Escorted Journeys & Registry Analytics --}}
            <div class="row g-4 mb-4">
                {{-- Left Column (7 cols): Upcoming Escorted Journeys --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                                    <i class="bi bi-compass-fill text-success me-1.5"></i> Upcoming Escorted Journeys
                                </h2>
                                <p class="text-muted small mb-0">Scheduled guided tours and private excursions</p>
                            </div>
                            <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('tour-guide.requests.index') }}">
                                All requests &rarr;
                            </a>
                        </div>

                        <div class="card-body p-3.5">
                            @forelse($escortedJourneys as $booking)
                                @php
                                    $statusPill = match($booking->status) {
                                        'confirmed', 'completed' => ['label' => 'Active', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                        'accepted' => ['label' => 'Approved', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                        'pending' => ['label' => 'Pending', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                        default => ['label' => ucfirst($booking->status), 'class' => 'bg-light text-dark border']
                                    };
                                @endphp
                                <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <h3 class="h6 fw-bold text-dark mb-0.5">{{ $booking->tourist->full_name ?? $booking->tourist->user?->email ?? 'Tourist' }}</h3>
                                            <span class="small text-secondary">
                                                {{ $guide->destination?->name ?? 'Destination not specified' }}
                                            </span>
                                        </div>
                                        <span class="badge {{ $statusPill['class'] }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                            {{ $statusPill['label'] }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1 font-monospace">
                                        <i class="bi bi-clock me-1"></i> {{ $booking->booking_date?->format('M d, Y (H:i)') ?: 'Date not specified' }}
                                    </div>
                                </div>
                            @empty
                                <x-ui.empty-state title="No scheduled journeys" message="Accepted and confirmed guide bookings will appear here." />
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column (5 cols): Registry Analytics & Trust Score --}}
                <div class="col-lg-5">
                    {{-- Registry Analytics --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                        <h2 class="h6 fw-bold mb-3 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-person-check-fill text-primary me-1.5"></i> Registry Analytics
                        </h2>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="small text-muted fw-bold">Profile Completeness</span>
                                <strong class="text-dark font-monospace">{{ $stats['profileCompleteness'] }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px; background: #f1f5f9;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $stats['profileCompleteness'] }}%;"></div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-light-subtle border">
                            <div class="small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.68rem;">Average rating</div>
                            <div class="d-flex align-items-center gap-2">
                                @if($stats['reviewCount'] > 0)
                                    <span class="text-warning fs-5">★</span>
                                    <strong class="text-dark fs-6">{{ number_format($stats['averageRating'], 1) }}</strong>
                                    <span class="text-muted small">({{ $stats['reviewCount'] }} tourist review{{ $stats['reviewCount'] === 1 ? '' : 's' }})</span>
                                @else
                                    <span class="text-muted small">No tourist reviews yet.</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <div class="p-2 rounded-2 bg-light border text-center">
                                    <span class="text-muted small d-block" style="font-size: 0.68rem;">Pending guide requests</span>
                                    <strong class="text-dark font-monospace">{{ $stats['pendingRequests'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-2 bg-light border text-center">
                                    <span class="text-muted small d-block" style="font-size: 0.68rem;">Active bookings</span>
                                    <strong class="text-success font-monospace">{{ $stats['activeBookings'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Operational Profile Summary --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <div class="card-header bg-white p-3.5 border-bottom">
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                                <i class="bi bi-person-lines-fill text-info me-1.5"></i> Profile summary
                            </h2>
                        </div>
                        <div class="card-body p-3.5">
                            <ul class="list-unstyled mb-0 small d-flex flex-column gap-2.5">
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Daily Guide Rate</span>
                                    <strong class="text-success font-monospace">{{ $guide->daily_rate === null ? 'Not configured' : number_format((float) $guide->daily_rate, 2).' ETB' }}</strong>
                                </li>
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Primary Destination</span>
                                    <strong class="text-dark">{{ $guide->destination?->name ?? 'National Scope' }}</strong>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Availability Status</span>
                                    <a class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 small" href="{{ route('tour-guide.availability') }}">Availability &rarr;</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-light-subtle p-3 border-top d-flex justify-content-between align-items-center">
                            <a class="small text-muted text-decoration-none" href="{{ route('provider.reports') }}">Reports</a>
                            <a class="small text-muted text-decoration-none" href="{{ route('notifications.index') }}">Notifications</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
