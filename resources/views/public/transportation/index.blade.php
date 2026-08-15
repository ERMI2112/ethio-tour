@extends('layouts.app')

@section('title', 'Transportation')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div><p class="text-uppercase small text-primary fw-semibold mb-1">Travel services</p><h1 class="h2 mb-1">Transportation &amp; Car Rental</h1><p class="text-muted mb-0">Compare vehicle rental and transport services from approved providers.</p></div>
        <form class="d-flex gap-2" method="GET" action="{{ route('transportation.index') }}"><label class="visually-hidden" for="transport-search">Search transportation</label><input id="transport-search" name="q" value="{{ $search }}" class="form-control" placeholder="Search services..."><button class="btn btn-primary">Search</button></form>
    </div>
    @if ($services->isEmpty())
        <x-ui.empty-state title="No transportation services found" message="Try another search or check back for newly published services." />
    @else
        <div class="row g-4">
            @foreach ($services as $service)
                <div class="col-md-6 col-xl-4"><article class="card h-100 border-0 shadow-sm"><div class="card-body d-flex flex-column"><h2 class="h5">{{ $service->service_name }}</h2><p class="small text-muted">{{ $service->serviceProvider->business_name }} · {{ $service->destination->name }}</p><p>{{ $service->description }}</p><div class="small mb-3"><strong>{{ number_format($service->price, 2) }} ETB</strong> per service period</div><a class="btn btn-outline-primary mt-auto align-self-start" href="{{ route('transportation.show', $service) }}">View vehicles</a></div></article></div>
            @endforeach
        </div>
    @endif
</div>
@endsection
