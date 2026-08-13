@extends('layouts.app')
@section('title', $tourismService->service_name)
@section('content')
    <div class="container py-5"><a class="text-decoration-none small" href="{{ route('tourism-services.index') }}">&larr; All tourism services</a><article class="card border-0 shadow-sm mt-3"><div class="card-body p-4 p-md-5"><p class="text-uppercase text-muted small"><a href="{{ route('categories.index') }}">{{ $tourismService->category->category_name }}</a> · <a href="{{ route('destinations.show', $tourismService->destination) }}">{{ $tourismService->destination->name }}</a></p><h1 class="h2">{{ $tourismService->service_name }}</h1><p class="text-muted">Provided by {{ $tourismService->serviceProvider->business_name }}</p><p>{{ $tourismService->description }}</p><div class="border-top pt-3"><strong>Price:</strong> {{ number_format($tourismService->price, 2) }}</div></div></article></div>
@endsection
