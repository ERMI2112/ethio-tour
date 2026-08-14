@extends('layouts.app')

@section('title', 'Hotel Dashboard')

@section('content')
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hotel Dashboard</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-muted small mb-1 text-uppercase">Hotel Portal</p>
            <h1 class="h2 mb-1">{{ $provider->business_name }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge text-bg-primary text-capitalize">{{ str_replace('_', ' ', $provider->provider_type) }}</span>
                <span class="badge text-bg-{{ $provider->status === 'approved' ? 'success' : 'secondary' }}">{{ ucfirst($provider->status) }}</span>
            </div>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('hotel.profile') }}">View profile</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Room-type services</p>
                    <p class="display-6 fw-bold mb-0">{{ $stats['roomTypeCount'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Physical rooms</p>
                    <p class="display-6 fw-bold mb-0">{{ $stats['totalRooms'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Active rooms</p>
                    <p class="display-6 fw-bold text-success mb-0">{{ $stats['activeRooms'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Inactive rooms</p>
                    <p class="display-6 fw-bold text-muted mb-0">{{ $stats['inactiveRooms'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Reservation summary</h2>
                    <a href="{{ route('hotel.reservations.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @php($r = $stats['reservations'])
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'pending'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['pending'] }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'accepted'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['accepted'] }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'payment_pending'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['payment_pending'] }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'confirmed'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['confirmed'] }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'rejected'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['rejected'] }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 rounded border bg-light">
                                <x-ui.status-badge :status="'cancelled'" />
                                <p class="display-6 fw-bold mt-2 mb-0">{{ $r['cancelled'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($stats['pendingAttention'] > 0)
                        <div class="alert alert-warning mt-4 mb-0 small">
                            <a href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}" class="alert-link fw-semibold">{{ $stats['pendingAttention'] }} reservation request(s)</a>
                            are awaiting your decision.
                        </div>
                    @else
                        <div class="alert alert-success mt-4 mb-0 small">
                            No reservation requests are currently awaiting your decision.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Upcoming stays</p>
                        <p class="display-6 fw-bold mb-0">{{ $stats['upcomingStays'] }}</p>
                        <p class="small text-muted mb-0">accepted or confirmed stays with a future check-in</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Manage your hotel</h2>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('hotel.services.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Room-type services</span>
                        <span class="badge text-bg-secondary rounded-pill">{{ $stats['roomTypeCount'] }}</span>
                    </a>
                    <a href="{{ route('hotel.rooms.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Physical rooms</span>
                        <span class="badge text-bg-secondary rounded-pill">{{ $stats['totalRooms'] }}</span>
                    </a>
                    <a href="{{ route('hotel.reservations.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Reservations</span>
                        <span class="badge text-bg-secondary rounded-pill">{{ array_sum($stats['reservations']) }}</span>
                    </a>
                    <a href="{{ route('hotel.profile') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Profile</span>
                        <span class="badge text-bg-secondary rounded-pill">Edit</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection