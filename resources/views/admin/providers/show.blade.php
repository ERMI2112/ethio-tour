@extends('layouts.app')

@section('title', 'Provider Governance Review · Administrator')

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
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;" role="status"></span>
                    GOVERNANCE REVIEW
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">{{ $provider->business_name }}</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                {{ str_replace('_',' ',ucfirst($provider->provider_type)) }} &bull; {{ $provider->user?->email }}
            </p>
        </div>
        <div>
            <a class="btn btn-light border rounded-pill px-3.5 py-2 fw-semibold text-muted" href="{{ route('admin.providers.index') }}">
                <i class="bi bi-arrow-left me-1"></i> Back to providers
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Provider & Account Details Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-building-fill text-primary me-1.5"></i> Provider and account
                    </h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 g-3">
                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Business</dt>
                        <dd class="col-sm-8 fw-bold text-dark fs-6">{{ $provider->business_name }}</dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Provider type</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace">
                                {{ str_replace('_',' ',ucfirst($provider->provider_type)) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Account</dt>
                        <dd class="col-sm-8">
                            @if($provider->user?->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    ● Active
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                    ○ Inactive
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Registered</dt>
                        <dd class="col-sm-8 text-secondary font-monospace">{{ $provider->created_at?->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Verification Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-file-earmark-text-fill text-info me-1.5"></i> Verification notes
                    </h2>
                </div>
                <div class="card-body p-4 text-secondary" style="line-height: 1.6;">
                    {{ $provider->verification_notes ?: 'No verification notes have been recorded.' }}
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Governance State Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-shield-check text-success me-1.5"></i> Governance state
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="fw-semibold text-dark">Bureau verification</span>
                        <x-ui.status-badge :status="$provider->verification_status" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-semibold text-dark">Platform status</span>
                        <x-ui.status-badge :status="$provider->status" />
                    </div>

                    @if($provider->verification_status === 'verified')
                        <div class="alert alert-info-subtle border-0 rounded-3 small text-secondary p-3 mb-4">
                            <i class="bi bi-info-circle-fill text-info me-1"></i> Administrator actions change only the platform status. Bureau verification is not editable here.
                        </div>

                        <form method="POST" action="{{ route('admin.providers.status',$provider) }}" class="d-flex flex-wrap gap-2" data-confirm="Apply this provider status change?">
                            @csrf @method('PATCH')
                            @if($provider->status === 'pending')
                                <button class="btn btn-vn-emerald flex-grow-1 fw-bold rounded-pill py-2 shadow-sm" name="status" value="approved">
                                    <i class="bi bi-check2-circle me-1"></i> Approve for platform
                                </button>
                                <button class="btn btn-outline-danger flex-grow-1 fw-bold rounded-pill py-2" name="status" value="rejected">
                                    <i class="bi bi-x-circle me-1"></i> Reject activation
                                </button>
                            @elseif($provider->status === 'approved')
                                <button class="btn btn-outline-warning w-100 fw-bold rounded-pill py-2" name="status" value="suspended">
                                    <i class="bi bi-pause-circle me-1"></i> Suspend operations
                                </button>
                            @elseif($provider->status === 'suspended')
                                <button class="btn btn-vn-emerald w-100 fw-bold rounded-pill py-2" name="status" value="approved">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reinstate operations
                                </button>
                            @endif
                        </form>
                    @else
                        <div class="alert alert-warning-subtle border-0 rounded-3 small text-secondary p-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> This provider is not eligible for Administrator activation until the Bureau marks it verified.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
