@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
<div class="container py-4 py-lg-5 tourist-reviews-page">
    <div class="tourist-page-header d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4" data-aos="fade-up">
        <div>
            <p class="text-uppercase text-success small fw-semibold mb-1">Traveler workspace</p>
            <h1 class="h2 mb-1">My Reviews</h1>
            <p class="text-muted mb-0">Review completed experiences and revisit your submitted feedback.</p>
        </div>
        <a class="btn btn-outline-success" href="{{ route('tourist.dashboard') }}">Dashboard</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <section class="tourist-side-card">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Submitted reviews</h2></div>
                <div class="card-body">
                    @forelse ($reviews as $review)
                        <article class="border-bottom py-3">
                            <div class="d-flex justify-content-between gap-3"><x-reviews.star-rating :rating="$review->rating" /><small class="text-muted">{{ $review->review_date?->format('M j, Y') }}</small></div>
                            <p class="mb-1 mt-2">{{ $review->comment }}</p>
                            <a class="small" href="{{ route('tourist.reservations.show', $review->booking) }}">View booking</a>
                        </article>
                    @empty
                        <x-ui.empty-state title="No reviews yet" message="Your submitted reviews will appear here." />
                    @endforelse
                    @if ($reviews->hasPages())
                        <div class="pt-3">{{ $reviews->links() }}</div>
                    @endif
                </div>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="tourist-side-card">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Ready to review</h2></div>
                <div class="card-body">
                    @forelse ($reviewOpportunities as $booking)
                        <div class="d-flex justify-content-between align-items-center gap-2 border-bottom py-2"><span class="small">Booking #BK-{{ sprintf('%05d', $booking->booking_id) }}</span><a class="btn btn-sm btn-primary" href="{{ route('tourist.reservations.show', $booking) }}">Write review</a></div>
                    @empty
                        <p class="small text-muted mb-0">Completed bookings will appear here when they&#039;re ready for review.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
