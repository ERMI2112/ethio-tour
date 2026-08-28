@extends('layouts.app')

@section('title', 'Restaurant Portal · '.$provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    <div class="ws-page-header workspace-page-header mb-4 pb-3 border-bottom"><div><span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Restaurant portal</span><h1 class="ws-title mb-1">{{ $provider->business_name }}</h1><p class="ws-lead mb-0">{{ $provider->user?->email }}</p></div><div class="d-flex gap-2"><a class="btn btn-dark btn-sm" href="{{ route('restaurant.services.create') }}">Add service</a><a class="btn btn-outline-secondary btn-sm" href="{{ route('restaurant.profile') }}">Profile</a></div></div>
    <h2 class="h5 mb-3">Needs attention</h2>
    @if($stats['pendingReservations'] > 0)<div class="alert alert-warning">{{ $stats['pendingReservations'] }} reservation request(s) are waiting. <a href="{{ route('restaurant.reservations.index', ['status' => 'pending']) }}">Review reservations</a></div>@else<div class="alert alert-success">No restaurant reservation requests are waiting.</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Services" icon="card-list" :value="$stats['serviceCount']" hint="Published restaurant offerings" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Tables" icon="grid-3x3-gap" :value="$stats['activeTables'].' / '.$stats['tableCount']" hint="Active / total tables" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="This month revenue" icon="cash-stack" :value="number_format($stats['monthlyRevenue'], 2).' ETB'" hint="Confirmed and completed bookings" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Reviews" icon="star" :value="$stats['reviewCount'] > 0 ? number_format($stats['reviewAverage'], 1).' / 5' : 'No reviews yet'" :hint="$stats['reviewCount'] > 0 ? $stats['reviewCount'].' recorded' : 'No review records'" /></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white d-flex justify-content-between"><div><h2 class="h5 mb-0">Menu performance</h2><small class="text-muted">Booking counts from current records</small></div><a href="{{ route('restaurant.services.index') }}" class="small">Manage</a></div><div class="list-group list-group-flush">@forelse($dishPerformance as $dish)<div class="list-group-item"><div class="d-flex justify-content-between"><strong>{{ $dish['name'] }}</strong><span>{{ $dish['orders'] }} booking(s)</span></div><div class="progress mt-2" style="height: 6px"><div class="progress-bar" style="width: {{ $dish['percentage'] }}%"></div></div><small class="text-muted">{{ number_format($dish['price'], 2) }} ETB · {{ $dish['category'] }}</small></div>@empty<div class="list-group-item text-muted">No restaurant services have been added yet.</div>@endforelse</div></div></div>
        <div class="col-lg-5"><div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Recent reservations</h2></div><div class="list-group list-group-flush">@forelse($recentReservations as $booking)<div class="list-group-item"><strong>{{ $booking->tourismService?->service_name ?: 'Restaurant service' }}</strong><small class="d-block text-muted">{{ $booking->tourist?->full_name ?: $booking->tourist?->user?->email ?: 'Tourist' }} · {{ $booking->restaurantReservation?->guest_count ?: 0 }} guest(s)</small><div class="mt-2"><x-ui.status-badge :status="$booking->status" /></div></div>@empty<div class="list-group-item text-muted">No restaurant reservations recorded yet.</div>@endforelse</div><div class="card-footer bg-white"><a href="{{ route('restaurant.reservations.index') }}" class="small">View all reservations</a></div></div></div>
    </div>
</div>
@endsection
