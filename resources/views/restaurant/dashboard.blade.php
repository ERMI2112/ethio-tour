@extends('layouts.app')

@section('title', 'Restaurant Dashboard')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><p class="text-muted small text-uppercase mb-1">Restaurant Portal</p><h1 class="h2 mb-1">Welcome, {{ $provider->business_name }}</h1><p class="text-muted mb-0">Manage your menu offerings, tables, and reservation requests.</p></div>
        <a class="btn btn-primary" href="{{ route('restaurant.profile') }}">View profile</a>
    </div>
    <section aria-labelledby="attention-heading" class="mb-4"><h2 id="attention-heading" class="h5 mb-3">Needs attention</h2>@if($stats['pendingReservations'] > 0)<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>{{ $stats['pendingReservations'] }} reservation request(s) waiting</strong><div class="small">Review time and table availability before accepting.</div></div><a class="btn btn-warning" href="{{ route('restaurant.reservations.index', ['status'=>'pending']) }}">Review reservations</a></div>@else<div class="alert alert-success"><strong>No reservation requests are waiting.</strong> New requests will appear in Reservations.</div>@endif</section>
    <div class="row g-3 mb-4">
        @foreach ([['Services', $stats['serviceCount'], 'restaurant.services.index'], ['Tables', $stats['tableCount'], 'restaurant.tables.index'], ['Active tables', $stats['activeTables'], 'restaurant.tables.index'], ['Pending reservations', $stats['pendingReservations'], 'restaurant.reservations.index']] as [$label, $value, $route])
            <div class="col-sm-6 col-xl-3"><a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route($route) }}"><div class="card-body"><div class="small text-muted">{{ $label }}</div><div class="display-6 fw-semibold text-primary">{{ $value }}</div></div></a></div>
        @endforeach
    </div>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3"><div><h2 class="h6 mb-1">Guest feedback</h2><p class="text-muted small mb-0">Reviews received across your restaurant services.</p></div><x-reviews.rating-summary :average="$stats['reviewAverage']" :count="$stats['reviewCount']" /></div></div>
    <div class="row g-4">
        <div class="col-lg-8"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Restaurant management</h2></div><div class="list-group list-group-flush"><a class="list-group-item list-group-item-action" href="{{ route('restaurant.services.index') }}">Menu and service offerings <span class="text-muted small d-block">Publish and update restaurant services through the shared TourismService model.</span></a><a class="list-group-item list-group-item-action" href="{{ route('restaurant.tables.index') }}">Table inventory <span class="text-muted small d-block">Maintain physical table numbers, capacities, and active status.</span></a><a class="list-group-item list-group-item-action" href="{{ route('restaurant.reservations.index') }}">Reservation requests <span class="text-muted small d-block">Review requests belonging only to your restaurant.</span></a></div></div></div>
        <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Reservation workflow</h2></div><div class="card-body"><p class="small text-muted mb-0">Accepting a reservation allocates a table. The tourist then completes payment through the central booking flow; notifications and reviews remain connected to that booking.</p></div></div></div>
    </div>
</div>
@endsection
