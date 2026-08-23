@extends('layouts.app')

@section('title', 'Hotel Profile · ' . $provider->business_name)

@section('content')
@php
    $user = $provider->user;
@endphp
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}" class="text-success text-decoration-none fw-semibold">Hotel Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    🏨 Sovereign Lodging Registry
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    ID #HTL-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">{{ $provider->business_name }}</h1>
            <p class="text-secondary mb-0 small">
                Verified Hospitality Management &bull; {{ $user?->email }}
            </p>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-vn-navy btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm" href="{{ route('hotel.profile.edit') }}">
                <i class="bi bi-pencil-square me-1"></i> Edit profile
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column: Manager & Business Credentials --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-shield-check text-success me-1.5"></i> Business profile
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 58px; height: 58px;">
                            🏨
                        </div>
                        <div>
                            <h3 class="h5 fw-bold mb-0 text-dark">{{ $provider->business_name }}</h3>
                            <span class="text-muted small">General Manager: <strong>{{ $user?->name ?: 'Abnet Kebede' }}</strong></span>
                        </div>
                    </div>

                    <dl class="row mb-0 g-3">
                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Business name</dt>
                        <dd class="col-sm-7 fw-bold text-dark">
                            {{ $provider->business_name }}
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 small ms-1">editable</span>
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Provider type</dt>
                        <dd class="col-sm-7 text-capitalize">
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                {{ str_replace('_', ' ', $provider->provider_type) }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Verification status</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-{{ $provider->status === 'approved' ? 'success' : 'secondary' }}-subtle text-{{ $provider->status === 'approved' ? 'success' : 'secondary' }} border rounded-pill px-2.5 py-1 fw-bold">
                                ● {{ ucfirst($provider->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Account state</dt>
                        <dd class="col-sm-7">
                            @if ($user?->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="card-footer bg-light-subtle p-3 border-top d-flex justify-content-between align-items-center">
                    <p class="small text-muted mb-0">Only your business name is editable. Provider type, verification status and account state are controlled by the platform.</p>
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" href="{{ route('hotel.profile.edit') }}">Edit business name</a>
                </div>
            </div>
        </div>

        {{-- Right Column: Heritage Lodging Showcase & Photo --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-image text-primary me-1.5"></i> Heritage Lodging Portfolio
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="rounded-4 overflow-hidden mb-3 border shadow-sm position-relative" style="height: 180px;">
                        <img src="/images/services/hotel-suite.jpg" alt="Heritage Hotel Suite" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-50 text-white backdrop-blur">
                            <span class="small fw-bold">Authentic Ethiopian Hospitality &bull; Sovereign Hotel Tier</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border mb-2">
                        <span class="text-dark small fw-bold">Published Suite Types</span>
                        <strong class="text-success font-monospace">{{ $provider->tourismServices()->count() }} Categories</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light-subtle border">
                        <span class="text-dark small fw-bold">Active Physical Rooms</span>
                        <strong class="text-primary font-monospace">{{ \App\Models\HotelRoom::whereHas('hotelRoomType.tourismService', fn ($q) => $q->where('provider_id', $provider->provider_id))->count() }} Rooms</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
