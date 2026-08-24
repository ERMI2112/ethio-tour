@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Bookings</h1>
            <p class="text-muted small mb-0">View and manage your tourism booking requests</p>
        </div>
        <a href="{{ route('tourism-services.index') }}" class="btn btn-primary btn-sm">Explore Services</a>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="overflow-auto"><ul class="nav nav-pills card-header-pills flex-nowrap">
                <li class="nav-item">
                    <a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('tourist.reservations.index') }}">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'pending']) }}">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'accepted']) }}">Accepted</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'payment_pending' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'payment_pending']) }}">Awaiting Payment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'confirmed' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'confirmed']) }}">Confirmed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'completed' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'completed']) }}">Completed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'rejected']) }}">Rejected</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'cancelled']) }}">Cancelled</a>
                </li>
            </ul></div>
        </div>
        <div class="card-body p-0">
            @if ($bookings->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-2 fs-5">No reservations found.</p>
                    <p class="small mb-0">Browse published tourism experiences to plan your next stay, meal, journey, tour, or event.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Service / Guide</th>
                                <th>Dates</th>
                                <th>Party size</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php($isGuideBooking = $booking->guide_id !== null && $booking->tourGuideReservation)
                                @php($res = $booking->hotelRoomReservation)
                                @php($restaurantRes = $booking->restaurantReservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('tourist.reservations.show', $booking) }}" class="fw-bold text-decoration-none">
                                            #BK-{{ sprintf('%05d', $booking->booking_id) }}
                                        </a>
                                    </td>
                                    @if ($isGuideBooking)
                                        <td><div class="fw-bold">Tour Guide</div><div class="small text-muted">License {{ $booking->tourGuide->license_number }}</div></td>
                                        <td><div class="small fw-semibold">{{ $booking->tourGuideReservation->start_date->format('M d, Y') }}</div><div class="small text-muted">to {{ $booking->tourGuideReservation->end_date->format('M d, Y') }}</div></td>
                                        <td>{{ $booking->tourGuideReservation->number_of_tourists }}</td>
                                        <td>
                                            @if ($booking->total_amount !== null)
                                                <span class="fw-bold">{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency ?? 'ETB' }}</span>
                                            @else
                                                <span class="text-muted">Not priced</span>
                                            @endif
                                        </td>
                                    @elseif ($booking->eventReservation)
                                        <td><div class="fw-bold">{{ $booking->eventReservation->ticketType->event->event_name }}</div><div class="small text-muted">{{ $booking->eventReservation->ticketType->name }}</div></td>
                                        <td><div class="small fw-semibold">{{ $booking->eventReservation->ticketType->event->event_date->format('M d, Y') }}</div><div class="small text-muted">Event tickets</div></td>
                                        <td>{{ $booking->eventReservation->quantity }}</td>
                                        <td><span class="fw-bold">{{ number_format($booking->total_amount ?? 0, 2) }} {{ $booking->currency ?? 'ETB' }}</span></td>
                                    @elseif ($booking->transportationReservation)
                                        @php($transportRes = $booking->transportationReservation)
                                        <td><div class="fw-bold">{{ $booking->tourismService->service_name ?? 'Transportation reservation' }}</div><div class="small text-muted">{{ $booking->tourismService->serviceProvider->business_name ?? '' }}</div></td>
                                        <td><div class="small fw-semibold">{{ $transportRes->pickup_at->format('M d, Y H:i') }}</div><div class="small text-muted">to {{ $transportRes->dropoff_at->format('M d, Y H:i') }}</div></td>
                                        <td>{{ $transportRes->passenger_count }}</td>
                                        <td>
                                            @if ($booking->total_amount !== null)
                                                <span class="fw-bold">{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency ?? 'ETB' }}</span>
                                            @else
                                                <span class="text-muted">Not priced</span>
                                            @endif
                                        </td>
                                    @elseif ($restaurantRes)
                                        <td><div class="fw-bold">{{ $booking->tourismService->service_name ?? 'Restaurant reservation' }}</div><div class="small text-muted">{{ $booking->tourismService->serviceProvider->business_name ?? '' }}</div></td>
                                        <td><div class="small fw-semibold">{{ $restaurantRes->reservation_date->format('M d, Y') }}</div><div class="small text-muted">{{ substr($restaurantRes->start_time, 0, 5) }}–{{ substr($restaurantRes->end_time, 0, 5) }}</div></td>
                                        <td>{{ $restaurantRes->guest_count }}</td>
                                        <td>
                                            @if ($booking->total_amount !== null)
                                                <span class="fw-bold">{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency ?? 'ETB' }}</span>
                                            @else
                                                <span class="text-muted">Not priced</span>
                                            @endif
                                        </td>
                                    @else
                                        @php($nights = $res ? max(1, (int) $res->check_in_date->diffInDays($res->check_out_date)) : 1)
                                        @php($totalCost = $booking->total_amount ?? ($booking->tourismService ? $nights * (float) $booking->tourismService->price : null))
                                        <td><div class="fw-bold">{{ $booking->tourismService->service_name ?? 'N/A' }}</div><div class="small text-muted">{{ $booking->tourismService->serviceProvider->business_name ?? '' }}</div></td>
                                        <td>@if ($res)<div class="small fw-semibold">{{ $res->check_in_date->format('M d, Y') }}</div><div class="small text-muted">to {{ $res->check_out_date->format('M d, Y') }} ({{ $nights }} night(s))</div>@else<span class="text-muted small">N/A</span>@endif</td>
                                        <td>{{ $res->guest_count ?? '-' }}</td>
                                        <td>
                                            @if ($totalCost !== null)
                                                <span class="fw-bold">{{ number_format((float) $totalCost, 2) }} {{ $booking->currency ?? 'ETB' }}</span>
                                            @else
                                                <span class="text-muted">Not priced</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <x-ui.status-badge :status="$booking->status" />
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tourist.reservations.show', $booking) }}" class="btn btn-outline-primary btn-sm">View</a>
                                            @if (in_array($booking->status, ['accepted', 'payment_pending'], true) && $booking->total_amount !== null && (float) $booking->total_amount > 0)
                                                <form method="POST" action="{{ route('payments.initialize', $booking) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">{{ $booking->status === 'payment_pending' ? 'Continue Payment' : 'Pay Now' }}</button>
                                                </form>
                                            @endif
                                            @if ($booking->status === 'pending')
                                                <form method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" data-confirm="Are you sure you want to cancel this reservation request?">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($bookings->hasPages())
                    <div class="p-3 border-top">
                        {{ $bookings->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
