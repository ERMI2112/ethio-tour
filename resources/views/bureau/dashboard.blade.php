@extends('layouts.app')

@section('title', 'Tourism Bureau Oversight · Sovereign Audit Console')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Sovereign Audit Console Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    Bureau Oversight
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    Gondar City Directorate
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Sovereign Audit Console!
            </h1>
            <p class="text-secondary mb-0 small">
                Gondar City Digital Tourism Bureau Directorate &bull; Official Regulatory Supervision Gateway
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Bureau Officer Identity Pill --}}
            <div class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-sm">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    🛡️
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        {{ auth()->user()->name ?: 'Sarah Jenkins' }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Bureau Supervisor</div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm" href="{{ route('bureau.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Audit Reports
                </a>
            </div>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    {{-- 4-Card Sovereign KPI Matrix (Page 8 Layout) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Total Active Tourists</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        14,240
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Peak Season</span>
                    <span class="small text-muted">+28% Growth YoY</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Certified Providers</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        2,481 Agents
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5 small">All Audited</span>
                    <span class="small text-muted">18 pending verification</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Active Escrow Volume</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        $189,450
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Sovereign Secured</span>
                    <span class="small text-muted">Commercial gateway</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Bureau Complaints</span>
                    <div class="h3 fw-bold text-dark mb-1 font-monospace" style="font-family: var(--font-display);">
                        2 Active
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small">Under Investigation</span>
                    <span class="small text-muted">Action within 24h</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Attention Queues --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="h6 fw-bold text-dark mb-3 text-uppercase" style="letter-spacing: 0.05em; font-size: 0.78rem;">
            Needs attention
        </h2>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4" style="border-left: 4px solid #e5a919 !important;">
                    <span class="badge bg-warning text-dark fw-bold mb-2 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Guide Queue</span>
                    <h3 class="h5 fw-bold text-dark mb-1">{{ $pendingGuides }} guide{{ $pendingGuides === 1 ? '' : 's' }} awaiting verification</h3>
                    <p class="small text-muted mb-3">Review guide credentials, licenses, and background before issuing accreditation.</p>
                    <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark" href="{{ route('bureau.guides.index', ['status' => 'pending']) }}">
                        Review Guide Queue &rarr;
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4" style="border-left: 4px solid #e5a919 !important;">
                    <span class="badge bg-warning text-dark fw-bold mb-2 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Provider Queue</span>
                    <h3 class="h5 fw-bold text-dark mb-1">{{ $pendingProviders }} provider{{ $pendingProviders === 1 ? '' : 's' }} awaiting verification</h3>
                    <p class="small text-muted mb-3">Verify hotel, dining, and transportation provider regulatory standing.</p>
                    <a class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark" href="{{ route('bureau.providers.index', ['status' => 'pending']) }}">
                        Review Provider Queue &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Main 2-Column Section: License Verification Queue & Sovereign Advisory --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Provider License Verification Queue & Monthly Trend Chart --}}
        <div class="col-lg-7">
            {{-- Provider License Verification Queue --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-patch-check-fill text-primary me-1.5"></i> Provider License Verification Queue
                        </h2>
                        <p class="text-muted small mb-0">Official regulatory audit &amp; credential validation</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('bureau.providers.index') }}">
                        View queue &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5">
                    <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Gondar Royal Guide Association</h3>
                                <span class="small text-secondary">Document: Cooperative Charter &bull; 14 Licensed Historians</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Approved</span>
                                <a class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" href="{{ route('bureau.guides.index') }}">Audit Files</a>
                            </div>
                        </div>
                    </div>

                    <div class="card border rounded-3 p-3 mb-2.5 bg-light-subtle shadow-2xs">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Simien Wilderness Lodge Pvt</h3>
                                <span class="small text-secondary">Document: UNESCO Buffer Zone Audit &bull; Eco-Lodge Tier</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">Pending</span>
                                <a class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" href="{{ route('bureau.providers.index') }}">Audit Files</a>
                            </div>
                        </div>
                    </div>

                    <div class="card border rounded-3 p-3 mb-0 bg-light-subtle shadow-2xs">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-0.5">Abyssinian Transport Co-op</h3>
                                <span class="small text-secondary">Document: Sovereign Escrow Setup &bull; 8 Minivans</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small">Rejected</span>
                                <a class="btn btn-sm btn-dark rounded-pill px-3 fw-bold" href="{{ route('bureau.providers.index') }}">Audit Files</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Visitor Trend Line Chart --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-graph-up text-success me-1.5"></i> Monthly Visitor Trend Line Chart
                    </h2>
                    <span class="badge bg-light text-dark border font-monospace small">Jan - Dec 2026</span>
                </div>
                <div class="p-3 rounded-3 bg-light-subtle border text-center">
                    <svg class="w-100" height="120" viewBox="0 0 500 120" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0b5e42" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#0b5e42" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,80 Q70,90 140,50 T280,75 T420,20 L500,10 L500,120 L0,120 Z" fill="url(#trendGrad)"/>
                        <path d="M0,80 Q70,90 140,50 T280,75 T420,20 L500,10" fill="none" stroke="#0b5e42" stroke-width="3.5" stroke-linecap="round"/>
                        <circle cx="140" cy="50" r="4" fill="#0b5e42"/>
                        <circle cx="280" cy="75" r="4" fill="#0b5e42"/>
                        <circle cx="420" cy="20" r="4" fill="#0b5e42"/>
                        <circle cx="500" cy="10" r="4" fill="#e5a919"/>
                    </svg>
                    <div class="d-flex justify-content-between text-muted small mt-2 px-1" style="font-size: 0.72rem;">
                        <span>Q1 Jan-Mar</span>
                        <span>Q2 Apr-Jun</span>
                        <span>Q3 Jul-Sep</span>
                        <strong class="text-success">Q4 Timket Peak (+28%)</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Sovereign Bureau Advisory & Compliance Scorecard --}}
        <div class="col-lg-5">
            {{-- Sovereign Bureau Advisory --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%); color: #fff;">
                <div class="p-4">
                    <h2 class="h6 fw-bold text-white mb-2" style="font-family: var(--font-display);">
                        Sovereign Bureau Advisory
                    </h2>
                    <p class="text-white-50 small mb-3">Timket tourist inflow coordination mandate is fully active.</p>

                    <div class="d-flex flex-column gap-2">
                        <div class="p-2.5 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20">
                            <strong class="text-white small d-block mb-0.5">Tourist Escrow Release</strong>
                            <span class="text-white-50 small">Automated on GPS checkpoint entry confirmation.</span>
                        </div>
                        <div class="p-2.5 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20">
                            <strong class="text-white small d-block mb-0.5">UNESCO Buffer Audits</strong>
                            <span class="text-white-50 small">Mandatory inspection required bi-monthly for all licensed hotels.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Provider Compliance Scorecard --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h2 class="h6 fw-bold text-dark mb-1" style="font-family: var(--font-display);">
                    Provider Compliance Scorecard
                </h2>
                <p class="small text-muted mb-0" style="line-height: 1.6;">
                    National average score is <strong>94.2%</strong>. 4 guides suspended for working without active digital escrow credentials.
                </p>
            </div>

            {{-- Verification Summary Stats --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h2 id="summary-heading" class="h6 fw-bold text-dark mb-3" style="font-family: var(--font-display);">
                    Verification summary
                </h2>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-2.5 rounded-3 bg-light-subtle border">
                            <span class="text-muted small d-block" style="font-size: 0.7rem;">Verified Guides</span>
                            <strong class="text-success font-monospace fs-6">{{ $verifiedGuides }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 rounded-3 bg-light-subtle border">
                            <span class="text-muted small d-block" style="font-size: 0.7rem;">Verified Providers</span>
                            <strong class="text-success font-monospace fs-6">{{ $approvedProviders }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 rounded-3 bg-light-subtle border">
                            <span class="text-muted small d-block" style="font-size: 0.7rem;">Heritage Sites</span>
                            <strong class="text-dark font-monospace fs-6">{{ $attractionCount }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 rounded-3 bg-light-subtle border">
                            <span class="text-muted small d-block" style="font-size: 0.7rem;">Museum Records</span>
                            <strong class="text-primary font-monospace fs-6">{{ $museumCount }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Museum / Tourism Operations Card --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        Museum / tourism operations
                    </h2>
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action p-3" href="{{ route('bureau.museums.index') }}">
                        <strong class="small text-dark d-block mb-0.5">Museum directory</strong>
                        <span class="text-muted small">Manage public museum records and entrance fees.</span>
                    </a>
                    <a class="list-group-item list-group-item-action p-3" href="{{ route('bureau.attractions.index') }}">
                        <strong class="small text-dark d-block mb-0.5">Heritage attractions</strong>
                        <span class="text-muted small">Update historical sites, palaces, and monument profiles.</span>
                    </a>
                </div>
                <div class="card-footer bg-light-subtle p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Recent decisions: {{ $recentDecisions->count() }}</span>
                    <a class="small text-muted text-decoration-none" href="{{ route('notifications.index') }}">Notifications</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
