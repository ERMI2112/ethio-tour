@extends('layouts.app')

@section('title', 'Incoming Hotel Reservations')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Incoming Hotel Reservations</h1>
            <p class="text-muted small mb-0">Manage customer booking requests for your hotel</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hotel.services.index') }}" class="btn btn-outline-secondary btn-sm">Room Types</a>
            <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary btn-sm">Physical Rooms</a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('hotel.reservations.index') }}">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'payment_pending' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'payment_pending']) }}">Payment Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'accepted']) }}">Accepted</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'rejected']) }}">Rejected</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'cancelled']) }}">Cancelled</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            @if ($bookings->isEmpty())
                <div class="p-5 text-center text-muted">
                    <p class="mb-2 fs-5">No reservation requests found.</p>
                    <p class="small mb-0">Incoming reservation requests from tourists will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Tourist</th>
                                <th>Room Type</th>
                                <th>Check-in / Check-out</th>
                                <th>Allocated Room</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php
                                    $res = $booking->hotelRoomReservation;
                                    $nights = $res ? max(1, (int) $res->check_in_date->diffInDays($res->check_out_date)) : 1;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('hotel.reservations.show', $booking) }}" class="fw-bold text-decoration-none">
                                            #BK-{{ sprintf('%05d', $booking->booking_id) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $booking->tourist->full_name ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $booking->tourist->nationality ?? '' }}</div>
                                    </td>
                                    <td>{{ $booking->tourismService->service_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($res)
                                            <div class="small fw-semibold">{{ $res->check_in_date->format('M d, Y') }}</div>
                                            <div class="small text-muted">to {{ $res->check_out_date->format('M d, Y') }} ({{ $nights }} n, {{ $res->guest_count }} g)</div>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($res && $res->hotelRoom)
                                            <span class="badge bg-success-subtle text-success border">Room {{ $res->hotelRoom->room_number }}</span>
                                        @else
                                            <span class="text-muted small">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($booking->status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
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
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('hotel.reservations.show', $booking) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                            @if ($booking->status === 'pending')
                                                <form method="POST" action="{{ route('hotel.reservations.accept', $booking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                </form>
                                                <form method="POST" action="{{ route('hotel.reservations.reject', $booking) }}" onsubmit="return confirm('Reject this reservation request?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
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
