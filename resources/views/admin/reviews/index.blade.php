@extends('layouts.app')

@section('title', 'Review Moderation · Administrator')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Review Moderation</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;" role="status"></span>
                    ADMINISTRATOR WORKSPACE
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Review moderation</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Review submitted reviews and remove any that violate platform standards.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-star-half me-1"></i> Quality &amp; Trust Enforcement
            </span>
        </div>
    </div>

    @if($reviews->isEmpty())
        <x-ui.empty-state title="No reviews" message="Submitted reviews will appear here for moderation." />
    @else
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                    <i class="bi bi-star-fill text-warning me-1.5"></i> Submitted Reviews &amp; Ratings
                </h2>
                <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                    Total: {{ $reviews->total() }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                        <tr>
                            <th class="ps-4 py-3">Reviewer</th>
                            <th class="py-3">Target</th>
                            <th class="py-3">Rating</th>
                            <th class="py-3">Comment</th>
                            <th class="py-3">Date</th>
                            <th class="pe-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td class="ps-4 py-3.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($review->tourist?->full_name ?? 'U', 0, 2)) }}
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $review->tourist?->full_name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5">
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        {{ $review->booking?->tourGuide ? 'Tour Guide' : ($review->booking?->tourismService?->service_name ?? 'Booking') }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    <x-reviews.star-rating :rating="$review->rating" />
                                </td>
                                <td class="py-3.5 text-secondary small" style="max-width: 320px;">
                                    {{ Str::limit($review->comment, 100) }}
                                </td>
                                <td class="py-3.5 text-muted small font-monospace">{{ $review->review_date?->format('Y-m-d') }}</td>
                                <td class="pe-4 py-3.5 text-end">
                                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="Remove this review?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                                            <i class="bi bi-trash3 me-1"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reviews->hasPages())
                <div class="p-3 border-top bg-light-subtle d-flex justify-content-end">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
