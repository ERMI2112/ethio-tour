@extends('layouts.app')

@section('title', 'Hotel Profile')

@section('content')
@php
    $user = $provider->user;
@endphp
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-muted small mb-1 text-uppercase">Hotel Profile</p>
            <h1 class="h2 mb-0">{{ $provider->business_name }}</h1>
        </div>
        <a class="btn btn-primary" href="{{ route('hotel.profile.edit') }}">Edit profile</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Business profile</h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Business name</dt>
                        <dd class="col-sm-7">
                            {{ $provider->business_name }}
                            <span class="badge text-bg-primary ms-1">editable</span>
                        </dd>
                        <dt class="col-sm-5">Provider type</dt>
                        <dd class="col-sm-7 text-capitalize">{{ str_replace('_', ' ', $provider->provider_type) }}</dd>
                        <dt class="col-sm-5">Verification status</dt>
                        <dd class="col-sm-7">
                            <span class="badge text-bg-{{ $provider->status === 'approved' ? 'success' : 'secondary' }}">{{ ucfirst($provider->status) }}</span>
                        </dd>
                        <dt class="col-sm-5">Account state</dt>
                        <dd class="col-sm-7">
                            @if ($user?->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="card-footer bg-white py-3">
                    <p class="small text-muted mb-2">Only your business name is editable. Provider type, verification status and account state are controlled by the platform.</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('hotel.profile.edit') }}">Edit business name</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Inventory & reservations</h2>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('hotel.dashboard') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Dashboard</span><span class="text-muted small">Overview</span>
                    </a>
                    <a href="{{ route('hotel.services.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Room-type services</span><span class="text-muted small">Published offerings</span>
                    </a>
                    <a href="{{ route('hotel.rooms.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Physical rooms</span><span class="text-muted small">Inventory</span>
                    </a>
                    <a href="{{ route('hotel.reservations.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Reservations</span><span class="text-muted small">Manage requests</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
