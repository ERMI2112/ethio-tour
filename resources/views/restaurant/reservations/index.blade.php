@extends('layouts.app')
@section('title', 'Restaurant Reservations')
@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb"><ol class="breadcrumb small"><li class="breadcrumb-item"><a href="{{ route('restaurant.dashboard') }}">Restaurant Dashboard</a></li><li class="breadcrumb-item active">Reservations</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h2 mb-1">Reservation requests</h1><p class="text-muted mb-0">Only reservations for your restaurant are shown.</p></div><a class="btn btn-outline-secondary" href="{{ route('restaurant.tables.index') }}">Table inventory</a></div>
    @include('layouts.partials.flash-messages')
    <div class="card border-0 shadow-sm mb-4"><div class="card-body py-2"><ul class="nav nav-pills flex-wrap"><li class="nav-item"><a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('restaurant.reservations.index') }}">All</a></li>@foreach(['pending'=>'Pending','accepted'=>'Accepted','payment_pending'=>'Awaiting Payment','confirmed'=>'Confirmed','cancelled'=>'Cancelled','completed'=>'Completed'] as $value => $label)<li class="nav-item"><a class="nav-link {{ $status === $value ? 'active' : '' }}" href="{{ route('restaurant.reservations.index', ['status'=>$value]) }}">{{ $label }}</a></li>@endforeach</ul></div></div>
    <div class="card border-0 shadow-sm"><div class="card-body p-0">
        @if($bookings->isEmpty())
            <x-ui.empty-state title="No restaurant reservations" message="Incoming reservation requests will appear here." />
        @else
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Booking</th><th>Tourist</th><th>Service</th><th>Date and time</th><th>Guests</th><th>Table</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            @foreach($bookings as $booking)
                @php($res = $booking->restaurantReservation)
                <tr><td><a href="{{ route('restaurant.reservations.show', $booking) }}">#BK-{{ sprintf('%05d', $booking->booking_id) }}</a></td><td>{{ $booking->tourist->full_name ?? 'N/A' }}</td><td>{{ $booking->tourismService->service_name ?? 'N/A' }}</td><td>
                    @if($res)<div class="small fw-semibold">{{ $res->reservation_date->format('M d, Y') }}</div><div class="small text-muted">{{ substr($res->start_time, 0, 5) }}–{{ substr($res->end_time, 0, 5) }}</div>@else N/A @endif
                </td><td>{{ $res->guest_count ?? '—' }}</td><td>
                    @if($res?->restaurantTable)<span class="badge bg-success-subtle text-success border">Table {{ $res->restaurantTable->table_number }}</span>@else<span class="text-muted small">Unassigned</span>@endif
                </td><td><x-ui.status-badge :status="$booking->status" /></td><td><div class="d-flex gap-1"><a class="btn btn-sm btn-outline-secondary" href="{{ route('restaurant.reservations.show', $booking) }}">View</a>
                    @if($booking->status === 'pending')
                        <form method="POST" action="{{ route('restaurant.reservations.accept', $booking) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Accept</button></form><form method="POST" action="{{ route('restaurant.reservations.reject', $booking) }}" onsubmit="return confirm('Reject this request?');">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                    @endif
                </div></td></tr>
            @endforeach
            </tbody></table></div>
            @if($bookings->hasPages())<div class="p-3 border-top">{{ $bookings->links() }}</div>@endif
        @endif
    </div></div>
</div>
@endsection
