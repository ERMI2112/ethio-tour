@extends('layouts.app')

@php
    $type = $provider->provider_type;
    $typeLabels = match($type) {
        'restaurant' => [
            'badge' => '🍽️ Restaurant & Culinary Gateway',
            'contact1' => 'Manager / Owner',
            'contact2' => 'Executive Head Chef',
            'capacity' => 'Seating Capacity',
            'permit' => 'Health & Food Safety Permit',
            'amenities' => 'Cuisine & Dining Features',
        ],
        'transportation_car_rental' => [
            'badge' => '🚐 Transportation & Fleet Gateway',
            'contact1' => 'Fleet Operations Director',
            'contact2' => 'Senior Dispatcher',
            'capacity' => 'Active Fleet Size',
            'permit' => 'RTA Commercial Permit',
            'amenities' => 'Safety & Fleet Standards',
        ],
        'event_organizer' => [
            'badge' => '🎭 Cultural Event Secretariat Gateway',
            'contact1' => 'Secretariat Coordinator',
            'contact2' => 'Clergy / Cultural Liaison',
            'capacity' => 'Pilgrim & Audience Capacity',
            'permit' => 'Bureau Assembly Permit',
            'amenities' => 'Liturgical & Safety Protocols',
        ],
        default => [
            'badge' => '🏨 Hotel & Lodging Gateway',
            'contact1' => 'General Manager',
            'contact2' => 'Front Desk Manager',
            'capacity' => 'Rooms Capacity',
            'permit' => 'Star Rating Accreditation',
            'amenities' => 'Verified Hotel Amenities',
        ]
    };
@endphp

