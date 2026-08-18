@extends('layouts.app')

@section('title', 'Tourist Dashboard')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase text-success small fw-semibold mb-1">Traveler workspace</p>
            <h1 class="h2 mb-1">Welcome, {{ $tourist->full_name ?: auth()->user()->email }}</h1>
            <p class="text-muted mb-0">Your bookings, trips, notifications, and reviews in one place.</p>
        </div>
        <a class="btn btn-success" href="{{ route('search') }}">Explore public site</a>
    </div>

    @if ($attention->isNotEmpty())
        <section class="mb-4" aria-labelledby="attention-heading">
            <h2 id="attention-heading" class="h5 mb-3">Needs attention</h2>
            <div class="row g-3">
                @foreach ($attention->take(4) as $item)
                    <div class="col-md-6"><div class="alert alert-warning h-100 d-flex justify-content-between align-items-center gap-3 mb-0"><span>{{ $item['label'] }}<span class="d-block small text-muted">Booking #BK-{{ sprintf('%05d', $item['booking']->booking_id) }}</span></span><a class="btn btn-sm btn-warning text-nowrap" href="{{ route('tourist.reservations.show', $item['booking']) }}">Review</a></div></div>
                @endforeach
            </div>
        </section>
    @else
        <div class="alert alert-success mb-4">You're all caught up. New booking and trip updates will appear here.</div>
    @endif

    <section class="mb-5" aria-labelledby="upcoming-heading">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 id="upcoming-heading" class="h4 mb-0">Upcoming bookings</h2><a class="small" href="{{ route('tourist.reservations.index') }}">View all bookings</a></div>
        @if ($upcomingBookings->isEmpty())
            <x-ui.empty-state title="No upcoming bookings" message="You don't have any upcoming bookings yet. Explore Ethiopia to find your next experience." />
        @else
            <div class="row g-3">
                @foreach ($upcomingBookings as $booking)
                    @php
                        $event = $booking->eventReservation?->ticketType?->event;
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <article class="card border-0 shadow-sm h-100"><div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge text-bg-light">#BK-{{ sprintf('%05d', $booking->booking_id) }}</span><x-ui.status-badge :status="$booking->status" /></div>
                            <h3 class="h5">
                                @if ($booking->tourGuide)
                                    Tour Guide booking
                                @elseif ($event)
                                    {{ $event->event_name }}
                                @else
                                    {{ $booking->tourismService?->service_name ?? 'Tourism reservation' }}
                                @endif
                            </h3>
                            <p class="small text-muted mb-2">
                                @if ($booking->tourGuideReservation)
                                    {{ $booking->tourGuideReservation->start_date->format('M j, Y') }} – {{ $booking->tourGuideReservation->end_date->format('M j, Y') }}
                                @elseif ($booking->hotelRoomReservation)
                                    {{ $booking->hotelRoomReservation->check_in_date->format('M j, Y') }} – {{ $booking->hotelRoomReservation->check_out_date->format('M j, Y') }}
                                @elseif ($booking->restaurantReservation)
                                    {{ $booking->restaurantReservation->reservation_date->format('M j, Y') }}
                                @elseif ($booking->transportationReservation)
                                    {{ $booking->transportationReservation->pickup_at->format('M j, Y H:i') }}
                                @elseif ($event)
                                    {{ $event->event_date->format('M j, Y') }}
                                @else
                                    {{ $booking->booking_date?->format('M j, Y') }}
                                @endif
                            </p>
                            @if ($booking->total_amount !== null)<p class="small mb-3">Amount: <strong>{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency ?? 'ETB' }}</strong></p>@endif
                            <div class="mt-auto d-flex flex-wrap gap-2"><a class="btn btn-outline-primary btn-sm" href="{{ route('tourist.reservations.show', $booking) }}">View details</a>@if ($payments->canPay($booking) && $booking->payment?->status !== 'success')<form method="POST" action="{{ route('payments.initialize', $booking) }}">@csrf<button class="btn btn-success btn-sm" type="submit">{{ $booking->status === 'payment_pending' ? 'Continue Payment' : 'Pay Now' }}</button></form>@endif</div>
                        </div></article>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="row g-4">
        <div class="col-lg-7"><section aria-labelledby="trips-heading" class="card border-0 shadow-sm h-100"><div class="card-header bg-white d-flex justify-content-between align-items-center"><h2 id="trips-heading" class="h5 mb-0">My Trips</h2><a class="small" href="{{ route('smart-trip.create') }}">Plan a new trip</a></div><div class="card-body">@forelse ($trips as $trip)<div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3"><div><h3 class="h6 mb-1">{{ $trip->title }}</h3><p class="small text-muted mb-0">{{ $trip->start_date->format('M j, Y') }} – {{ $trip->end_date->format('M j, Y') }} · {{ $trip->items_count }} itinerary item(s)</p></div><a class="btn btn-outline-success btn-sm" href="{{ route('smart-trip.show', $trip) }}">Open trip</a></div>@empty<x-ui.empty-state title="No saved trips yet" message="Plan your first trip with Smart Trip." />@endforelse</div></section></div>
        <div class="col-lg-5"><section aria-labelledby="notifications-heading" class="card border-0 shadow-sm h-100"><div class="card-header d-flex justify-content-between align-items-center bg-white"><h2 id="notifications-heading" class="h5 mb-0">Notifications @if ($unreadNotificationCount)<span class="badge text-bg-primary">{{ $unreadNotificationCount }} unread</span>@endif</h2><a class="small" href="{{ route('notifications.index') }}">View all</a></div><div class="list-group list-group-flush">@forelse ($recentNotifications as $notification)<a class="list-group-item list-group-item-action {{ ! $notification->read_status ? 'bg-primary-subtle' : '' }}" href="{{ route('notifications.index') }}"><strong class="small">{{ $notification->title }}</strong><span class="d-block small text-muted">{{ $notification->sent_date?->diffForHumans() }}</span></a>@empty<div class="p-4 text-center text-muted">You're all caught up.</div>@endforelse</div></section></div>
    </div>

    <div class="row g-4 mt-1"><div class="col-lg-7"><section aria-labelledby="reviews-heading" class="card border-0 shadow-sm"><div class="card-header bg-white d-flex justify-content-between align-items-center"><h2 id="reviews-heading" class="h5 mb-0">My Reviews</h2><a class="small" href="{{ route('tourist.reviews.index') }}">View all</a></div><div class="card-body">@forelse ($reviews as $review)<div class="border-bottom py-3"><div class="d-flex justify-content-between"><x-reviews.star-rating :rating="$review->rating" /><small class="text-muted">{{ $review->review_date?->format('M j, Y') }}</small></div><p class="small mb-0 mt-2">{{ \Illuminate\Support\Str::limit($review->comment, 140) }}</p></div>@empty<p class="text-muted mb-0">You haven't submitted a review yet.</p>@endforelse</div></section></div><div class="col-lg-5"><section aria-labelledby="review-ready-heading" class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 id="review-ready-heading" class="h5 mb-0">Reviews ready</h2></div><div class="card-body">@forelse ($reviewOpportunities as $booking)<div class="d-flex justify-content-between align-items-center gap-2 border-bottom py-2"><span class="small">Booking #BK-{{ sprintf('%05d', $booking->booking_id) }}</span><a class="btn btn-outline-primary btn-sm" href="{{ route('tourist.reservations.show', $booking) }}">Write review</a></div>@empty<p class="small text-muted mb-0">Completed bookings will appear here when they&#039;re ready for review.</p>@endforelse</div></section></div></div>
</div>
@endsection
