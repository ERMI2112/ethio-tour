@extends('layouts.app')

@section('title', 'Museums')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-primary fw-semibold mb-1">Explore Ethiopia</p>
            <h1 class="h2 mb-1">Museums</h1>
            <p class="text-muted mb-0">Discover museums, collections, and cultural history from across Ethiopia.</p>
        </div>
        <form class="d-flex gap-2" method="GET" action="{{ route('museums.index') }}" role="search">
            <label class="visually-hidden" for="museum-search">Search museums</label>
            <input id="museum-search" name="q" value="{{ $search }}" class="form-control" placeholder="Search name, location...">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    @if ($museums->isEmpty())
        <x-ui.empty-state title="No museums found" message="Try a different search or check back soon for new museum information." />
    @else
        <div class="row g-4">
            @foreach ($museums as $museum)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5">{{ $museum->museum_name }}</h2>
                            <p class="text-muted small mb-2">{{ $museum->location }}</p>
                            <p class="mb-3">{{ $museum->description }}</p>
                            <div class="small text-muted mb-3">
                                <div><strong>Hours:</strong> {{ $museum->opening_hours }}</div>
                                @if ($museum->entrance_fee !== null)
                                    <div><strong>Entrance:</strong> {{ $museum->entrance_fee }} ETB</div>
                                @endif
                            </div>
                            <a class="btn btn-outline-primary mt-auto align-self-start" href="{{ route('museums.show', $museum) }}">View museum</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
