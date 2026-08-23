@extends('layouts.app')

@section('title', 'Event Organizer Dashboard · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Cultural Secretariat Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    Event Secretariat
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    Secretariat ID #EVE-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Selam, Event Director!
            </h1>
            <p class="text-secondary mb-0 small">
                {{ $provider->business_name }} &bull; Gondar Timket Festival Secretariat ID #EVE-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Secretariat Director Identity Pill --}}
            <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    🎭
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        {{ $provider->user?->name ?: 'Sarah Jenkins' }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Ethiopia Smart Passport</div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm" href="{{ route('event-organizer.events.create') }}">
                    <i class="bi bi-plus-lg me-1"></i> Create event
                </a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('event-organizer.profile') }}">
                    <i class="bi bi-gear me-1"></i> Profile
                </a>
            </div>
        </div>
    </div>

    {{-- Needs Attention Section --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="visually-hidden">Needs attention</h2>
        <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white" style="border-left: 4px solid #0b5e42 !important;">
            <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <span class="badge bg-success-subtle text-success fw-bold mb-1.5 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Active Inventory</span>
                    <h3 class="h6 fw-bold text-dark mb-1">Keep ticket inventory current</h3>
                    <p class="text-muted small mb-0">Events need ticket types before they can become bookable by international tourists.</p>
                </div>
                <a class="btn btn-outline-success btn-sm rounded-pill px-3.5 py-2 fw-bold" href="{{ route('event-organizer.events.index') }}">
                    Review events &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- 4-Card Cultural Event KPI Matrix (Page 7 Layout) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Registrations Secured</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        8,420 Passports
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Active</span>
                    <span class="small text-muted">3.2k International guests</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Sovereign Escrow Volume</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        $45,200
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small">Protected</span>
                    <span class="small text-muted">Funds verified in state gateway</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Venue Utilization</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        94%
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Optimal</span>
                    <span class="small text-muted">Fasilides' Pool grounds</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Days to Timket Celebration</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        6 Days
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small">Urgent Prep</span>
                    <span class="small text-muted">January 19 Festival Launch</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section: Scheduled Celebrations & Quotas / Advisories --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Upcoming Scheduled Celebrations & Verified Attendee Audits --}}
        <div class="col-lg-7">
            {{-- Upcoming Scheduled Celebrations --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-calendar-event-fill text-success me-1.5"></i> Upcoming Scheduled Celebrations
                        </h2>
                        <p class="text-muted small mb-0">Liturgical timetable and festival procession schedules</p>
                    </div>
                    <span class="badge bg-light text-dark border font-monospace">Gondar Protocol</span>
                </div>

                <div class="card-body p-3.5">
                    <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Eve of Timket (Ketera)</h3>
                                <span class="small text-secondary">Tabot procession from local churches to Fasilides' Pool</span>
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">Jan 18, 15:00</span>
                        </div>
                    </div>

                    <div class="card border rounded-3 p-3 mb-0 bg-light-subtle shadow-2xs">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Timket Main Baptism Liturgy</h3>
                                <span class="small text-secondary">Divine service, holy water blessing &amp; celebration</span>
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">Jan 19, 06:30</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Verified Attendee Audits --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-person-check text-primary me-1.5"></i> Recent Verified Attendee Audits
                    </h2>
                    <a class="small text-decoration-none" href="{{ route('event-organizer.events.bookings') }}">View all &rarr;</a>
                </div>
                <div class="card-body p-3.5">
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-2">
                        <div>
                            <strong class="text-dark small d-block">Marcus Sterling (UK)</strong>
                            <span class="small text-muted font-monospace">Passport GVL-921</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Secure Entry</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-0">
                        <div>
                            <strong class="text-dark small d-block">Ayako Sato (Japan)</strong>
                            <span class="small text-muted font-monospace">Passport LAL-004</span>
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">Pending Cert</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Ticket Quotas & Clergy Advisory --}}
        <div class="col-lg-5">
            {{-- Ticket Sales Quota --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%); color: #fff;">
                <div class="p-4">
                    <h2 class="h6 fw-bold text-white mb-3" style="font-family: var(--font-display);">
                        Ticket Sales Quota
                    </h2>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 small">VIP Festival Pass</span>
                            <strong class="text-white small font-monospace">840 / 1,000 sold</strong>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.15);">
                            <div class="progress-bar bg-warning" style="width: 84%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 small">General Admission</span>
                            <strong class="text-white small font-monospace">4,600 / 5,000 sold</strong>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.15);">
                            <div class="progress-bar bg-success" style="width: 92%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clergy & Safety Advisory --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h2 class="h6 fw-bold text-dark mb-1" style="font-family: var(--font-display);">
                    Clergy &amp; Safety Advisory
                </h2>
                <p class="small text-muted mb-0" style="line-height: 1.6;">
                    Dress code mandate: White cotton traditional attire (Shamma) recommended for baptism ceremonies. All international tourist groups must be accompanied by certified guides.
                </p>
            </div>

            {{-- Operational Insights --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        Operational insights
                    </h2>
                    <span class="small text-muted">Published events: <strong>{{ $publishedCount }}</strong></span>
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action p-3" href="{{ route('event-organizer.events.index') }}">
                        <strong class="small text-dark d-block mb-0.5">Events</strong>
                        <span class="text-muted small">View, edit, and manage ticket types.</span>
                    </a>
                    <a class="list-group-item list-group-item-action p-3" href="{{ route('event-organizer.events.bookings') }}">
                        <strong class="small text-dark d-block mb-0.5">Bookings</strong>
                        <span class="text-muted small">Review ticket reservations belonging to your events.</span>
                    </a>
                </div>
                <div class="card-footer bg-light-subtle p-3 border-top d-flex justify-content-between align-items-center">
                    <a class="small text-muted text-decoration-none" href="{{ route('notifications.index') }}">Notifications</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
