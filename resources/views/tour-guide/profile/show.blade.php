@extends('layouts.app')

@section('title', 'My Tour Guide Profile')

@section('content')
@php($verificationClass = match ($guide->verification_status) {
    'verified' => 'text-bg-success',
    'rejected' => 'text-bg-danger',
    default => 'text-bg-warning text-dark',
})
<div class="container py-4 py-lg-5"><div class="row g-4"><div class="col-lg-3">@include('tour-guide.partials.sidebar')</div><div class="col-lg-9"><nav aria-label="breadcrumb"><ol class="breadcrumb small"><li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">My Profile</li></ol></nav><div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="text-muted small text-uppercase mb-1">Tour Guide Profile</p><h1 class="h2 mb-0">Professional profile</h1></div><a class="btn btn-primary" href="{{ route('tour-guide.profile.edit') }}">Edit profile</a></div><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0">Profile details</h2></div><div class="card-body p-4"><dl class="row mb-0"><dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $guide->user?->email }}</dd><dt class="col-sm-4">License number</dt><dd class="col-sm-8">{{ $guide->license_number }} <span class="badge text-bg-light border text-dark ms-1">Platform controlled</span></dd><dt class="col-sm-4">Areas of expertise</dt><dd class="col-sm-8">{{ $guide->expertise }}</dd><dt class="col-sm-4">Daily Guide Rate</dt><dd class="col-sm-8">{{ $guide->daily_rate === null ? 'Not set' : number_format((float) $guide->daily_rate, 2).' ETB per day' }}</dd><dt class="col-sm-4">Availability status</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $guide->availability_status === 'available' ? 'success' : 'secondary' }}">{{ ucfirst($guide->availability_status) }}</span></dd><dt class="col-sm-4">Verification status</dt><dd class="col-sm-8"><span class="badge {{ $verificationClass }}">{{ ucfirst($guide->verification_status) }}</span> <span class="text-muted small">Bureau controlled</span></dd></dl></div><div class="card-footer bg-white small text-muted">You can update expertise, availability, and daily rate. License, role, and verification decisions cannot be changed here.</div></div></div></div></div>
@endsection
