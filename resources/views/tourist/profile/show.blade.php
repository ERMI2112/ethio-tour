@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5 tourist-profile-page">
    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" data-aos="fade-up">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-compass me-1"></i> Traveler Workspace
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                    My Profile
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                My Profile
            </h1>
            <p class="text-secondary mb-0 small">
                Manage the personal details and identity used for your Ethio Tour traveler account.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('tourist.dashboard') }}">
                <i class="bi bi-grid me-1"></i> Dashboard
            </a>
            <a class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" href="{{ route('tourist.profile.edit') }}">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    {{-- Identity Overview Banner --}}
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4" data-aos="fade-up">
        <div class="card-body p-4 p-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" style="width: 56px; height: 56px; font-size: 1.25rem;">
                    {{ strtoupper(substr($tourist->full_name ?: $tourist->user?->email ?: 'T', 0, 2)) }}
                </div>
                <div>
                    <h2 class="h5 fw-bold mb-0 text-dark">{{ $tourist->full_name ?: 'Traveler' }}</h2>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                        <span class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $tourist->user->email }}</span>
                        @if($tourist->nationality)
                            <span class="text-muted small">&bull;</span>
                            <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $tourist->nationality }}
                            </span>
                        @endif
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>Verified Traveler
                        </span>
                    </div>
                </div>
            </div>
            <div>
                <a class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('tourist.profile.edit') }}">
                    <i class="bi bi-pencil-square me-1"></i> Update Details
                </a>
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden h-100">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-person-badge text-success me-1.5"></i> Personal Information
                    </h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 gy-3">
                        <dt class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Full name</dt>
                        <dd class="col-sm-8 text-dark fw-bold mb-0">{{ $tourist->full_name ?: 'Not set' }}</dd>

                        <dt class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Nationality</dt>
                        <dd class="col-sm-8 text-dark mb-0">{{ $tourist->nationality ?: 'Not specified' }}</dd>

                        <dt class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Account Email</dt>
                        <dd class="col-sm-8 text-dark font-monospace small mb-0">{{ $tourist->user->email }}</dd>

                        <dt class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">Platform Role</dt>
                        <dd class="col-sm-8 text-dark mb-0">
                            <span class="badge bg-light text-dark border">Tourist / Traveler</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden h-100">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-shield-check text-primary me-1.5"></i> Account Status & Governance
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="small text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Status</div>
                        <x-ui.status-badge status="active" />
                    </div>
                    <p class="small text-muted mb-3">
                        Your account role and active verification status are managed centrally by the platform and cannot be escalated from this profile.
                    </p>
                    <div class="p-3 rounded-3 bg-light border small text-muted">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <span>Need to change your password or login credentials? Visit your <a href="{{ route('account') }}" class="text-decoration-none fw-semibold">Account settings</a>.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
