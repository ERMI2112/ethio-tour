@extends('layouts.app')

@section('title', 'Tour Guide Dashboard')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <p class="text-muted small text-uppercase mb-1">Tour Guide Portal</p>
                    <h1 class="h2 mb-1">Welcome back, {{ $guide->user?->email }}</h1>
                    <p class="text-muted mb-0">License {{ $guide->license_number }} · Daily rate: {{ $guide->daily_rate === null ? 'Not set' : number_format((float) $guide->daily_rate, 2).' ETB' }}</p>
                </div>
                <a class="btn btn-primary" href="{{ route('tour-guide.profile.edit') }}">Edit profile</a>
            </div>

            @php($verificationClass = match ($guide->verification_status) {
                'verified' => 'text-bg-success',
                'rejected' => 'text-bg-danger',
                default => 'text-bg-warning text-dark',
            })
            <div class="alert alert-secondary d-flex gap-3 align-items-start" role="status">
                <span class="fw-semibold">Verification status</span>
                <span><span class="badge {{ $verificationClass }}">{{ ucfirst($guide->verification_status) }}</span> This Bureau-controlled decision cannot be changed from your profile.</span>
            </div>

            <section aria-labelledby="attention-heading" class="mb-4"><h2 id="attention-heading" class="h5 mb-3">Needs attention</h2>@if($stats['pendingRequests'] > 0)<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>{{ $stats['pendingRequests'] }} booking request(s) waiting</strong><div class="small">Review dates and availability before deciding.</div></div><a class="btn btn-warning" href="{{ route('tour-guide.requests.index', ['status'=>'pending']) }}">Review requests</a></div>@elseif($guide->verification_status !== 'verified')<div class="alert alert-info">Your profile is awaiting Bureau verification. Verification decisions are read-only here.</div>@else<div class="alert alert-success"><strong>No booking requests are waiting.</strong> Check Availability before accepting new tours.</div>@endif</section>
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-1">Pending guide requests</p><p class="display-6 fw-bold mb-0">{{ $stats['pendingRequests'] }}</p></div></div></div>
                <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-1">Active guide bookings</p><p class="display-6 fw-bold mb-0">{{ $stats['activeBookings'] }}</p></div></div></div>
                <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-1">Completed bookings</p><p class="display-6 fw-bold mb-0">{{ $stats['completedBookings'] }}</p></div></div></div>
                <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-muted small mb-1">Average rating</p><p class="display-6 fw-bold mb-0">{{ $stats['averageRating'] === null ? '—' : number_format((float) $stats['averageRating'], 1) }}</p><small class="text-muted">{{ $stats['reviewCount'] }} {{ $stats['reviewCount'] === 1 ? 'review' : 'reviews' }}</small></div></div></div>
            </div>

            <div class="row g-4">
                <div class="col-md-7"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Profile summary</h2></div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-4">License</dt><dd class="col-sm-8">{{ $guide->license_number }}</dd><dt class="col-sm-4">Expertise</dt><dd class="col-sm-8">{{ $guide->expertise }}</dd><dt class="col-sm-4">Availability</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }}">{{ ucfirst($guide->availability_status) }}</span></dd></dl></div></div></div>
                <div class="col-md-5"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Quick actions</h2></div><div class="list-group list-group-flush"><a class="list-group-item list-group-item-action" href="{{ route('tour-guide.profile') }}">View my profile</a><a class="list-group-item list-group-item-action" href="{{ route('tour-guide.profile.edit') }}">Update expertise and availability</a></div><div class="card-footer bg-white small text-muted">Booking requests, schedule management, reviews, and earnings tools will be introduced in later phases.</div></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
