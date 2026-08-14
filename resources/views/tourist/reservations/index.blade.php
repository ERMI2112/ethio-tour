@extends('layouts.app')

@section('title', 'My Hotel Bookings')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Hotel Reservations</h1>
            <p class="text-muted small mb-0">View and manage your hotel reservation requests</p>
        </div>
        <a href="{{ route('tourism-services.index') }}" class="btn btn-primary btn-sm">Explore Hotels</a>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-pills card-header-pills">
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
                    <a class="nav-link {{ $status === 'payment_pending' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'payment_pending']) }}">Payment Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'rejected']) }}">Rejected</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('tourist.reservations.index', ['status' => 'cancelled']) }}">Cancelled</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            @if ($bookings->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-2 fs-5">No reservations found.</p>
                    <p class="small mb-0">Browse our published tourism services to make your first hotel reservation.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Hotel & Room</th>
                                <th>Check-in / Check-out</th>
                                <th>Guests</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php
                                    $res = $booking->hotelRoomReservation;
                                    $nights = $res ? max(1, (int) $res->check_in_date->diffInDays($res->check_out_date)) : 1;
                                    $totalCost = $booking->tourismService ? $nights * $booking->tourismService->price : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('tourist.reservations.show', $booking) }}" class="fw-bold text-decoration-none">
                                            #BK-{{ sprintf('%05d', $booking->booking_id) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $booking->tourismService->service_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $booking->tourismService->serviceProvider->business_name ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if ($res)
                                            <div class="small fw-semibold">{{ $res->check_in_date->format('M d, Y') }}</div>
                                            <div class="small text-muted">to {{ $res->check_out_date->format('M d, Y') }} ({{ $nights }} night(s))</div>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $res->guest_count ?? '-' }}</td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($totalCost, 2) }} ETB</span>
                                    </td>
                                    <td>
                                        @switch($booking->status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending Review</span>
                                                @break
                                            @case('accepted')
                                                <span class="badge bg-info text-dark">Accepted</span>
                                                @break
                                            @case('payment_pending')
                                                <span class="badge bg-primary">Payment Pending</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-success">Confirmed</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ ucfirst($booking->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tourist.reservations.show', $booking) }}" class="btn btn-outline-primary btn-sm">View</a>
                                            @if ($booking->status === 'pending')
                                                <form method="POST" action="{{ route('tourist.reservations.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this reservation request?');">
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
