@extends('layouts.app')

@section('title', 'User Inspection · '.$user->email)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-success text-decoration-none fw-semibold">Platform Users</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">{{ $user->email }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a class="btn btn-light border rounded-pill px-3.5 py-2 fw-semibold text-muted" href="{{ route('admin.users.index') }}">
                <i class="bi bi-arrow-left me-1"></i> Back to Users List
            </a>
        </div>
        <div class="d-flex gap-2">
            @if($user->user_id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}');">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-{{ $user->is_active ? 'danger' : 'success' }} btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-power me-1"></i> {{ $user->is_active ? 'Deactivate User Account' : 'Activate User Account' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Core Account Overview Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm border border-light" style="width: 64px; height: 64px;">
                            {{ strtoupper(substr($user->email, 0, 2)) }}
                        </div>
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $user->email }}</h1>
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                                <x-ui.status-badge :status="$user->is_active ? 'active' : 'inactive'" />
                            </div>
                            <p class="text-muted small mb-0 font-monospace">
                                User ID: #{{ $user->user_id }} &bull; Registered on {{ $user->created_at?->format('F d, Y · H:i') }} ({{ $user->created_at?->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="p-3 bg-light-subtle border rounded-4 d-inline-block text-start shadow-sm">
                        <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Platform Role</div>
                        <strong class="text-success text-capitalize fs-6">{{ str_replace('_', ' ', $user->role) }}</strong>
                        <div class="small text-muted mt-1">Status: <strong class="{{ $user->is_active ? 'text-success' : 'text-danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <!-- Role-Specific Deep Inspection Sections -->
    @if($user->role === 'tour_guide' && $user->tourGuide)
        @php($guide = $user->tourGuide)
        <section class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 mb-1">Tour Guide Credentials</span>
                        <h2 class="h4 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $guide->full_name }}</h2>
                    </div>
                    <span class="badge bg-{{ $guide->verification_status === 'verified' ? 'success' : 'warning' }}-subtle text-{{ $guide->verification_status === 'verified' ? 'success' : 'dark' }} px-3 py-2 rounded-pill fs-6 fw-bold">
                        {{ ucfirst($guide->verification_status) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3 text-center">
                        <img src="{{ $guide->profileImageUrl() }}" alt="{{ $guide->full_name }}" class="rounded-4 img-fluid mb-3 shadow-sm border" style="max-height: 180px; width: 100%; object-fit: cover;">
                        <div class="fw-bold fs-5 text-dark">{{ $guide->full_name }}</div>
                        <div class="text-muted small font-monospace">License: <code>{{ $guide->license_number }}</code></div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 bg-light-subtle border rounded-3">
                                    <span class="text-muted small d-block fw-bold">Primary Destination</span>
                                    <strong>{{ $guide->destination?->name ?? 'National' }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 bg-light-subtle border rounded-3">
                                    <span class="text-muted small d-block fw-bold">Experience</span>
                                    <strong>{{ $guide->years_of_experience ?? 0 }} Years</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 bg-light-subtle border rounded-3">
                                    <span class="text-muted small d-block fw-bold">Daily Rate</span>
                                    <strong class="text-success">{{ number_format((float) $guide->daily_rate, 2) }} ETB</strong>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="p-3 bg-light-subtle border rounded-3">
                                    <span class="text-muted small d-block fw-bold">Contact Phone</span>
                                    <strong>{{ $guide->phone_number ?? 'Not set' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h3 class="h6 fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Professional Bio</h3>
                            <p class="text-secondary bg-light-subtle border p-3 rounded-3 mb-0" style="line-height: 1.6;">{{ $guide->bio ?: ($guide->expertise ?: 'No biography provided yet.') }}</p>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <h3 class="h6 fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Languages Spoken</h3>
                                <div class="d-flex flex-wrap gap-1.5">
                                    @forelse($guide->languagesList() as $lang)
                                        <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1">{{ $lang }}</span>
                                    @empty
                                        <span class="small text-muted">No languages listed</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3 class="h6 fw-bold text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Specialties</h3>
                                <div class="d-flex flex-wrap gap-1.5">
                                    @forelse($guide->specialtiesList() as $spec)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">{{ $spec }}</span>
                                    @empty
                                        <span class="small text-muted">No specialties listed</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    @elseif($user->role === 'service_provider' && $user->serviceProvider)
        @php($provider = $user->serviceProvider)
        <section class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 mb-1">Service Provider</span>
                        <h2 class="h4 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $provider->business_name }}</h2>
                    </div>
                    <span class="badge bg-{{ $provider->status === 'approved' ? 'success' : 'secondary' }}-subtle text-{{ $provider->status === 'approved' ? 'success' : 'secondary' }} px-3 py-2 rounded-pill fs-6 fw-bold">
                        Status: {{ ucfirst($provider->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Provider Vertical</span>
                            <strong>{{ str($provider->provider_type)->replace('_', ' ')->title() }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Operational Status</span>
                            <strong class="{{ $provider->isOperational() ? 'text-success' : 'text-danger' }}">
                                {{ $provider->isOperational() ? '● Fully Operational' : '○ Non-Operational' }}
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Verification Notes</span>
                            <span class="text-secondary small">{{ $provider->verification_notes ?: 'No verification notes recorded.' }}</span>
                        </div>
                    </div>
                </div>

                <h3 class="h6 fw-bold text-dark text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Published Services &amp; Inventory</h3>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem;">
                            <tr>
                                <th class="ps-3 py-2.5">Service Name</th>
                                <th class="py-2.5">Category</th>
                                <th class="py-2.5">Destination</th>
                                <th class="pe-3 py-2.5 text-end">Price (ETB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($provider->tourismServices as $svc)
                                <tr>
                                    <td class="ps-3 py-3 fw-bold text-dark">{{ $svc->service_name }}</td>
                                    <td class="py-3"><span class="badge bg-light text-dark border rounded-pill px-2 py-0.5">{{ $svc->category?->category_name ?? 'General' }}</span></td>
                                    <td class="py-3 text-muted small">{{ $svc->destination?->name ?? 'Ethiopia' }}</td>
                                    <td class="pe-3 py-3 fw-bold text-success text-end font-monospace">{{ number_format((float) $svc->price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No services published yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    @elseif($user->role === 'tourism_bureau_officer' && $user->tourismBureauOfficer)
        @php($officer = $user->tourismBureauOfficer)
        <section class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 mb-1">Regulatory Authority</span>
                <h2 class="h4 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">Tourism Bureau Officer #{{ $officer->officer_id }}</h2>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Officer ID</span>
                            <strong class="font-monospace">#{{ $officer->officer_id }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Assigned Destinations</span>
                            <strong>{{ $officer->destinations->pluck('name')->join(', ') ?: 'National Tourism Scope' }}</strong>
                        </div>
                    </div>
                </div>

                <h3 class="h6 fw-bold text-dark text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Managed Regional Destinations</h3>
                <div class="row g-3">
                    @forelse($officer->destinations as $dest)
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light-subtle">
                                <h4 class="h6 fw-bold mb-1 text-dark">📍 {{ $dest->name }}</h4>
                                <p class="small text-muted mb-0">{{ $dest->location }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No specific destinations assigned.</div>
                    @endforelse
                </div>
            </div>
        </section>

    @elseif($user->role === 'tourist' && $user->tourist)
        @php($tourist = $user->tourist)
        <section class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1 mb-1">Traveler Account</span>
                <h2 class="h4 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $tourist->full_name }}</h2>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Full Name</span>
                            <strong>{{ $tourist->full_name }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Nationality</span>
                            <strong>{{ $tourist->nationality }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light-subtle border rounded-3">
                            <span class="text-muted small d-block fw-bold">Saved Smart Trips</span>
                            <strong class="text-success">{{ $tourist->trips->count() }} Trips</strong>
                        </div>
                    </div>
                </div>

                @if($tourist->trips->isNotEmpty())
                    <h3 class="h6 fw-bold text-dark text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.05em;">Saved Smart Trips</h3>
                    <div class="row g-3">
                        @foreach($tourist->trips as $t)
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light-subtle">
                                    <h4 class="h6 fw-bold mb-1 text-dark">🧳 {{ $t->title }}</h4>
                                    <p class="small text-muted mb-0">
                                        {{ $t->start_date->format('M d') }} – {{ $t->end_date->format('M d, Y') }} &bull; {{ $t->destinations->pluck('name')->join(', ') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    @elseif($user->role === 'administrator')
        <section class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white p-4 border-bottom">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 mb-1">System Administration</span>
                <h2 class="h4 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">Administrator Access Privileges</h2>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-0" style="line-height: 1.6;">This account holds complete administrative oversight across user security, subscription configurations, governance reviews, and the audit log.</p>
            </div>
        </section>
    @endif
</div>
@endsection
