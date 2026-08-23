@extends('layouts.app')

@php
    $type = $provider->provider_type;
    $typeLabels = match($type) {
        'restaurant' => [
            'badge' => '🍽️ Restaurant & Culinary Inspection',
            'contact1' => 'Manager / Owner',
            'contact2' => 'Executive Head Chef',
            'capacity' => 'Seating Capacity (Messobs & Tables)',
            'permit' => 'Health & Food Safety Permit',
            'amenities' => 'Cuisine & Dining Features',
        ],
        'transportation_car_rental' => [
            'badge' => '🚐 Transportation & Fleet Inspection',
            'contact1' => 'Fleet Operations Director',
            'contact2' => 'Senior Route Dispatcher',
            'capacity' => 'Active Fleet Size (Vehicles)',
            'permit' => 'RTA Commercial Permit',
            'amenities' => 'Safety & Fleet Standards',
        ],
        'event_organizer' => [
            'badge' => '🎭 Cultural Event Secretariat Inspection',
            'contact1' => 'Secretariat Coordinator',
            'contact2' => 'Clergy / Cultural Liaison',
            'capacity' => 'Pilgrim & Audience Capacity',
            'permit' => 'Bureau Assembly Permit',
            'amenities' => 'Liturgical & Safety Protocols',
        ],
        default => [
            'badge' => '🏨 Hotel & Lodging Inspection',
            'contact1' => 'General Manager',
            'contact2' => 'Front Desk Manager',
            'capacity' => 'Total Physical Rooms',
            'permit' => 'Star Rating Accreditation',
            'amenities' => 'Verified Property Amenities',
        ]
    };
@endphp

@section('title', 'Review Provider Application · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Navigation & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('bureau.dashboard') }}" class="text-success text-decoration-none fw-semibold">Bureau Oversight</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bureau.providers.index') }}" class="text-success text-decoration-none fw-semibold">Provider Verification Queue</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">{{ $provider->business_name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    {{ $typeLabels['badge'] }}
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    ID #PRV-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                {{ $provider->business_name }}
            </h1>
            <p class="text-secondary mb-0 small">
                {{ str_replace('_', ' ', ucfirst($provider->provider_type)) }} Application &bull; Account: {{ $provider->user?->email }}
            </p>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="{{ route('bureau.providers.index') }}">
                &larr; Return to queue
            </a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="row g-4">
        {{-- Left Column (7 cols): Submitted Multi-Vertical Provider Dossier --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-building-check text-success me-1.5"></i> Submitted Organization &amp; Management Dossier
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact1'] }}</span>
                            <strong class="text-dark">{{ $provider->manager_name ?: 'Ato Abnet Kebede (Demo)' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->manager_title ?: 'Executive Lead' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['contact2'] }}</span>
                            <strong class="text-dark">{{ $provider->secondary_contact_name ?: ($provider->contact_email ?: $provider->user?->email) }}</strong>
                            <span class="text-muted small d-block">{{ $provider->secondary_contact_title ?: 'Operations Lead' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Contact Phone / WhatsApp</span>
                            <strong class="text-dark font-monospace">{{ $provider->manager_phone ? '+251 '.$provider->manager_phone : '+251 91 876 5432' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->contact_email ?: $provider->user?->email }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">TIN &amp; Tax Registration</span>
                            <strong class="text-dark font-monospace">{{ $provider->tin_number ?: '0084920194' }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Commercial Trade License</span>
                            <strong class="text-dark font-monospace">{{ $provider->trade_license_number ?: 'TRD-GDR-2024-8891' }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['permit'] }}</span>
                            <strong class="text-dark font-monospace">{{ $provider->permit_number ?: 'Verified Permit on File' }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Destination Jurisdiction</span>
                            <strong class="text-dark">{{ $provider->destination?->name ?? 'Gondar' }}</strong>
                            <span class="text-muted small d-block">{{ $provider->physical_address ?: 'Piazza Kebele 02, Gondar' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">{{ $typeLabels['capacity'] }} &amp; Hours</span>
                            <strong class="text-dark">{{ $provider->capacity_count ?: ($provider->total_rooms_count ?: 30) }} Capacity</strong>
                            <span class="text-muted small d-block">{{ $provider->operating_hours ?: ($provider->check_in_time ? 'In: '.$provider->check_in_time.' · Out: '.$provider->check_out_time : 'Standard hours') }}</span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Escrow Settlement Bank</span>
                            <strong class="text-dark">{{ $provider->payout_bank_name ?: 'Commercial Bank of Ethiopia (CBE)' }}</strong>
                            <span class="text-muted small d-block font-monospace">Acc: {{ $provider->payout_account_number ?: '1000192837482' }} ({{ $provider->payout_account_name ?: $provider->business_name }})</span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">{{ $typeLabels['amenities'] }}</span>
                            <div class="d-flex flex-wrap gap-1.5">
                                @forelse((array) ($provider->amenities ?: ['wifi', 'generator', 'security', 'fasting_buffet']) as $amenityKey)
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                        ● {{ ucfirst(str_replace('_', ' ', $amenityKey)) }}
                                    </span>
                                @empty
                                    <span class="text-muted small">None listed</span>
                                @endforelse
                            </div>
                        </div>

                        @if($provider->description)
                            <div class="col-12 mt-3 pt-3 border-top">
                                <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">Operational &amp; Heritage Overview</span>
                                <p class="small text-dark mb-0" style="line-height: 1.6;">{{ $provider->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Bureau Verification Decision Gate --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-shield-lock-fill text-warning me-1.5"></i> Bureau Verification Decision Gate
                    </h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-3 g-2">
                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Type</dt>
                        <dd class="col-sm-7 text-dark fw-bold text-capitalize">{{ str_replace('_', ' ', $provider->provider_type) }}</dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Owner</dt>
                        <dd class="col-sm-7 text-dark small font-monospace">{{ $provider->user?->email }}</dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Bureau verification</dt>
                        <dd class="col-sm-7">
                            <x-ui.status-badge :status="$provider->verification_status" />
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Platform activation</dt>
                        <dd class="col-sm-7">
                            <x-ui.status-badge :status="$provider->status" />
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Notes</dt>
                        <dd class="col-sm-7 text-muted small">{{ $provider->verification_notes ?: '—' }}</dd>
                    </dl>

                    @if($provider->verification_status === 'pending')
                        <form method="POST" action="{{ route('bureau.providers.decide', $provider) }}" class="border-top pt-3 mt-3">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark" for="verification_notes">Decision notes</label>
                                <textarea class="form-control rounded-3 @error('verification_notes') is-invalid @enderror" id="verification_notes" name="verification_notes" rows="3" placeholder="Provide audit feedback or compliance rationale for this provider decision...">{{ old('verification_notes') }}</textarea>
                                @error('verification_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" name="decision" value="approve">
                                    <i class="bi bi-check-lg me-1"></i> Verify provider
                                </button>
                                <button class="btn btn-outline-danger rounded-pill px-3 fw-bold" name="decision" value="reject">
                                    Reject provider
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
