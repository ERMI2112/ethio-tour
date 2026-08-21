@extends('layouts.app')

@section('title', 'Hotel Reservations')

@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reservations</li>
        </ol>
    </nav>

    <div class="ws-page-header mb-4">
        <div><span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Hotel operations</span><h1 class="ws-title">Reservations</h1><p class="ws-lead">Requests shown here apply only to your hotel.</p></div>
        <div class="ws-actions d-flex gap-2">
            <a href="{{ route('hotel.services.index') }}" class="btn btn-outline-secondary btn-sm">Room Types</a>
            <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary btn-sm">Physical Rooms</a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-pills card-header-pills flex-wrap">
                <li class="nav-item"><a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('hotel.reservations.index') }}">All</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}">Pending</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'accepted']) }}">Accepted</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'payment_pending' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'payment_pending']) }}">Awaiting Payment</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'confirmed' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'confirmed']) }}">Confirmed</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'rejected']) }}">Rejected</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'cancelled']) }}">Cancelled</a></li>
                <li class="nav-item"><a class="nav-link {{ $status === 'completed' ? 'active' : '' }}" href="{{ route('hotel.reservations.index', ['status' => 'completed']) }}">Completed</a></li>
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
                                @php($res = $booking->hotelRoomReservation)
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
                                            <div class="small text-muted">to {{ $res->check_out_date->format('M d, Y') }} ({{ $res->guest_count }} guest(s))</div>
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
                                    <td><x-ui.status-badge :status="$booking->status" /></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('hotel.reservations.show', $booking) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                            @if ($booking->status === 'pending')
                                                <form method="POST" action="{{ route('hotel.reservations.accept', $booking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                </form>
                                                <form method="POST" action="{{ route('hotel.reservations.reject', $booking) }}" data-confirm="Reject this reservation request?">
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
                    <div class="p-3 border-top">{{ $bookings->links() }}</div>
                @endif
            @endif
        </div>
    </div>
<div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Reservation status guide</h2>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-3 small">
                <span><x-ui.status-badge :status="'pending'" /> Awaiting your decision.</span>
                <span><x-ui.status-badge :status="'accepted'" /> Accepted; awaiting payment before confirmation.</span>
                <span><x-ui.status-badge :status="'payment_pending'" /> Room allocated; not yet confirmed until paid.</span>
                <span><x-ui.status-badge :status="'confirmed'" /> Payment received and stay confirmed.</span>
                <span><x-ui.status-badge :status="'rejected'" /> Request declined.</span>
                <span><x-ui.status-badge :status="'cancelled'" /> Cancelled by the guest.</span>
            </div>
        </div>
    </div>
</div>
@endsection
