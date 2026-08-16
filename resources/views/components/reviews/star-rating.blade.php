@props(['rating' => 0])
<span class="text-warning" aria-label="{{ number_format((float) $rating, 1) }} out of 5 stars" title="{{ number_format((float) $rating, 1) }} / 5">@for ($star = 1; $star <= 5; $star++){{ $star <= round((float) $rating) ? '★' : '☆' }}@endfor</span>
