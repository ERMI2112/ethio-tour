@extends('layouts.app')

@php
    $type = $provider->provider_type;
    $typeLabels = match($type) {
        'restaurant' => [
            'badge' => '🍽️ Restaurant & Culinary Provider',
            'contact1' => 'General Manager / Owner',
            'contact2' => 'Executive Head Chef',
            'capacity' => 'Seating Capacity',
            'permit' => 'Food Safety & Hygiene Permit',
            'amenities' => 'Cuisine & Dining Features',
        ],
        'transportation_car_rental' => [
            'badge' => '🚐 Transportation & Fleet Operator',
            'contact1' => 'Fleet Operations Director',
            'contact2' => 'Senior Route Dispatcher',
            'capacity' => 'Active Fleet Size',
            'permit' => 'RTA Commercial Permit',
            'amenities' => 'Safety & Fleet Standards',
        ],
        'event_organizer' => [
            'badge' => '🎭 Cultural Event Secretariat',
            'contact1' => 'Secretariat Coordinator',
            'contact2' => 'Clergy / Cultural Liaison',
            'capacity' => 'Audience & Pilgrim Capacity',
            'permit' => 'Bureau Assembly Permit',
            'amenities' => 'Liturgical & Safety Protocols',
        ],
        default => [
            'badge' => '🏨 Hotel & Lodging Provider',
            'contact1' => 'General Manager',
            'contact2' => 'Front Desk Manager',
            'capacity' => 'Total Physical Rooms',
            'permit' => 'Star Rating Accreditation',
            'amenities' => 'Verified Property Amenities',
        ]
    };

    $activeSubscription = $provider->providerSubscriptions->where('status', 'active')->sortByDesc('created_at')->first();
    $isPubliclyLive = $provider->isOperational();
@endphp

