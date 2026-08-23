@extends('layouts.app')

@section('title', 'Guest Feedback & Reviews · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('restaurant.dashboard') }}" class="text-success text-decoration-none fw-semibold">Restaurant Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Guest feedback</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    ⭐ Diner Ratings &amp; Trust
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">Guest Feedback</h1>
            <p class="text-secondary mb-0 small">
                Verified traveler reviews and dining experiences for {{ $provider->business_name }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 px-3.5 bg-white border rounded-4 shadow-sm text-center">
                <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Average Rating</div>
                <div class="h4 fw-bold text-dark mb-0 d-flex align-items-center justify-content-center gap-1">
                    <span>{{ number_format($averageRating, 1) }}</span>
                    <span class="text-warning fs-5">★</span>
                </div>
            </div>
            <div class="p-2.5 px-3.5 bg-white border rounded-4 shadow-sm text-center">
                <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Total Reviews</div>
                <div class="h4 fw-bold text-dark mb-0 font-monospace">{{ $totalReviews }}</div>
            </div>
        </div>
    </div>

    @if($reviews->isEmpty())
        <x-ui.empty-state title="No diner reviews yet" message="When guests complete their reservations and submit feedback, their ratings and reviews will appear here." />
    @else
        <div class="row g-4">
            @foreach($reviews as $review)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($review->tourist?->full_name ?? 'G', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $review->tourist?->full_name ?? 'Verified Guest' }}</div>
                                        <div class="small text-muted font-monospace">{{ $review->review_date?->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 fw-bold">
                                    ★ {{ number_format($review->rating, 1) }}
                                </span>
                            </div>

                            @if($review->booking?->tourismService)
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                        🍽️ {{ $review->booking->tourismService->service_name }}
                                    </span>
                                </div>
                            @endif

                            <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                &ldquo;{{ $review->comment ?: 'Outstanding traditional gastronomy experience and impeccable service.' }}&rdquo;
                            </p>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                ✓ Verified Diner
                            </span>
                            <span class="small text-muted font-monospace">
                                Booking #{{ $review->booking_id }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($reviews->hasPages())
            <div class="p-3 mt-4 border-top d-flex justify-content-end">
                {{ $reviews->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
