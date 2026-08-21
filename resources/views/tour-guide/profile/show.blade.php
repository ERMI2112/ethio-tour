@extends('layouts.app')

@section('title', ($guide->full_name ?: 'Tour Guide') . ' — Professional Profile')

@section('content')
@php
    $verificationClass = match ($guide->verification_status) {
        'verified' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        default => 'text-bg-warning text-dark',
    };
    $guideName = $guide->full_name ?: $guide->user?->email;
    $reviewCount = \App\Models\Review::whereHas('booking', fn ($q) => $q->where('guide_id', $guide->guide_id))->count();
    $avgRating = \App\Models\Review::whereHas('booking', fn ($q) => $q->where('guide_id', $guide->guide_id))->avg('rating');
@endphp
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>

            {{-- ═══════════════════════════════════════════════════
                 PROFESSIONAL HERO BANNER
                 ═══════════════════════════════════════════════════ --}}
            <div class="card border-0 shadow mb-4 overflow-hidden">
                {{-- Cover background --}}
                <div class="position-relative" style="background: linear-gradient(135deg, #0a2e1c 0%, #051a10 60%, #0d3824 100%); min-height: 200px;">
                    {{-- Decorative pattern overlay --}}
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><circle cx=%2230%22 cy=%2230%22 r=%221%22 fill=%22rgba(255,255,255,0.03)%22/></svg>'); opacity: 0.5;"></div>

                    {{-- Hero content --}}
                    <div class="position-relative p-4 p-md-5">
                        <div class="row align-items-end g-4">
                            {{-- Large Professional Avatar --}}
                            <div class="col-auto">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ $guide->profileImageUrl() }}"
                                         alt="{{ $guideName }}"
                                         class="rounded-circle shadow-lg"
                                         style="width: 140px; height: 140px; object-fit: cover; border: 4px solid rgba(255,193,7,0.9);">
                                    {{-- Availability indicator --}}
                                    <span class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center rounded-circle shadow {{ $guide->availability_status === 'available' ? 'bg-success' : 'bg-secondary' }}"
                                          style="width: 28px; height: 28px; border: 3px solid #0a2e1c;"
                                          title="{{ ucfirst($guide->availability_status) }}">
                                        <span class="visually-hidden">{{ $guide->availability_status }}</span>
                                    </span>
                                </div>
                            </div>

                            {{-- Name + Meta --}}
                            <div class="col">
                                {{-- Status badges --}}
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    @if($guide->verification_status === 'verified')
                                        <span class="badge bg-success bg-opacity-90 px-3 py-2 d-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                            Bureau Verified
                                        </span>
                                    @else
                                        <span class="badge {{ $verificationClass }} px-3 py-2">
                                            {{ ucfirst($guide->verification_status) }}
                                        </span>
                                    @endif
                                    <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2">
                                        License {{ $guide->license_number }}
                                    </span>
                                    @if($guide->destination)
                                        <span class="badge bg-warning text-dark fw-semibold px-3 py-2">
                                            📍 {{ $guide->destination->name }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Full name --}}
                                <h1 class="text-white mb-1 fw-bold" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                                    {{ $guideName }}
                                </h1>

                                {{-- Subtitle credentials --}}
                                <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                                    @if($guide->years_of_experience)
                                        <span class="text-white text-opacity-75 small d-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                            {{ $guide->years_of_experience }} years experience
                                        </span>
                                    @endif
                                    @if($avgRating)
                                        <span class="text-warning small fw-semibold">
                                            ★ {{ number_format((float)$avgRating, 1) }} ({{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }})
                                        </span>
                                    @endif
                                    <span class="text-white text-opacity-50 small">
                                        Member since {{ $guide->created_at?->format('M Y') ?? '2026' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Action button --}}
                            <div class="col-12 col-md-auto text-md-end">
                                <a class="btn btn-warning fw-bold text-dark px-4 py-2 shadow-sm" href="{{ route('tour-guide.profile.edit') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                    Edit profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 PROFILE DETAILS — TWO-COLUMN LAYOUT
                 ═══════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                {{-- LEFT: Biography & Expertise --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="text-primary" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>
                            <h2 class="h5 mb-0 fw-bold text-dark">Professional Biography</h2>
                        </div>
                        <div class="card-body p-4">
                            @if($guide->bio)
                                <div class="text-secondary mb-4" style="line-height: 1.7;">
                                    {{ $guide->bio }}
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-3 text-muted small mb-4">
                                    No detailed biography added yet. Click <a href="{{ route('tour-guide.profile.edit') }}" class="text-primary fw-semibold">Edit profile</a> to add your background and welcome tourists.
                                </div>
                            @endif

                            <h3 class="h6 text-uppercase text-muted fw-bold mb-2 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                                Areas of Expertise
                            </h3>
                            <p class="text-dark mb-4" style="line-height: 1.6;">{{ $guide->expertise }}</p>

                            <h3 class="h6 text-uppercase text-muted fw-bold mb-3 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                Specialties
                            </h3>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @foreach($guide->specialtiesList() as $specialty)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 fw-medium" style="font-size: 0.8rem;">
                                        ★ {{ $specialty }}
                                    </span>
                                @endforeach
                            </div>

                            <h3 class="h6 text-uppercase text-muted fw-bold mb-3 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793zm5 4a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                                Languages Spoken
                            </h3>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($guide->languagesList() as $lang)
                                    <span class="badge bg-light text-dark border px-3 py-2 fw-medium d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                        🗣 {{ $lang }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Business & Verification Specs --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="text-success" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>
                            <h2 class="h5 mb-0 fw-bold text-dark">Operational Details</h2>
                        </div>
                        <div class="card-body p-4">
                            <dl class="mb-0">
                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Email</dt>
                                    <dd class="fw-medium mb-0 text-end">{{ $guide->user?->email }}</dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Phone</dt>
                                    <dd class="fw-medium mb-0 text-end">{{ $guide->phone_number ?: 'Not provided' }}</dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Daily Guide Rate</dt>
                                    <dd class="fw-bold text-dark mb-0 text-end" style="font-size: 1.05rem;">
                                        {{ $guide->daily_rate === null ? 'Not set' : number_format((float) $guide->daily_rate, 2).' ETB / day' }}
                                    </dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Experience</dt>
                                    <dd class="fw-medium mb-0 text-end">
                                        {{ $guide->years_of_experience ? $guide->years_of_experience . ' years certified' : 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Primary Region</dt>
                                    <dd class="fw-medium mb-0 text-end">
                                        {{ $guide->destination?->name ?? 'Northern Ethiopia' }}
                                    </dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">License Number</dt>
                                    <dd class="mb-0 text-end">
                                        <span class="badge bg-dark text-white px-2 py-1">{{ $guide->license_number }}</span>
                                        <span class="badge text-bg-light border text-dark ms-1">Platform controlled</span>
                                    </dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Availability</dt>
                                    <dd class="mb-0">
                                        <span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }} px-3 py-1">
                                            {{ ucfirst($guide->availability_status) }}
                                        </span>
                                    </dd>
                                </div>

                                <div class="d-flex justify-content-between align-items-start py-2">
                                    <dt class="text-muted small text-uppercase fw-semibold mb-0">Verification status</dt>
                                    <dd class="mb-0 text-end">
                                        <span class="badge {{ $verificationClass }} px-3 py-1">
                                            {{ ucfirst($guide->verification_status) }}
                                        </span>
                                        <span class="text-muted d-block mt-1" style="font-size: 0.7rem;">Bureau controlled</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="card-footer bg-light small text-muted p-3 border-top">
                            You can update expertise, availability, bio, languages, experience, and daily rate. License, role, and verification decisions cannot be changed here.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
