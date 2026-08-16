@props(['reviews'])
@if ($reviews->isEmpty())
    <p class="text-muted mb-0">No reviews yet.</p>
@else
    <div class="list-group list-group-flush">@foreach ($reviews as $review)<article class="list-group-item px-0"><div class="d-flex justify-content-between gap-3"><div><x-reviews.star-rating :rating="$review->rating" /><span class="ms-2 fw-semibold">{{ $review->tourist?->full_name ? Str::before($review->tourist->full_name, ' ') : 'Verified traveler' }}</span></div><time class="small text-muted">{{ $review->review_date?->format('M j, Y') }}</time></div><p class="mb-0 mt-2">{{ $review->comment }}</p></article>@endforeach</div>
@endif
