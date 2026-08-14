@extends('layouts.app')

@section('title', 'Edit Room Type')

@section('content')
<div class="container py-4 py-lg-5"><div class="row justify-content-center"><div class="col-xl-9">
<nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2"><li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('hotel.services.index') }}">Room-Type Services</a></li><li class="breadcrumb-item active" aria-current="page">Edit</li></ol></nav>
<div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Edit {{ $service->service_name }}</h1><p class="text-muted mb-4">Update the room type's name, price, details and capacity. Physical rooms are managed separately.</p>@include('hotel.services._form', ['formAction' => route('hotel.services.update', $service), 'submitLabel' => 'Save changes'])</div></div></div></div></div>
@endsection