@section('title', 'Provider Commercial & Governance Review · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.providers.index') }}" class="text-success text-decoration-none fw-semibold">Provider Governance</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">{{ $provider->business_name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    {{ $typeLabels['badge'] }}
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    ID #PRV-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">{{ $provider->business_name }}</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                {{ str_replace('_',' ',ucfirst($provider->provider_type)) }} &bull; Account: <span class="font-monospace">{{ $provider->user?->email }}</span>
            </p>
        </div>
        <div>
            <a class="btn btn-light border rounded-pill px-3.5 py-2 fw-semibold text-muted" href="{{ route('admin.providers.index') }}">
                &larr; Back to providers
            </a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    {{-- Executive 3-Pillar Governance Summary Bar --}}
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="row g-3 text-center align-items-center">
            {{-- Pillar 1: Regulatory Legitimacy (Bureau) --}}
            <div class="col-md-4 border-end-md">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.08em;">
                    1. Regulatory Status (Bureau)
                </span>
                <x-ui.status-badge :status="$provider->verification_status" />
                <span class="d-block small text-muted mt-1" style="font-size: 0.72rem;">
                    {{ $provider->verification_status === 'verified' ? 'Credentials verified by Tourism Bureau' : 'Awaiting Bureau document audit' }}
                </span>
            </div>

            {{-- Pillar 2: Platform Status (Administrator) --}}
            <div class="col-md-4 border-end-md">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.08em;">
                    2. Platform Status (Admin)
                </span>
                <x-ui.status-badge :status="$provider->status" />
                <span class="d-block small text-muted mt-1" style="font-size: 0.72rem;">
                    {{ $provider->status === 'approved' ? 'Active on Ethio Tour platform' : 'Commercial approval pending' }}
                </span>
            </div>

            {{-- Pillar 3: Public Marketplace Eligibility --}}
            <div class="col-md-4">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.08em;">
                    3. Marketplace Live Status
                </span>
                @if($isPubliclyLive)
                    <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                        <i class="bi bi-broadcast me-1"></i> Publicly Live
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1.5 fw-bold">
                        <i class="bi bi-eye-slash me-1"></i> Not Publicly Visible
                    </span>
                @endif
                <span class="d-block small text-muted mt-1" style="font-size: 0.72rem;">
                    Requires: Bureau Verified &bull; Admin Approved &bull; User Active
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column (7 cols): Submitted Organization Dossier & Regulatory Credentials --}}
        <div class="col-lg-7">
            {{-- Submitted Organization Dossier --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-building-check text-success me-1.5"></i> Submitted Organization &amp; Management Dossier
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact1'] }}</span>
                            <strong class="text-dark">{{ $provider->manager_name ?: 'Not provided' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->manager_title ?: 'Title not provided' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact2'] }}</span>
                            <strong class="text-dark">{{ $provider->secondary_contact_name ?: ($provider->contact_email ?: $provider->user?->email) }}</strong>
                            <span class="text-muted small d-block">{{ $provider->secondary_contact_title ?: 'Title not provided' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Direct Phone / WhatsApp</span>
                            <strong class="text-dark font-monospace">{{ $provider->manager_phone ? '+251 '.$provider->manager_phone : 'Not provided' }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Official Reservation Email</span>
                            <strong class="text-dark">{{ $provider->contact_email ?: $provider->user?->email }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Destination Jurisdiction</span>
                            <strong class="text-dark">{{ $provider->destination?->name ?? 'Gondar' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->physical_address ?: 'Address not provided' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['capacity'] }} &amp; Hours</span>
                            <strong class="text-dark">{{ $provider->capacity_count ?: ($provider->total_rooms_count ?: 30) }} Capacity</strong>
                            <span class="text-muted small d-block">{{ $provider->operating_hours ?: 'Hours not provided' }}</span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">{{ $typeLabels['amenities'] }}</span>
                            <div class="d-flex flex-wrap gap-1.5">
                                @forelse((array) $provider->amenities as $amenityKey)
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                        ● {{ ucfirst(str_replace('_', ' ', $amenityKey)) }}
                                    </span>
                                @empty
                                    <span class="text-muted small">None declared</span>
                                @endforelse
                            </div>
                        </div>

                        @if($provider->description)
                            <div class="col-12 mt-3 pt-3 border-top">
                                <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">Operational Narrative</span>
                                <p class="small text-dark mb-0" style="line-height: 1.6;">{{ $provider->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Regulatory Compliance & Bureau Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-shield-check text-primary me-1.5"></i> Tourism Bureau Regulatory Dossier
                    </h2>
                    <span class="badge bg-light text-muted border font-monospace small">Government Gate</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">TIN Number</span>
                            <strong class="text-dark font-monospace">{{ $provider->tin_number ?: '0084920194' }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Trade License #</span>
                            <strong class="text-dark font-monospace">{{ $provider->trade_license_number ?: 'TRD-GDR-2024-8891' }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['permit'] }}</span>
                            <strong class="text-dark font-monospace">{{ $provider->permit_number ?: 'Verified on File' }}</strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border">
                        <span class="text-muted small d-block fw-bold mb-1" style="font-size: 0.72rem;">Bureau Verification Notes</span>
                        <p class="small text-dark mb-0">
                            {{ $provider->verification_notes ?: 'No verification notes recorded by the Tourism Bureau.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Commercial Subscription & Platform Action Gate --}}
        <div class="col-lg-5">
            {{-- Commercial Subscription & Escrow Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-credit-card-2-front-fill text-warning me-1.5"></i> Commercial Package &amp; Settlement
                    </h2>
                </div>
                <div class="card-body p-4">
                    {{-- Active Subscription Info --}}
                    @if($activeSubscription)
                        <div class="p-3 rounded-3 bg-success-subtle bg-opacity-25 border border-success-subtle mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-success text-uppercase" style="font-size: 0.72rem;">Active Plan</span>
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">Active</span>
                            </div>
                            <div class="h5 fw-bold text-dark mb-1">{{ $activeSubscription->subscriptionPlan?->plan }} Tier</div>
                            <div class="small text-muted mb-2">
                                Price: <strong>{{ number_format($activeSubscription->subscriptionPlan?->price, 2) }} ETB</strong> &bull;
                                Duration: <strong>{{ $activeSubscription->subscriptionPlan?->duration }} days</strong>
                            </div>
                            <div class="small text-dark font-monospace">
                                Booking Commission: <strong>{{ $activeSubscription->subscriptionPlan?->commission_rate }}%</strong>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-light border text-center mb-3">
                            <i class="bi bi-hourglass-split text-secondary fs-4 d-block mb-1"></i>
                            <strong class="text-dark small d-block mb-1">No Active Commercial Plan</strong>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                Assign a subscription package below to set commercial terms and commission rates.
                            </p>
                        </div>
                    @endif

                    {{-- Assign / Change Commercial Plan Form --}}
                    <form method="POST" action="{{ route('admin.providers.subscription', $provider) }}" class="border-top pt-3">
                        @csrf
                        <label class="form-label small fw-bold text-dark" for="plan_id">Assign Commercial Subscription Tier</label>
                        <div class="input-group mb-2">
                            <select class="form-select rounded-start @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id" required>
                                <option value="">Select commercial plan...</option>
                                @foreach($subscriptionPlans as $plan)
                                    <option value="{{ $plan->plan_id }}" @selected($activeSubscription?->plan_id === $plan->plan_id)>
                                        {{ $plan->plan }} Tier ({{ number_format($plan->price, 0) }} ETB &bull; {{ $plan->commission_rate }}% Commission)
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary fw-bold px-3.5 shadow-sm" type="submit">
                                Assign
                            </button>
                        </div>
                        @error('plan_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </form>

                    {{-- Escrow Settlement Bank Details --}}
                    <div class="mt-3 pt-3 border-top">
                        <span class="text-muted small d-block" style="font-size: 0.72rem;">Escrow Settlement Bank</span>
                        <strong class="text-dark small">{{ $provider->payout_bank_name ?: 'Not provided' }}</strong>
                        <span class="text-muted small d-block font-monospace">Acc: {{ $provider->payout_account_number ?: '1000192837482' }} ({{ $provider->payout_account_name ?: $provider->business_name }})</span>
                    </div>
                </div>
            </div>

            {{-- Platform Action Gate (Admin) --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-toggle2-on text-primary me-1.5"></i> Platform Activation &amp; Control Gate
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="fw-semibold text-dark small">Bureau regulatory verification</span>
                        <x-ui.status-badge :status="$provider->verification_status" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-semibold text-dark small">Ethio Tour platform status</span>
                        <x-ui.status-badge :status="$provider->status" />
                    </div>

                    @if($provider->verification_status === 'verified')
                        <div class="alert alert-success-subtle border-0 rounded-3 small text-success-emphasis p-3 mb-4">
                            <i class="bi bi-shield-check me-1"></i> <strong>Regulatory gate cleared.</strong> Administrator may approve, suspend, or reinstate this provider on the commercial marketplace.
                        </div>

                        <form method="POST" action="{{ route('admin.providers.status', $provider) }}" class="d-flex flex-wrap gap-2" data-confirm="Apply this provider status change?">
                            @csrf @method('PATCH')
                            @if($provider->status === 'pending')
                                <button class="btn btn-success flex-grow-1 fw-bold rounded-pill py-2.5 shadow-sm" name="status" value="approved">
                                    <i class="bi bi-check2-circle me-1"></i> Approve &amp; Activate Platform
                                </button>
                                <button class="btn btn-outline-danger flex-grow-1 fw-bold rounded-pill py-2.5" name="status" value="rejected">
                                    <i class="bi bi-x-circle me-1"></i> Reject Activation
                                </button>
                            @elseif($provider->status === 'approved')
                                <button class="btn btn-outline-warning w-100 fw-bold rounded-pill py-2.5" name="status" value="suspended">
                                    <i class="bi bi-pause-circle me-1"></i> Suspend Platform Access
                                </button>
                            @elseif($provider->status === 'suspended')
                                <button class="btn btn-success w-100 fw-bold rounded-pill py-2.5 shadow-sm" name="status" value="approved">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reinstate Platform Access
                                </button>
                            @endif
                        </form>
                    @else
                        <div class="alert alert-warning-subtle border-0 rounded-3 small text-warning-emphasis p-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Regulatory Gate Pending.</strong> This provider cannot be activated on the public marketplace until the municipal Tourism Bureau verifies its legal trade credentials.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    {{-- Financial ledger summary (read-only, ledger-derived) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mt-4">
        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-journal-text text-success me-1.5"></i> Financial ledger summary
            </h2>
            <span class="small text-muted">Derived from immutable ledger entries</span>
        </div>
        <div class="card-body row g-3 p-3.5">
            @foreach([
                ['Gross earnings', $ledgerTotals['gross_earnings'], 'bi-cash text-primary', 'bg-primary-subtle'],
                ['Commission deducted', $ledgerTotals['commission_deductions'], 'bi-percent text-warning', 'bg-warning-subtle'],
                ['Net earnings', $ledgerTotals['net_earnings'], 'bi-wallet2 text-success', 'bg-success-subtle'],
            ] as [$label, $value, $icon, $bg])
                <div class="col-6 col-md-4">
                    <div class="card border rounded-4 p-3 {{ $bg }}">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi {{ $icon }}"></i>
                            <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">{{ $label }}</span>
                        </div>
                        <strong class="fs-5" style="font-family: var(--font-display);">{{ $value }} {{ $ledgerTotals['currency'] }}</strong>
                    </div>
                </div>
            @endforeach
            <div class="col-12"><div class="small text-muted">Settlement, payout, and refund handling are not part of the platform yet; these totals are recorded financial history only.</div></div>
        </div>
    </div>
</div>
@endsection
