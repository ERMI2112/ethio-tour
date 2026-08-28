@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5 tourist-profile-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" data-aos="fade-up">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-compass me-1"></i> Traveler Workspace
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                    Account Settings
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Edit Profile
            </h1>
            <p class="text-secondary mb-0 small">
                Update only the personal details supported by your tourist profile.
            </p>
        </div>

        <div>
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" href="{{ route('tourist.profile') }}">
                &larr; Back to profile
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden" data-aos="fade-up">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-person-gear text-success me-1.5"></i> Profile Information
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tourist.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3.5">
                            <label class="form-label text-dark fw-semibold small" for="full_name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control rounded-3 @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $tourist->full_name) }}" placeholder="e.g. Abebe Bekele" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Your primary name displayed on bookings and tour guide reservations.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-dark fw-semibold small" for="nationality">Nationality <span class="text-danger">*</span></label>
                            <input class="form-control rounded-3 @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality', $tourist->nationality) }}" placeholder="e.g. Ethiopian" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Helps service providers and tour guides prepare suitable services.</div>
                        </div>

                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <button class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs" type="submit">
                                <i class="bi bi-check2 me-1"></i> Save Changes
                            </button>
                            <a class="btn btn-light border rounded-pill px-3 text-secondary" href="{{ route('tourist.profile') }}">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden h-100" data-aos="fade-up" data-aos-delay="70">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-info-circle text-primary me-1.5"></i> Information
                    </h2>
                </div>
                <div class="card-body p-4 small text-muted">
                    <p class="mb-3">
                        <strong class="text-dark d-block mb-1">Email Address:</strong>
                        <span class="font-monospace">{{ $tourist->user->email }}</span>
                    </p>
                    <p class="mb-3">
                        To change your account password or registered email, please visit the global <a href="{{ route('account') }}" class="text-decoration-none fw-semibold">Account settings</a>.
                    </p>
                    <div class="p-3 rounded-3 bg-light border">
                        <i class="bi bi-shield-lock text-success me-1"></i>
                        <span>Platform data protection ensures your personal details are shared only with providers of your confirmed bookings.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
