@extends('layouts.app')

@section('title', 'Destinations')

@section('content')
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div><h1 class="h2 mb-1">Destinations</h1><p class="text-muted mb-0">Discover Ethiopian tourism destinations and their heritage information.</p></div>
            <form class="d-flex gap-2" method="GET" action="{{ route('destinations.index') }}"><label class="visually-hidden" for="destination-search">Search destinations</label><input class="form-control" id="destination-search" name="q" placeholder="Search name or location" value="{{ $search }}"><x-ui.button variant="outline-primary" type="submit">Search</x-ui.button></form>
        </div>
        @if ($destinations->isEmpty())
            <x-ui.empty-state title="No destinations found" message="Try a different search term or return later for new tourism information." />
        @else
            <div class="row g-4">@foreach ($destinations as $destination)<div class="col-md-6 col-lg-4"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h2 class="h5"><a class="stretched-link text-decoration-none" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a></h2><p class="text-muted small mb-2">{{ $destination->location }}</p><p class="mb-3">{{ \Illuminate\Support\Str::limit($destination->description, 150) }}</p><div class="d-flex gap-3 text-muted small"><span>{{ $destination->heritage_sites_count }} heritage {{ \Illuminate\Support\Str::plural('site', $destination->heritage_sites_count) }}</span><span>{{ $destination->tourism_services_count }} {{ \Illuminate\Support\Str::plural('service', $destination->tourism_services_count) }}</span></div></div></div></div>@endforeach</div>
        @endif
    </div>
@endsection
