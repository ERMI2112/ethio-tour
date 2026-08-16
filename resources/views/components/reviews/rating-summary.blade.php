@props(['average' => null, 'count' => 0])
<div class="d-flex align-items-center gap-3">@if ($count > 0)<x-reviews.star-rating :rating="$average" /><strong>{{ number_format((float) $average, 1) }} / 5</strong><span class="text-muted small">({{ $count }} {{ $count === 1 ? 'review' : 'reviews' }})</span>@else<span class="text-muted small">No reviews yet</span>@endif</div>
