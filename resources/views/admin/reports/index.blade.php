@extends('layouts.app')

@section('title', 'Reports & Analytics · Administrator')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Reports &amp; Analytics</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;" role="status"></span>
                    ADMINISTRATOR WORKSPACE
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Reports and analytics</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Track platform activity and booking volume across the network.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-graph-up-arrow me-1"></i> Platform Intelligence
            </span>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <form class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="from">
                    <i class="bi bi-calendar-event me-1"></i> From
                </label>
                <input class="form-control rounded-3" type="date" id="from" name="from" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="to">
                    <i class="bi bi-calendar-event me-1"></i> To
                </label>
                <input class="form-control rounded-3" type="date" id="to" name="to" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="status">
                    <i class="bi bi-funnel-fill me-1"></i> Booking status
                </label>
                <select class="form-select rounded-3" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending','accepted','rejected','payment_pending','confirmed','cancelled','completed'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst(str_replace('_',' ', $option)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-vn-navy w-100 fw-bold shadow-sm rounded-3 py-2">
                    Apply filters
                </button>
            </div>
        </div>
    </form>

    {{-- KPI Metric Cards --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['Total users', $totalUsers, 'bi-people-fill', 'text-primary', 'bg-primary-subtle'],
            ['Active users', $activeUsers, 'bi-person-check-fill', 'text-success', 'bg-success-subtle'],
            ['Bookings', $bookingTotal, 'bi-journal-check', 'text-info', 'bg-info-subtle'],
            ['Reviews', $reviewCount, 'bi-star-fill', 'text-warning', 'bg-warning-subtle'],
            ['Average rating', $reviewAverage === null ? '—' : number_format((float) $reviewAverage, 1), 'bi-graph-up', 'text-danger', 'bg-danger-subtle'],
        ] as [$label, $value, $icon, $color, $bg])
            <div class="col-6 col-xl">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">{{ $label }}</span>
                        <div class="d-flex align-items-center justify-content-center rounded-circle {{ $bg }}" style="width: 28px; height: 28px;">
                            <i class="bi {{ $icon }} {{ $color }}" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    <div class="h3 fw-bold mb-0 {{ $color }}" style="font-family: var(--font-display);">{{ $value }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 2-Column: Role Breakdown & Booking Status --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-people-fill text-primary me-1.5"></i> Users by role
                    </h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($roleBreakdown as $role => $total)
                        @php
                            $roleIcon = match($role) {
                                'tourist' => 'bi-suitcase-lg-fill text-success',
                                'tour_guide' => 'bi-person-badge-fill text-info',
                                'service_provider' => 'bi-building-fill text-primary',
                                'tourism_bureau_officer' => 'bi-shield-fill text-warning',
                                'administrator' => 'bi-gear-fill text-danger',
                                default => 'bi-person-fill text-secondary',
                            };
                        @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi {{ $roleIcon }}"></i>
                                <span class="fw-semibold">{{ str_replace('_',' ', ucfirst($role)) }}</span>
                            </span>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-bold">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-journal-check text-success me-1.5"></i> Booking status
                    </h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($statusBreakdown as $state => $total)
                        @php
                            $stateColor = match($state) {
                                'completed' => 'bg-success-subtle text-success',
                                'confirmed' => 'bg-info-subtle text-info',
                                'accepted' => 'bg-primary-subtle text-primary',
                                'pending', 'payment_pending' => 'bg-warning-subtle text-warning-emphasis',
                                'cancelled', 'rejected' => 'bg-danger-subtle text-danger',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span class="fw-semibold">{{ ucfirst(str_replace('_',' ', $state)) }}</span>
                            <span class="badge {{ $stateColor }} rounded-pill px-2.5 py-1 fw-bold">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bookings by Domain --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white p-3.5 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-pie-chart-fill text-info me-1.5"></i> Bookings by domain
            </h2>
        </div>
        <div class="card-body row g-3 p-3.5">
            @foreach($domainBookings as $domain => $total)
                @php
                    $domainIcon = match($domain) {
                        'hotel' => 'bi-building text-primary',
                        'restaurant' => 'bi-cup-hot-fill text-warning',
                        'tour_guide', 'guide' => 'bi-person-badge text-success',
                        'transportation', 'transport' => 'bi-truck text-info',
                        'event' => 'bi-ticket-perforated text-danger',
                        default => 'bi-grid text-secondary',
                    };
                @endphp
                <div class="col-6 col-md-4 col-xl">
                    <div class="card border rounded-4 p-3 h-100 bg-light-subtle text-center">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-white border mx-auto mb-2" style="width: 44px; height: 44px;">
                            <i class="bi {{ $domainIcon }} fs-5"></i>
                        </div>
                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">{{ ucfirst($domain) }}</div>
                        <strong class="fs-3" style="font-family: var(--font-display);">{{ $total }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Subscription Configuration --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-credit-card-2-front-fill text-warning me-1.5"></i> Subscription configuration
            </h2>
        </div>
        <div class="card-body row g-3 p-3.5">
            @foreach([
                ['Total plans', $planTotal, 'bi-toggles text-primary', 'bg-primary-subtle'],
                ['Active plans', $activePlans, 'bi-check-circle-fill text-success', 'bg-success-subtle'],
                ['Inactive plans', $inactivePlans, 'bi-pause-circle-fill text-secondary', 'bg-light'],
                ['Active subscriptions', $activeSubscriptions, 'bi-person-check-fill text-info', 'bg-info-subtle'],
            ] as [$label, $value, $icon, $bg])
                <div class="col-6 col-md-3">
                    <div class="card border rounded-4 p-3 {{ $bg }}">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi {{ $icon }}"></i>
                            <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">{{ $label }}</span>
                        </div>
                        <strong class="fs-3" style="font-family: var(--font-display);">{{ $value }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
