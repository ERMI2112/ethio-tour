@extends('layouts.app')

@section('title', 'Tourist Dashboard')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5 tourist-dashboard">
    {{-- Top Welcome & Traveler Identity Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-compass me-1"></i> Traveler Workspace
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                    Verified Account
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Tena Yistilign, {{ $tourist->full_name ?: 'Traveler' }}!
            </h1>
            <p class="text-secondary mb-0 small">
                Welcome, {{ $tourist->full_name ?: auth()->user()->email }} &bull; Plan your Ethiopian journeys, view verified bookings, and track itineraries.
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Traveler Profile Badge --}}
            <a class="d-flex align-items-center gap-2.5 p-1.5 pe-3 bg-white border rounded-pill shadow-xs text-decoration-none transition-hover" href="{{ route('tourist.profile') }}" title="View My Profile">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 38px; height: 38px; font-size: 0.85rem;">
                    {{ strtoupper(substr($tourist->full_name ?: $tourist->user?->email ?: 'T', 0, 2)) }}
                </div>
                <div class="text-start">
                    <div class="fw-bold text-dark lh-1" style="font-size: 0.85rem;">
                        {{ $tourist->full_name ?: ($tourist->user?->email ?: 'Tourist') }}
                    </div>
                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $tourist->nationality ?: 'Verified Traveler' }}</div>
                </div>
            </a>

            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold d-none d-sm-inline-flex align-items-center gap-1.5" href="{{ route('tourist.profile') }}">
                <i class="bi bi-person"></i> My Profile
            </a>
        </div>
    </div>

    {{-- 4 Key Metrics Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-ui.stat-card label="Upcoming Bookings" icon="journal-bookmark" :value="$upcomingBookings->count()" :hint="$upcomingBookings->whereIn('status', ['confirmed', 'completed'])->count().' confirmed · '.$upcomingBookings->where('status', 'pending')->count().' pending'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-ui.stat-card label="Smart Trips" icon="map" :value="$trips->count()" hint="Saved multi-stop itineraries" />
        </div>
        <div class="col-6 col-xl-3">
            <x-ui.stat-card label="Needs Attention" icon="exclamation-circle" :value="$attention->count()" :hint="$attention->count() > 0 ? $attention->count().' action item(s) pending' : 'All caught up'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-ui.stat-card label="My Reviews" icon="star" :value="$reviews->count()" :hint="$reviewOpportunities->count() > 0 ? $reviewOpportunities->count().' review(s) ready to write' : 'Feedback recorded'" />
        </div>
    </div>

    {{-- Active Travel Focus Banner (When Trips or Upcoming Bookings Exist) --}}
    @if ($trips->isNotEmpty() || $upcomingBookings->isNotEmpty())
        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden text-white" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%);">
            <div class="card-body p-4 p-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="bi bi-compass me-1"></i> Active Travel Focus
                        </span>
                        @if ($upcomingBookings->isNotEmpty())
                            <span class="badge bg-light bg-opacity-10 text-light border border-light border-opacity-25 rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.7rem;">
                                {{ $upcomingBookings->count() }} active booking{{ $upcomingBookings->count() === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </div>
                    <h2 class="h4 fw-bold mb-1 text-white" style="font-family: var(--font-display);">
                        {{ $trips->first()?->title ?: 'Upcoming booking' }}
                    </h2>
                    <p class="text-white-50 small mb-0">
                        Your saved trips and upcoming bookings are managed directly from your traveler account.
                    </p>
                </div>
                @if ($upcomingBookings->first())
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-25 text-center min-w-140">
                        <div class="small text-white-50 fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Next Reservation</div>
                        <div class="h5 fw-bold text-white font-monospace mb-1">#BK-{{ sprintf('%05d', $upcomingBookings->first()->booking_id) }}</div>
                        <div class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">{{ ucfirst($upcomingBookings->first()->status) }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Quick Action Hub (4 Discovery & Planning Tiles) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 text-decoration-none d-flex flex-row align-items-center gap-3 text-dark transition-hover" href="{{ route('tour-guides.index') }}">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                    <i class="bi bi-person-badge fs-5"></i>
                </div>
                <div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Find Guide</h3>
                    <p class="text-muted small mb-0">Certified local historians</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 text-decoration-none d-flex flex-row align-items-center gap-3 text-dark transition-hover" href="{{ route('tourism-services.index', ['provider_type' => 'hotel']) }}">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                    <i class="bi bi-building fs-5"></i>
                </div>
                <div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Book Hotel</h3>
                    <p class="text-muted small mb-0">Verified heritage stays</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 text-decoration-none d-flex flex-row align-items-center gap-3 text-dark transition-hover" href="{{ route('tourism-services.index', ['provider_type' => 'restaurant']) }}">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                    <i class="bi bi-cup-hot fs-5"></i>
                </div>
                <div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Restaurants</h3>
                    <p class="text-muted small mb-0">Traditional dining & coffee</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3.5 text-decoration-none d-flex flex-row align-items-center gap-3 text-dark transition-hover" href="{{ route('smart-trip.index') }}">
                <div class="rounded-circle bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                    <i class="bi bi-map fs-5"></i>
                </div>
                <div>
                    <h3 class="h6 fw-bold mb-0 text-dark">Smart Trip</h3>
                    <p class="text-muted small mb-0">Custom journey builder</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Needs Attention Section --}}
    @if ($attention->isNotEmpty())
        <section class="mb-4" aria-labelledby="attention-heading">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <h2 id="attention-heading" class="h6 fw-bold text-dark mb-0 text-uppercase d-flex align-items-center gap-2" style="letter-spacing: 0.05em; font-size: 0.8rem;">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Needs attention
                </h2>
                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">{{ $attention->count() }} action item{{ $attention->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="row g-3">
                @foreach ($attention->take(4) as $item)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <strong class="text-dark d-block small mb-1">{{ $item['label'] }}</strong>
                                    <span class="small text-muted font-monospace">Booking #BK-{{ sprintf('%05d', $item['booking']->booking_id) }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-light border rounded-pill px-3" href="{{ route('tourist.reservations.show', $item['booking']) }}">Open booking</a>
                                    @if ($payments->canPay($item['booking']) && $item['booking']->payment?->status !== 'success')
                                        <form method="POST" action="{{ route('payments.initialize', $item['booking']) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" type="submit">
                                                {{ $item['booking']->status === 'payment_pending' ? 'Continue Payment' : 'Pay Now' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center gap-2.5">
                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-bold">✓ Calm</span>
                <span class="small text-muted"><strong>You're all caught up.</strong> New booking and trip updates will appear here.</span>
            </div>
        </div>
    @endif

    {{-- Main 2-Column Section --}}
    <div class="row g-4 mb-4">
        {{-- Left Column (7 cols): Supervised Active Bookings & Smart Trips --}}
        <div class="col-lg-7">
            {{-- Supervised Active Bookings --}}
            <section class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" aria-labelledby="upcoming-heading">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 id="upcoming-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-journal-bookmark-fill text-success me-1.5"></i> Supervised Active Bookings
                        </h2>
                        <span class="visually-hidden">Upcoming bookings</span>
                        <p class="text-muted small mb-0">Direct escrow verified bookings</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-pill px-3 fw-semibold small text-muted border" href="{{ route('tourist.reservations.index') }}">
                        View all &rarr;
                    </a>
                </div>

                <div class="card-body p-3.5">
                    @if ($upcomingBookings->isEmpty())
                        <div class="p-3">
                            <x-ui.empty-state icon="bi-calendar2-heart" title="No upcoming bookings" message="You don't have any upcoming bookings yet. Explore Ethiopia to find your next experience." />
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2.5">
                            @foreach ($upcomingBookings as $booking)
                                @php
                                    $event = $booking->eventReservation?->ticketType?->event;
                                    $serviceName = $booking->tourGuide ? 'Guide: '.($booking->tourGuide->full_name ?: 'Licensed Tour Guide') : ($event ? $event->event_name : ($booking->tourismService?->service_name ?? 'Tourism reservation'));
                                    $statusPill = match($booking->status) {
                                        'confirmed', 'completed' => ['label' => 'Confirmed', 'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                        'accepted', 'payment_pending' => ['label' => 'Pending Payment', 'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                        'pending' => ['label' => 'Pending', 'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                        'cancelled', 'rejected' => ['label' => 'Cancelled', 'class' => 'bg-secondary-subtle text-secondary border'],
                                        default => ['label' => ucfirst($booking->status), 'class' => 'bg-light text-dark border']
                                    };
                                    $iconClass = $booking->tourGuide ? 'bi-person-badge text-success' : ($event ? 'bi-ticket-perforated text-danger' : ($booking->hotelRoomReservation ? 'bi-building text-primary' : ($booking->restaurantReservation ? 'bi-cup-hot text-warning' : 'bi-car-front text-info')));
                                @endphp
                                <div class="card border rounded-3 p-3 bg-light-subtle shadow-xs">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi {{ $iconClass }} fs-5 flex-shrink-0"></i>
                                            <div>
                                                <span class="badge bg-light text-muted border rounded-2 px-2 py-0.5 small font-monospace me-1.5">
                                                    #BK-{{ sprintf('%05d', $booking->booking_id) }}
                                                </span>
                                                <strong class="text-dark fs-6">{{ $serviceName }}</strong>
                                            </div>
                                        </div>
                                        <span class="badge {{ $statusPill['class'] }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                            {{ $statusPill['label'] }}
                                        </span>
                                    </div>

                                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-2 border-top">
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            @if ($booking->tourGuideReservation)
                                                {{ $booking->tourGuideReservation->start_date->format('M j, Y') }} – {{ $booking->tourGuideReservation->end_date->format('M j, Y') }}
                                            @elseif ($booking->hotelRoomReservation)
                                                {{ $booking->hotelRoomReservation->check_in_date->format('M j, Y') }} – {{ $booking->hotelRoomReservation->check_out_date->format('M j, Y') }}
                                            @elseif ($booking->restaurantReservation)
                                                {{ $booking->restaurantReservation->reservation_date->format('M j, Y') }} ({{ substr($booking->restaurantReservation->start_time, 0, 5) }})
                                            @elseif ($booking->transportationReservation)
                                                {{ $booking->transportationReservation->pickup_at->format('M j, Y H:i') }}
                                            @elseif ($event)
                                                {{ $event->event_date->format('M j, Y') }}
                                            @else
                                                {{ $booking->booking_date?->format('M j, Y') }}
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            @if ($booking->total_amount !== null)
                                                <strong class="text-dark font-monospace fs-6">
                                                    {{ number_format((float) $booking->total_amount, 2) }} ETB
                                                </strong>
                                            @endif
                                            <a class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 small" href="{{ route('tourist.reservations.show', $booking) }}">Details</a>
                                            @if ($payments->canPay($booking) && $booking->payment?->status !== 'success')
                                                <form method="POST" action="{{ route('payments.initialize', $booking) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold small" type="submit">
                                                        {{ $booking->status === 'payment_pending' ? 'Continue Payment' : 'Pay Now' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- Smart Trips Section --}}
            <section class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" aria-labelledby="trips-heading">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 id="trips-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-map text-primary me-1.5"></i> My Trips
                        </h2>
                        <p class="text-muted small mb-0">Multi-stop journey itineraries</p>
                    </div>
                    <a class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" href="{{ route('smart-trip.create') }}">
                        + Plan a new trip
                    </a>
                </div>
                <div class="card-body p-3.5">
                    @forelse ($trips as $trip)
                        <div class="d-flex justify-content-between align-items-center gap-3 p-3 rounded-3 bg-light-subtle mb-2 border shadow-xs">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">📍 {{ $trip->title }}</h3>
                                <p class="small text-muted mb-0">
                                    {{ $trip->start_date->format('M j, Y') }} – {{ $trip->end_date->format('M j, Y') }} &bull; {{ $trip->items_count }} itinerary item(s)
                                </p>
                            </div>
                            <a class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" href="{{ route('smart-trip.show', $trip) }}">Open trip</a>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small">
                            <x-ui.empty-state icon="bi-map" title="No saved trips yet" message="Plan your first trip with Smart Trip." />
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Right Column (5 cols): Notifications & Reviews --}}
        <div class="col-lg-5">
            {{-- Notifications Center --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4" aria-labelledby="notifications-heading">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 id="notifications-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-bell text-primary me-1.5"></i> Notifications
                        @if ($unreadNotificationCount)
                            <span class="badge bg-danger rounded-pill px-2 py-0.5 small ms-1">{{ $unreadNotificationCount }} unread</span>
                        @endif
                    </h2>
                    <a class="small text-decoration-none text-muted" href="{{ route('notifications.index') }}">View all &rarr;</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentNotifications as $notification)
                        <a class="list-group-item list-group-item-action p-3 {{ ! $notification->read_status ? 'bg-primary-subtle bg-opacity-25' : '' }}" href="{{ route('notifications.index') }}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <strong class="small text-dark d-block mb-0.5">{{ $notification->title }}</strong>
                                @if(! $notification->read_status)
                                    <span class="badge bg-primary p-1 rounded-circle" style="width: 6px; height: 6px;" title="Unread"></span>
                                @endif
                            </div>
                            <span class="d-block small text-muted">{{ $notification->sent_date?->diffForHumans() }}</span>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
                            You're all caught up.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Reviews & Feedback Card --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" aria-labelledby="reviews-heading">
                <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
                    <h2 id="reviews-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-star-fill text-warning me-1.5"></i> My Reviews
                    </h2>
                    <a class="small text-decoration-none text-muted" href="{{ route('tourist.reviews.index') }}">View all &rarr;</a>
                </div>
                <div class="card-body p-3.5">
                    @forelse ($reviews as $review)
                        <div class="border-bottom py-2.5">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <x-reviews.star-rating :rating="$review->rating" />
                                <small class="text-muted font-monospace">{{ $review->review_date?->format('M j, Y') }}</small>
                            </div>
                            <p class="small text-secondary mb-0">{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</p>
                        </div>
                    @empty
                        <p class="text-muted small mb-0 py-2 text-center">You haven't submitted a review yet.</p>
                    @endforelse

                    @if ($reviewOpportunities->isNotEmpty())
                        <div class="mt-3 pt-3 border-top">
                            <h3 id="review-ready-heading" class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="bi bi-pencil-square text-primary me-1"></i> Experiences ready for review
                            </h3>
                            @foreach ($reviewOpportunities as $booking)
                                <div class="d-flex justify-content-between align-items-center gap-2 py-1.5">
                                    <span class="small font-monospace">Booking #BK-{{ sprintf('%05d', $booking->booking_id) }}</span>
                                    <a class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 small fw-semibold" href="{{ route('tourist.reservations.show', $booking) }}">Write review</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-2 text-center">
                            <span class="small text-muted" style="font-size: 0.75rem;">Completed bookings will appear here when they're ready for review.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
