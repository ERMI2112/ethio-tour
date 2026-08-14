@extends('layouts.app')

@section('title', 'Hotel Management')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><p class="text-muted mb-1">Hotel provider area</p><h1 class="h2 mb-0">{{ $provider->business_name }}</h1></div>
        <a class="btn btn-primary" href="{{ route('hotel.profile.edit') }}">Edit profile</a>
    </div>
    <div class="row g-4">
        <div class="col-md-5"><div class="card border-0 shadow-sm h-100"><div class="card-body"><h2 class="h5">Provider profile</h2><dl class="row mb-0"><dt class="col-sm-5">Business name</dt><dd class="col-sm-7">{{ $provider->business_name }}</dd><dt class="col-sm-5">Provider type</dt><dd class="col-sm-7 text-capitalize">{{ str_replace('_', ' ', $provider->provider_type) }}</dd><dt class="col-sm-5">Status</dt><dd class="col-sm-7"><span class="badge text-bg-secondary">{{ $provider->status }}</span></dd></dl></div></div></div>
        <div class="col-md-7"><div class="card border-0 shadow-sm h-100"><div class="card-body"><h2 class="h5">Inventory management</h2><p class="text-muted">Manage room-type services and physical rooms. Booking and availability workflows will be added later.</p><div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('hotel.services.index') }}">Room-type services</a><a class="btn btn-outline-primary" href="{{ route('hotel.rooms.index') }}">Physical rooms</a></div></div></div></div>
    </div>
</div>
@endsection
