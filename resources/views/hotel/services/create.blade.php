@extends('layouts.app')

@section('title', 'Add Room Type')

@section('content')
<div class="container py-4 py-lg-5"><div class="row justify-content-center"><div class="col-xl-9">
<nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2"><li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('hotel.services.index') }}">Room-Type Services</a></li><li class="breadcrumb-item active" aria-current="page">Add</li></ol></nav>
<div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Add room-type service</h1><p class="text-muted mb-4">Create a Standard, Deluxe, or Suite offering for your hotel.</p>@if($destinations->isEmpty() || $categories->isEmpty())<div class="alert alert-warning" role="alert"><strong>Reference data is not available yet.</strong><br>A destination and category must be published by the Tourism Bureau before a room type can be created.</div>@else@include('hotel.services._form', ['formAction' => route('hotel.services.store'), 'submitLabel' => 'Create room type'])@endif</div></div></div></div></div>
@endsection
