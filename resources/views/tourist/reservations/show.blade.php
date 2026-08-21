@extends('layouts.app')

@section('title', 'Reservation Details #BK-' . sprintf('%05d', $booking->booking_id))

@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tourist.reservations.index') }}">My Bookings</a></li>
            <li class="breadcrumb-item active" aria-current="page">#BK-{{ sprintf('%05d', $booking->booking_id) }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Reservation #BK-{{ sprintf('%05d', $booking->booking_id) }}</h1>
            <p class="text-muted small mb-0">Requested on {{ $booking->booking_date ? $booking->booking_date->format('F d, Y at H:i') : $booking->created_at->format('F d, Y') }}</p>
        </div>
        <div>
            <x-ui.status-badge :status="$booking->status" />
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5">Payment</h2>
            @include('payments.action', ['booking' => $booking])
        </div>
    </div>

    @if ($reviewEligible)
        <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5">Write a review</h2><p class="small text-muted">This completed booking is eligible for one review. Your submission cannot be edited later.</p><form method="POST" action="{{ route('tourist.reservations.reviews.store', $booking) }}">@csrf<div class="row g-3"><div class="col-md-3"><label class="form-label" for="rating">Rating</label><select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required><option value="">Choose</option>@for($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5</option>@endfor</select>@error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-9"><label class="form-label" for="comment">Review</label><textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="3" minlength="10" maxlength="2000" required>{{ old('comment') }}</textarea>@error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><button class="btn btn-primary mt-3" type="submit">Submit review</button></form></div></div>
    @elseif ($booking->review)
        <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5">Your review</h2><x-reviews.star-rating :rating="$booking->review->rating" /><p class="mt-2 mb-1">{{ $booking->review->comment }}</p><small class="text-muted">Submitted {{ $booking->review->review_date?->format('M j, Y') }}</small></div></div>
    @endif

    @if ($booking->guide_id !== null && $booking->tourGuideReservation)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0">Tour Guide Booking Details</h2>
                    </div>
                    <div class="card-body p-4">
                        @php
                            $guideObj = $booking->tourGuide;
                            $guideName = $guideObj?->full_name ?: ($guideObj?->user?->email ?? 'Licensed Tour Guide');
                        @endphp
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            @if($guideObj)
                                <img src="{{ $guideObj->profileImageUrl() }}"
                                     alt="{{ $guideName }}"
                                     class="rounded-circle border border-2 shadow-sm"
                                     style="width: 70px; height: 70px; object-fit: cover;">
                            @endif
                            <div>
                                <h3 class="h5 mb-1 text-dark fw-bold">{{ $guideName }}</h3>
                                <div class="text-muted small">License: {{ $guideObj?->license_number ?? 'N/A' }}</div>
                                @if($guideObj?->phone_number)
                                    <div class="text-muted small">📞 {{ $guideObj->phone_number }}</div>
                                @endif
                                @if($guideObj && !empty($guideObj->languagesList()))
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($guideObj->languagesList() as $lang)
                                            <span class="badge bg-light text-dark border small">{{ $lang }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 p-3 bg-light rounded border">
                            <div class="col-sm-6"><span class="text-muted small d-block">Start Date</span><strong>{{ $booking->tourGuideReservation->start_date->format('F d, Y') }}</strong></div>
                            <div class="col-sm-6"><span class="text-muted small d-block">End Date</span><strong>{{ $booking->tourGuideReservation->end_date->format('F d, Y') }}</strong></div>
                            <div class="col-sm-6"><span class="text-muted small d-block">Party Size</span><strong>{{ $booking->tourGuideReservation->number_of_tourists }} tourist(s)</strong></div>
                            <div class="col-sm-6"><span class="text-muted small d-block">Booking total</span><strong>{{ $booking->total_amount !== null ? number_format((float) $booking->total_amount, 2).' '.($booking->currency ?? 'ETB') : 'Not available' }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h2 class="h5 mb-0">Booking Status</h2></div>
                    <div class="card-body p-4">
                        @if ($booking->status === 'pending')
                            <form method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" data-confirm="Are you sure you want to cancel this booking request?">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger w-100">Cancel Request</button>
                            </form>
                        @elseif ($booking->status === 'payment_pending')
                            <div class="alert alert-info small mb-0"><strong>Awaiting Payment</strong> — Complete payment using the payment action above to confirm your booking.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif ($booking->eventReservation)
    @php
        $eventRes = $booking->eventReservation;
    @endphp
    <div class="row g-4"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5">Event ticket reservation</h2><h3 class="h4 text-primary">{{ $eventRes->ticketType->event->event_name }}</h3><p class="text-muted">{{ $eventRes->ticketType->name }} · {{ $eventRes->ticketType->event->event_date->format('F d, Y') }}</p><p>Quantity: <strong>{{ $eventRes->quantity }}</strong></p></div></div></div><div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><p>Total: <strong>{{ number_format($booking->total_amount ?? 0,2) }} {{ $booking->currency ?? 'ETB' }}</strong></p><p class="small text-muted mb-0">Use the payment action above when this booking is ready for payment.</p></div></div></div></div>
    @elseif ($booking->transportationReservation)
    @php
        $transportRes = $booking->transportationReservation;
    @endphp
    <div class="row g-4"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Transportation Reservation Details</h2></div><div class="card-body p-4"><h3 class="h4 text-primary">{{ $booking->tourismService->service_name ?? 'Transportation reservation' }}</h3><p class="text-muted mb-3">Provided by <strong>{{ $booking->tourismService->serviceProvider->business_name ?? 'N/A' }}</strong></p><div class="row g-3 p-3 bg-light rounded border"><div class="col-sm-6"><span class="text-muted small d-block">Pickup</span><strong>{{ $transportRes->pickup_location }} · {{ $transportRes->pickup_at->format('M d, Y H:i') }}</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Drop-off</span><strong>{{ $transportRes->dropoff_location }} · {{ $transportRes->dropoff_at->format('M d, Y H:i') }}</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Passengers</span><strong>{{ $transportRes->passenger_count }}</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Vehicle</span><strong>{{ $transportRes->vehicle?->vehicle_identifier ?? 'Unassigned' }}</strong></div></div></div></div></div><div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Pricing Summary</h2></div><div class="card-body p-4"><div class="d-flex justify-content-between mb-2"><span>Daily rate</span><strong>{{ $transportationDailyRate !== null ? number_format((float) $transportationDailyRate, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</strong></div><div class="d-flex justify-content-between mb-3 pb-2 border-bottom"><span>Rental duration</span><strong>{{ $transportationRentalDays }} day(s)</strong></div><div class="d-flex justify-content-between text-primary fw-bold"><span>Booking total</span><strong>{{ $booking->total_amount !== null ? number_format((float) $booking->total_amount, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</strong></div>@if($booking->status==='pending')<form class="mt-4" method="POST" action="{{ route('tourist.reservations.cancel',$booking) }}">@csrf @method('PATCH')<button class="btn btn-outline-danger w-100">Cancel Request</button></form>@endif</div></div></div></div>
    @elseif ($booking->restaurantReservation)
    @php
        $restaurantRes = $booking->restaurantReservation;
    @endphp
    <div class="row g-4">
        <div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Restaurant Reservation Details</h2></div><div class="card-body p-4"><h3 class="h4 text-primary">{{ $booking->tourismService->service_name ?? 'Restaurant reservation' }}</h3><p class="text-muted mb-3">Provided by <strong>{{ $booking->tourismService->serviceProvider->business_name ?? 'N/A' }}</strong></p><div class="row g-3 p-3 bg-light rounded border"><div class="col-sm-6"><span class="text-muted small d-block">Date</span><strong>{{ $restaurantRes->reservation_date->format('F d, Y') }}</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Time</span><strong>{{ substr($restaurantRes->start_time, 0, 5) }}–{{ substr($restaurantRes->end_time, 0, 5) }}</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Guests</span><strong>{{ $restaurantRes->guest_count }} guest(s)</strong></div><div class="col-sm-6"><span class="text-muted small d-block">Table</span><strong>{{ $restaurantRes->restaurantTable ? 'Table '.$restaurantRes->restaurantTable->table_number : 'Unassigned' }}</strong></div></div></div></div></div>
        <div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Pricing Summary</h2></div><div class="card-body p-4"><div class="d-flex justify-content-between mb-2"><span>Reservation price</span><strong>{{ $booking->total_amount !== null ? number_format((float) $booking->total_amount, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</strong></div><div class="d-flex justify-content-between pb-3 border-bottom text-primary fw-bold"><span>Booking total</span><strong>{{ $booking->total_amount !== null ? number_format((float) $booking->total_amount, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</strong></div><p class="small text-muted mt-3 mb-0">The booking total is frozen when you submit your request and is payable after acceptance.</p>@if($booking->status === 'pending')<form class="mt-3" method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" data-confirm="Cancel this request?">@csrf @method('PATCH')<button class="btn btn-outline-danger w-100">Cancel Request</button></form>@endif</div></div></div>
    </div>
    @else
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Hotel & Room Details</h2>
                </div>
                <div class="card-body p-4">
                    <h3 class="h4 text-primary">{{ $booking->tourismService->service_name ?? 'N/A' }}</h3>
                    <p class="text-muted mb-3">Provided by <strong>{{ $booking->tourismService->serviceProvider->business_name ?? 'N/A' }}</strong></p>
                    <p class="mb-2">{{ $booking->tourismService->description ?? '' }}</p>

                    @php
                        $roomType = $booking->tourismService->hotelRoomType ?? null;
                        $res = $booking->hotelRoomReservation;
                        $nights = $res ? max(1, (int) $res->check_in_date->diffInDays($res->check_out_date)) : 1;
                        $totalCost = $booking->total_amount ?? ($booking->tourismService ? $nights * (float) $booking->tourismService->price : null);
                    @endphp

                    @if ($roomType)
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge bg-light text-dark border">Capacity: {{ $roomType->capacity }} guest(s)</span>
                            @foreach ($roomType->amenities ?? [] as $amenity)
                                <span class="badge bg-secondary-subtle text-secondary border">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3 p-3 bg-light rounded border">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Check-in Date</span>
                            <strong class="fs-6">{{ $res ? $res->check_in_date->format('F d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Check-out Date</span>
                            <strong class="fs-6">{{ $res ? $res->check_out_date->format('F d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Duration</span>
                            <strong>{{ $nights }} night(s)</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Guests</span>
                            <strong>{{ $res->guest_count ?? 1 }} guest(s)</strong>
                        </div>
                        @if ($res && $res->hotelRoom)
                            <div class="col-12 pt-2 border-top">
                                <span class="text-muted small d-block">Allocated Room Number</span>
                                <strong class="text-success fs-6">Room {{ $res->hotelRoom->room_number }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Pricing Summary</h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Listed nightly rate</span>
                        <span>{{ $booking->tourismService?->price !== null ? number_format((float) $booking->tourismService->price, 2).' '.($booking->currency ?? 'ETB') : 'Not available' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $booking->total_amount !== null ? 'Booking total' : 'Estimated total' }}</span>
                        <span>{{ $totalCost !== null ? number_format((float) $totalCost, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                        <span>Duration</span>
                        <span>{{ $nights }} night(s)</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold text-primary mb-4">
                        <span>{{ $booking->total_amount !== null ? 'Total to pay' : 'Estimated stay cost' }}</span>
                        <span>{{ $totalCost !== null ? number_format((float) $totalCost, 2).' '.($booking->currency ?? 'ETB') : 'Not priced' }}</span>
                    </div>

                    @if ($booking->status === 'pending')
                        <form method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" data-confirm="Are you sure you want to cancel this reservation request?">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger w-100">Cancel Request</button>
                        </form>
                    @elseif ($booking->status === 'payment_pending')
                        <div class="alert alert-info small mb-0">
                            <strong>Awaiting Payment</strong> — Your request was accepted and a room is allocated, but your stay is not yet confirmed.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
