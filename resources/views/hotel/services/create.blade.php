@extends('layouts.app')

@section('title', 'Add Room Type')

@section('content')
<div class="container py-5"><div class="row justify-content-center"><div class="col-xl-9"><a class="small text-decoration-none" href="{{ route('hotel.services.index') }}">&larr; Room-type services</a><div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Add room-type service</h1><p class="text-muted mb-4">Create a Standard, Deluxe, or Suite offering for your hotel.</p>@include('hotel.services._form', ['formAction' => route('hotel.services.store'), 'submitLabel' => 'Create room type'])</div></div></div></div></div>
@endsection
