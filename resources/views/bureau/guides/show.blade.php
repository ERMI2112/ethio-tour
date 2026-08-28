@extends('layouts.app')

@php
    $guideDisplayName = $guide->full_name ?: ($guide->user?->email ?? 'Tour Guide');
@endphp

@section('title', 'Guide Verification Review — ' . $guideDisplayName)

@section('content')
<div class="container py-4 py-lg-5">
    <nav class="mb-3" aria-label="Breadcrumb">
        <a href="{{ route('bureau.guides.index') }}">Guide verification</a> <span class="text-muted">/ Review</span>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-semibold mb-1">Regulatory review</p>
            <h1 class="h2 mb-1">{{ $guideDisplayName }}</h1>
            <p class="text-muted mb-0">Evaluate license credentials, languages, experience, and photo verification.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('bureau.guides.index') }}">Back to queue</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Identity & Photo --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0 fw-bold">Guide identity</h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-4 align-items-center mb-4 pb-3 border-bottom">
                        <img src="{{ $guide->profileImageUrl() }}"
                             alt="{{ $guideDisplayName }}"
                             class="rounded-circle border border-2 shadow-sm"
                             style="width: 85px; height: 85px; object-fit: cover;"
                             width="85" height="85" decoding="async">
                        <div>
                            <h3 class="h5 mb-1 text-dark fw-bold">{{ $guideDisplayName }}</h3>
                            <div class="text-muted small">{{ $guide->user?->email }}</div>
                            @if($guide->phone_number)
                                <div class="text-muted small">📞 {{ $guide->phone_number }}</div>
                            @endif
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted small text-uppercase">Account</dt>
                        <dd class="col-sm-8">{{ $guide->user?->email ?? 'Not provided' }}</dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Registration</dt>
                        <dd class="col-sm-8">{{ $guide->created_at?->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Primary Region</dt>
                        <dd class="col-sm-8">{{ $guide->destination?->name ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Professional Credentials --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0 fw-bold">Professional information</h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted small text-uppercase">License</dt>
                        <dd class="col-sm-8"><span class="badge bg-dark text-white">{{ $guide->license_number ?: 'Not provided' }}</span></dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Experience</dt>
                        <dd class="col-sm-8">{{ $guide->years_of_experience ? $guide->years_of_experience . ' years' : 'Not specified' }}</dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Languages</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($guide->languagesList() as $lang)
                                    <span class="badge bg-light text-dark border">{{ $lang }}</span>
                                @endforeach
                            </div>
                        </dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Daily Rate</dt>
                        <dd class="col-sm-8 fw-semibold text-dark">{{ $guide->daily_rate ? number_format((float) $guide->daily_rate, 2) . ' ETB / day' : 'Not configured' }}</dd>

                        <dt class="col-sm-4 text-muted small text-uppercase">Expertise</dt>
                        <dd class="col-sm-8">{{ $guide->expertise ?: 'Not provided' }}</dd>

                        @if($guide->bio)
                            <dt class="col-sm-4 text-muted small text-uppercase">Biography</dt>
                            <dd class="col-sm-8 text-secondary small">{{ $guide->bio }}</dd>
                        @endif

                        <dt class="col-sm-4 text-muted small text-uppercase">Availability</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }}">
                                {{ $guide->availability_status ? ucfirst($guide->availability_status) : 'Not provided' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Verification State & Decisions --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0 fw-bold">Verification state</h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <span class="fw-semibold">Bureau Status</span>
                        <x-ui.status-badge :status="$guide->verification_status" />
                    </div>

                    <p class="small text-muted mb-3">
                        <strong>Existing notes:</strong> {{ $guide->verification_notes ?: 'None recorded' }}
                    </p>

                    @if($guide->verification_status === 'pending')
                        <form method="POST" action="{{ route('bureau.guides.decide', $guide) }}" data-confirm="Record this verification decision?">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="verification_notes">Decision notes</label>
                                <textarea class="form-control @error('verification_notes') is-invalid @enderror"
                                          id="verification_notes"
                                          name="verification_notes"
                                          rows="4"
                                          placeholder="Record verification check notes, license certificate status, or reasons for decision...">{{ old('verification_notes') }}</textarea>
                                @error('verification_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-success px-4" name="decision" value="approve">Verify guide</button>
                                <button class="btn btn-outline-danger" name="decision" value="reject">Reject guide</button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-secondary small mb-0">
                            This guide has already received a Bureau verification decision.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3"><h2 class="h5 mb-0 fw-bold">Verification documents</h2></div>
                <div class="list-group list-group-flush">
                    @forelse($guide->verificationDocuments as $document)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div><strong>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</strong><small class="d-block text-muted">{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</small></div>
                                <div class="d-flex align-items-center gap-2"><x-ui.status-badge :status="$document->status" /><a class="btn btn-sm btn-outline-secondary" href="{{ route('bureau.guides.documents.download', [$guide, $document]) }}">Download</a></div>
                            </div>
                            @if($document->status === 'pending')
                                <form method="POST" action="{{ route('bureau.guides.documents.decide', [$guide, $document]) }}" class="d-flex gap-2 mt-3">@csrf @method('PATCH')<input class="form-control" name="review_notes" placeholder="Review notes"><button class="btn btn-success" name="decision" value="approved">Approve</button><button class="btn btn-outline-danger" name="decision" value="rejected">Reject</button></form>
                            @elseif($document->review_notes)
                                <small class="d-block text-muted mt-2">Review notes: {{ $document->review_notes }}</small>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No verification documents uploaded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