@section('title', 'Provider Application Status · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Application Status Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('provider.status') }}" class="text-success text-decoration-none fw-semibold">Provider Workspace</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Provider Application Status</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 5px; height: 5px;" role="status"></span>
                    {{ $typeLabels['badge'] }}
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    Application #APP-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Provider Application Status
            </h1>
            <p class="text-secondary mb-0 small">
                Your onboarding account remains available while the platform reviews both regulatory verification and operational activation.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-vn-navy btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm" href="{{ route('provider.profile.edit') }}">
                <i class="bi bi-pencil-square me-1"></i> Edit profile
            </a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    {{-- 4-Stage Regulatory Verification Stepper --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <h2 class="h6 fw-bold mb-3 text-dark" style="font-family: var(--font-display);">
            <i class="bi bi-diagram-3-fill text-success me-1.5"></i> Onboarding &amp; Verification Progress
        </h2>

        <div class="row g-3 text-center">
            {{-- Step 1: Account Created --}}
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border bg-success-subtle bg-opacity-25 h-100">
                    <div class="badge bg-success text-white rounded-circle p-2 mb-2">✓</div>
                    <strong class="small d-block text-dark fw-bold">1. Account Created</strong>
                    <span class="small text-muted" style="font-size: 0.72rem;">User registered</span>
                </div>
            </div>

            {{-- Step 2: Details Submitted --}}
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border {{ $provider->manager_name || $provider->trade_license_number ? 'bg-success-subtle bg-opacity-25' : 'bg-warning-subtle bg-opacity-25' }} h-100">
                    <div class="badge {{ $provider->manager_name || $provider->trade_license_number ? 'bg-success' : 'bg-warning text-dark' }} rounded-circle p-2 mb-2">
                        {{ $provider->manager_name || $provider->trade_license_number ? '✓' : '2' }}
                    </div>
                    <strong class="small d-block text-dark fw-bold">2. Organization Dossier</strong>
                    <span class="small text-muted" style="font-size: 0.72rem;">
                        {{ $provider->manager_name ? 'Submitted' : 'Pending Details' }}
                    </span>
                </div>
            </div>

            {{-- Step 3: Bureau Verification --}}
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border {{ $provider->verification_status === 'verified' ? 'bg-success-subtle bg-opacity-25' : ($provider->verification_status === 'rejected' ? 'bg-danger-subtle bg-opacity-25' : 'bg-light') }} h-100">
                    <div class="badge {{ $provider->verification_status === 'verified' ? 'bg-success' : ($provider->verification_status === 'rejected' ? 'bg-danger' : 'bg-secondary') }} rounded-circle p-2 mb-2">
                        {{ $provider->verification_status === 'verified' ? '✓' : '3' }}
                    </div>
                    <strong class="small d-block text-dark fw-bold">3. Bureau Verification</strong>
                    <span class="small text-muted" style="font-size: 0.72rem;">
                        {{ ucfirst($provider->verification_status) }}
                    </span>
                </div>
            </div>

            {{-- Step 4: Platform Activation --}}
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border {{ $provider->status === 'approved' ? 'bg-success-subtle bg-opacity-25' : 'bg-light' }} h-100">
                    <div class="badge {{ $provider->status === 'approved' ? 'bg-success' : 'bg-secondary' }} rounded-circle p-2 mb-2">
                        {{ $provider->status === 'approved' ? '✓' : '4' }}
                    </div>
                    <strong class="small d-block text-dark fw-bold">4. Platform Activation</strong>
                    <span class="small text-muted" style="font-size: 0.72rem;">
                        {{ ucfirst($provider->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Formal Application Status Ledger & Property Details --}}
        <div class="col-lg-7">
            {{-- Official Application Ledger --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-file-earmark-text-fill text-primary me-1.5"></i> Regulatory Review Gates
                    </h2>
                    <span class="badge bg-light text-dark border font-monospace small">Live Audit</span>
                </div>

                <div class="card-body p-4">
                    <dl class="row mb-0 g-3">
                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Business</dt>
                        <dd class="col-sm-8 fw-bold text-dark fs-6">{{ $provider->business_name }}</dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Bureau verification</dt>
                        <dd class="col-sm-8">
                            <x-ui.status-badge :status="$provider->verification_status" />
                        </dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Platform activation</dt>
                        <dd class="col-sm-8">
                            <x-ui.status-badge :status="$provider->status" />
                        </dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Feedback</dt>
                        <dd class="col-sm-8">
                            @if($provider->verification_notes)
                                <div class="p-3 rounded-3 bg-light border text-dark small">
                                    {{ $provider->verification_notes }}
                                </div>
                            @else
                                <span class="text-muted small">No feedback has been recorded.</span>
                            @endif
                        </dd>
                    </dl>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a class="btn btn-outline-primary btn-sm rounded-pill px-3.5 fw-bold" href="{{ route('provider.profile.edit') }}">
                            <i class="bi bi-pencil me-1"></i> Edit profile
                        </a>
                        <span class="small text-muted font-monospace">Submitted {{ $provider->created_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Submitted Property Profile Summary --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-building-check text-success me-1.5"></i> Submitted Organization Dossier
                        </h2>
                        <span class="text-muted small">Management credentials &amp; compliance data under review</span>
                    </div>
                    <a class="small text-success fw-bold text-decoration-none" href="{{ route('provider.profile.edit') }}">Update &rarr;</a>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact1'] }}</span>
                            <strong class="text-dark small">{{ $provider->manager_name ?: 'Pending Submission' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->manager_title ?: 'Executive' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact2'] }}</span>
                            <strong class="text-dark small">{{ $provider->secondary_contact_name ?: ($provider->contact_email ?: $provider->user?->email) }}</strong>
                            <span class="text-muted small d-block">{{ $provider->secondary_contact_title ?: 'Operational Lead' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">TIN &amp; Commercial Permits</span>
                            <strong class="text-dark small font-monospace">
                                TIN: {{ $provider->tin_number ?: 'Pending' }} &bull; License: {{ $provider->trade_license_number ?: 'Pending' }}
                            </strong>
                            @if($provider->permit_number)
                                <span class="text-muted small d-block font-monospace">Permit: {{ $provider->permit_number }}</span>
                            @endif
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Destination &amp; {{ $typeLabels['capacity'] }}</span>
                            <strong class="text-dark small">
                                {{ $provider->destination?->name ?? 'Gondar' }} &bull; {{ $provider->capacity_count ?: ($provider->total_rooms_count ?: 30) }} Capacity
                            </strong>
                            <span class="text-muted small d-block">{{ $provider->operating_hours ?: 'Standard operating schedules' }}</span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Escrow Settlement Bank</span>
                            <strong class="text-dark small">{{ $provider->payout_bank_name ?: 'Commercial Bank of Ethiopia (CBE)' }}</strong>
                            <span class="text-muted small d-block font-monospace">Acc: {{ $provider->payout_account_number ?: 'Pending settlement account' }} ({{ $provider->payout_account_name ?: $provider->business_name }})</span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block mb-1.5" style="font-size: 0.72rem;">{{ $typeLabels['amenities'] }}</span>
                            <div class="d-flex flex-wrap gap-1.5">
                                @forelse((array) ($provider->amenities ?: ['wifi', 'generator', 'security']) as $amenityKey)
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                        ● {{ ucfirst(str_replace('_', ' ', $amenityKey)) }}
                                    </span>
                                @empty
                                    <span class="text-muted small">Standard specifications pending submission.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Subscription Configuration & Supervision Authority --}}
        <div class="col-lg-5">
            {{-- Subscription Configuration Card --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-credit-card-2-front-fill text-primary me-1.5"></i> Subscription configuration
                    </h2>
                </div>
                <div class="card-body p-4">
                    @php($subscription = $provider->providerSubscriptions->sortByDesc('created_at')->first())
                    @if($subscription)
                        <dl class="row mb-0 g-2">
                            <dt class="col-sm-5 text-muted small fw-bold">Current plan</dt>
                            <dd class="col-sm-7 fw-bold text-dark">{{ $subscription->subscriptionPlan?->plan ?: 'Unavailable' }}</dd>

                            <dt class="col-sm-5 text-muted small fw-bold">Plan state</dt>
                            <dd class="col-sm-7">
                                <x-ui.status-badge :status="$subscription->subscriptionPlan?->active ? 'active' : 'inactive'" />
                            </dd>

                            <dt class="col-sm-5 text-muted small fw-bold">Provider subscription</dt>
                            <dd class="col-sm-7">
                                <x-ui.status-badge :status="$subscription->status" />
                            </dd>
                        </dl>
                    @else
                        <div class="p-3 rounded-3 bg-light-subtle border text-center">
                            <i class="bi bi-hourglass-split text-secondary fs-4 d-block mb-1"></i>
                            <strong class="text-dark small d-block mb-1">Awaiting Verification</strong>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                No subscription configuration has been assigned. Billing will be available after payment integration.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Supervision Authority Callout --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%); color: #fff;">
                <div class="p-4">
                    <h2 class="h6 fw-bold text-white mb-2" style="font-family: var(--font-display);">
                        Gondar Directorate Oversight
                    </h2>
                    <p class="text-white-50 small mb-3">
                        Official licensing authority for certified tourism providers across the historic circuit.
                    </p>

                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20 mb-3">
                        <div class="small fw-bold text-warning mb-1 text-uppercase" style="font-size: 0.68rem;">SLA Review Timeline</div>
                        <p class="small text-white mb-0">Decisions are issued within 24 to 48 business hours following document audit.</p>
                    </div>

                    <div class="small text-white-50">
                        Help desk: <strong class="text-white">support@ethiotour.et</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
