@extends('layouts.app')

@section('title', 'Tour Guide Reviews & Ratings')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reviews</li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <p class="text-muted small text-uppercase mb-1">Tourist Feedback</p>
                    <h1 class="h2 mb-0 fw-bold">Ratings &amp; Reviews</h1>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center h-100 bg-white">
                        <span class="text-muted small text-uppercase fw-semibold mb-1">Overall Rating</span>
                        <div class="display-4 fw-bold text-warning mb-1">
                            {{ $averageRating !== null ? number_format((float) $averageRating, 1) : '—' }}
                        </div>
                        <div class="text-warning mb-1">
                            @if($averageRating !== null)
                                {{ str_repeat('★', (int) round($averageRating)) }}<span class="text-muted">{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</span>
                            @else
                                <span class="text-muted">No ratings yet</span>
                            @endif
                        </div>
                        <span class="text-muted small">Based on {{ $totalReviews }} {{ $totalReviews === 1 ? 'review' : 'reviews' }}</span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 h-100 bg-white">
                        <h2 class="h6 text-uppercase text-muted fw-bold mb-3">Rating Breakdown</h2>
                        <div class="d-flex flex-column gap-2">
                            @for ($stars = 5; $stars >= 1; $stars--)
                                @php
                                    $count = $ratingDistribution[$stars] ?? 0;
                                    $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2 small">
                                    <span class="text-muted text-nowrap" style="width: 45px;">{{ $stars }} ★</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="text-muted text-end text-nowrap" style="width: 50px;">{{ $count }} ({{ $pct }}%)</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Review List --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0 fw-bold">Recent Verified Reviews</h2>
                    <span class="badge bg-light text-dark border">{{ $totalReviews }} total</span>
                </div>
                <div class="card-body p-4">
                    @if($reviews->isEmpty())
                        <div class="text-center py-5">
                            <div class="display-6 text-muted mb-2">★ ★ ★ ★ ★</div>
                            <h3 class="h5 text-dark mb-1">No reviews yet</h3>
                            <p class="text-muted small mb-0">Reviews from tourists will appear here automatically after they complete booked guided tours.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-4">
                            @foreach($reviews as $review)
                                <article class="pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="text-warning mb-1">
                                                {{ str_repeat('★', (int) $review->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - (int) $review->rating) }}</span>
                                                <span class="fw-bold text-dark ms-1">{{ $review->rating }}.0</span>
                                            </div>
                                            <h3 class="h6 mb-0 text-dark fw-bold">
                                                {{ $review->tourist?->full_name ?? $review->tourist?->user?->name ?? 'Verified Tourist' }}
                                            </h3>
                                        </div>
                                        <span class="text-muted small">{{ $review->review_date?->format('M j, Y') ?? $review->created_at?->format('M j, Y') }}</span>
                                    </div>
                                    <p class="text-secondary mb-2">
                                        “{{ $review->comment }}”
                                    </p>
                                    @if($review->booking)
                                        <div class="badge bg-light text-secondary border small">
                                            Booking #{{ $review->booking->booking_id }} &bull; Completed Tour
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
