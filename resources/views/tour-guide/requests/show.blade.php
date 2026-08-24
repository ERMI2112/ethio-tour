@extends('layouts.app')

@section('title', 'Guide Booking Request #BK-' . sprintf('%05d', $booking->booking_id))

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                            INCOMING BOOKING REQUEST
                        </span>
                        <span class="text-muted small font-monospace">#BK-{{ sprintf('%05d', $booking->booking_id) }}</span>
                    </div>
                    <h1 class="h3 fw-bold text-dark mb-0">Booking Request from {{ $booking->tourist?->full_name ?? 'Traveler' }}</h1>
                    <p class="text-secondary small mb-0">Requested on {{ $booking->booking_date?->format('F d, Y \a\t g:i A') }}</p>
                </div>
                <div>
                    <x-ui.status-badge :status="$booking->status" />
                </div>
            </div>

            @include('layouts.partials.flash-messages')

            {{-- Tourist Goals & Journey Details Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-compass-fill text-primary me-1.5"></i> Journey Specifications &amp; Tourist Objectives
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Tourist Name</span>
                            <strong class="text-dark fs-6">{{ $booking->tourist?->full_name }}</strong>
                            <span class="text-muted small d-block">{{ $booking->tourist?->nationality ?: 'International Traveler' }}</span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Party Size</span>
                            <strong class="text-dark fs-6">{{ $booking->tourGuideReservation?->number_of_tourists }} Person(s)</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Tour Start Date</span>
                            <strong class="text-dark">{{ $booking->tourGuideReservation?->start_date?->format('l, F d, Y') }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Tour End Date</span>
                            <strong class="text-dark">{{ $booking->tourGuideReservation?->end_date?->format('l, F d, Y') }}</strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Preferred Tour Language</span>
                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
                                🗣️ {{ $booking->tourGuideReservation?->language_preference ?: 'English (Default)' }}
                            </span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Total Estimated Fee</span>
                            <strong class="text-dark font-monospace fs-6">{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency ?? 'ETB' }}</strong>
                        </div>
                    </div>

                    {{-- Special Interests --}}
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">
                            🎯 Tourist Special Interests &amp; Focus Topics
                        </span>
                        @if($booking->tourGuideReservation?->special_interests)
                            <div class="d-flex flex-wrap gap-1.5 mt-1">
                                @foreach(explode(',', $booking->tourGuideReservation->special_interests) as $interest)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                        ● {{ trim($interest) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="small text-muted mb-0">General historical and cultural tour interest.</p>
                        @endif
                    </div>

                    {{-- What Tourist Wants to Achieve (Trip Goals & Notes) --}}
                    <div class="p-3 rounded-3 bg-light border">
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">
                            📝 Tourist Goals &amp; Special Requests (What they want to achieve)
                        </span>
                        @if($booking->tourGuideReservation?->notes)
                            <p class="small text-dark mb-0" style="line-height: 1.6; white-space: pre-line;">{{ $booking->tourGuideReservation->notes }}</p>
                        @else
                            <p class="small text-muted mb-0">No special goals or custom requests provided by tourist.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Decision Action Card --}}
            @if ($booking->status === 'pending')
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white p-3.5 border-bottom">
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-check2-circle text-success me-1.5"></i> Request Decision
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        @if (auth()->user()->tourGuide?->verification_status === 'verified')
                            <p class="text-secondary small mb-3">
                                Accepting this booking will block your calendar for the requested dates and notify {{ $booking->tourist?->full_name }} to proceed with payment.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('tour-guide.requests.accept', $booking) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-vn-emerald fw-bold rounded-pill px-4 py-2 shadow-sm" type="submit">
                                        <i class="bi bi-check-lg me-1"></i> Accept Booking Request
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('tour-guide.requests.reject', $booking) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-danger fw-bold rounded-pill px-4 py-2" type="submit">
                                        <i class="bi bi-x-lg me-1"></i> Decline Request
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-warning-subtle border-0 rounded-3 small p-3 mb-3">
                                Only a Bureau-verified tour guide can accept incoming booking requests. You may decline this request.
                            </div>
                            <form method="POST" action="{{ route('tour-guide.requests.reject', $booking) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger fw-bold rounded-pill px-4 py-2" type="submit">
                                    Decline Request
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
