@extends('layouts.app')

@section('title', 'Tour Guide Dashboard')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            {{-- Header with Avatar --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="p-4 p-md-4" style="background: linear-gradient(135deg, #0d3824 0%, #051a10 100%); color: #fff;">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="{{ $guide->profileImageUrl() }}"
                                 alt="{{ $guide->full_name ?: ($guide->user?->name ?? 'Tour Guide') }}"
                                 class="rounded-circle border border-2 border-warning shadow-sm"
                                 style="width: 76px; height: 76px; object-fit: cover;">
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge bg-warning text-dark fw-bold">Tour Guide Portal</span>
                                <span class="badge bg-white bg-opacity-20 text-white border border-white-subtle">License {{ $guide->license_number }}</span>
                                @if($guide->destination)
                                    <span class="badge bg-success-subtle text-success">📍 {{ $guide->destination->name }}</span>
                                @endif
                            </div>
                            <h1 class="h3 text-white mb-1 fw-bold">
                                Welcome back, {{ $guide->full_name ?: $guide->user?->email }}
                            </h1>
                            <p class="text-white-50 small mb-0">
                                Daily rate: {{ $guide->daily_rate === null ? 'Not configured' : number_format((float) $guide->daily_rate, 2).' ETB' }} &bull; {{ $guide->user?->email }}
                            </p>
                        </div>
                        <div class="col-12 col-md-auto text-md-end">
                            <a class="btn btn-warning btn-sm fw-bold text-dark px-3" href="{{ route('tour-guide.profile.edit') }}">
                                Edit profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php($verificationClass = match ($guide->verification_status) {
                'verified' => 'text-bg-success',
                'rejected' => 'text-bg-danger',
                default => 'text-bg-warning text-dark',
            })
            <div class="alert alert-secondary d-flex gap-3 align-items-start mb-4" role="status">
                <span class="fw-semibold">Verification status</span>
                <span><span class="badge {{ $verificationClass }}">{{ ucfirst($guide->verification_status) }}</span> This Bureau-controlled decision cannot be changed from your profile.</span>
            </div>

            {{-- Attention State --}}
            <section aria-labelledby="attention-heading" class="mb-4">
                <h2 id="attention-heading" class="h5 mb-3">Needs attention</h2>
                @if($stats['pendingRequests'] > 0)
                    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <strong>{{ $stats['pendingRequests'] }} booking request(s) waiting</strong>
                            <div class="small">Review dates and availability before deciding.</div>
                        </div>
                        <a class="btn btn-warning btn-sm" href="{{ route('tour-guide.requests.index', ['status'=>'pending']) }}">Review requests</a>
                    </div>
                @elseif($guide->verification_status !== 'verified')
                    <div class="alert alert-info">Your profile is awaiting Bureau verification. Verification decisions are read-only here.</div>
                @else
                    <div class="alert alert-success"><strong>No booking requests are waiting.</strong> Check Availability before accepting new tours.</div>
                @endif
            </section>

            {{-- Stat Cards Grid --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Pending guide requests</p>
                            <p class="display-6 fw-bold mb-0">{{ $stats['pendingRequests'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Active guide bookings</p>
                            <p class="display-6 fw-bold mb-0">{{ $stats['activeBookings'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Completed bookings</p>
                            <p class="display-6 fw-bold mb-0">{{ $stats['completedBookings'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">Average rating</p>
                            <p class="display-6 fw-bold mb-0">{{ $stats['averageRating'] === null ? '—' : number_format((float) $stats['averageRating'], 1) }}</p>
                            <small class="text-muted">{{ $stats['reviewCount'] }} {{ $stats['reviewCount'] === 1 ? 'review' : 'reviews' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary & Quick Navigation --}}
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0 fw-bold">Profile summary</h2>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted small text-uppercase">License</dt>
                                <dd class="col-sm-8 fw-semibold">{{ $guide->license_number }}</dd>

                                <dt class="col-sm-4 text-muted small text-uppercase">Expertise</dt>
                                <dd class="col-sm-8">{{ $guide->expertise }}</dd>

                                <dt class="col-sm-4 text-muted small text-uppercase">Languages</dt>
                                <dd class="col-sm-8">
                                    @foreach($guide->languagesList() as $lang)
                                        <span class="badge bg-light text-dark border small">{{ $lang }}</span>
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4 text-muted small text-uppercase">Availability</dt>
                                <dd class="col-sm-8">
                                    <span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($guide->availability_status) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0 fw-bold">Quick actions</h2>
                        </div>
                        <div class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="{{ route('tour-guide.profile') }}">
                                <span>View my profile</span>
                                <span class="text-muted">&rarr;</span>
                            </a>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="{{ route('tour-guide.profile.edit') }}">
                                <span>Update expertise and availability</span>
                                <span class="text-muted">&rarr;</span>
                            </a>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="{{ route('tour-guide.reviews') }}">
                                <span>View traveler reviews</span>
                                <span class="badge bg-warning text-dark">{{ $stats['reviewCount'] }}</span>
                            </a>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="{{ route('tour-guide.earnings') }}">
                                <span>View earnings &amp; payouts</span>
                                <span class="badge bg-success-subtle text-success">{{ number_format((float) $stats['totalEarnings'], 0) }} ETB</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
