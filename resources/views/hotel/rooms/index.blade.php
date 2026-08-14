@extends('layouts.app')

@section('title', 'Physical Rooms')

@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Physical Rooms</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Physical rooms</h1>
            <p class="text-muted mb-0">Active rooms are operational inventory. Date-specific availability is handled automatically at booking time.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('hotel.rooms.create') }}">Add physical room</a>
    </div>

    @if($rooms->isEmpty())
        <x-ui.empty-state title="No physical rooms yet" message="Add rooms under your existing room-type services, or create a room-type service first." />
    @else
        @foreach($roomTypes as $roomType)
            @if ($roomType->hotelRooms->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="text-uppercase text-muted small me-2">Room type</span>
                            <strong class="fs-5">{{ $roomType->tourismService->service_name }}</strong>
                            <span class="text-muted small ms-2">{{ number_format($roomType->tourismService->price, 2) }} ETB / night</span>
                        </div>
                        <span class="badge text-bg-light border">Capacity: {{ $roomType->capacity }} guest(s)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Physical inventory</th>
                                    <th>Room number</th>
                                    <th>Status</th>
                                    <th>Reservation history</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roomType->hotelRooms as $room)
                                    <tr>
                                        <td><span class="text-uppercase text-muted small">Room #{{ $room->room_number }}</span></td>
                                        <td><strong>{{ $room->room_number }}</strong></td>
                                        <td>
                                            <span class="badge text-bg-{{ $room->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($room->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($room->hotel_room_reservations_count > 0)
                                                <span class="badge bg-light text-dark border">{{ $room->hotel_room_reservations_count }} reservation(s)</span>
                                            @else
                                                <span class="text-muted small">No history</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('hotel.rooms.edit', $room) }}">Edit</a>
                                            <form class="d-inline" method="POST" action="{{ route('hotel.rooms.destroy', $room) }}" onsubmit="return confirm('Remove room {{ $room->room_number }}? Rooms with reservation history cannot be removed; mark them inactive instead.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
@endsection
