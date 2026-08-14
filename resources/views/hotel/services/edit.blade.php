@extends('layouts.app')

@section('title', 'Edit Room Type')

@section('content')
<div class="container py-5"><div class="row justify-content-center"><div class="col-xl-9"><a class="small text-decoration-none" href="{{ route('hotel.services.index') }}">&larr; Room-type services</a><div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Edit {{ $service->service_name }}</h1><p class="text-muted mb-4">Update the generic service and its hotel room-type details.</p>@include('hotel.services._form', ['formAction' => route('hotel.services.update', $service), 'submitLabel' => 'Save changes'])</div></div></div></div></div>
@endsection
