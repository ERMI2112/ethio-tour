@extends('layouts.app')

@section('title', 'My Tour Packages & Excursions')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('tour-guide.partials.sidebar')
        </div>
        <div class="col-lg-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Tours</li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <p class="text-muted small text-uppercase mb-1">Guided Excursions Catalog</p>
                    <h1 class="h2 mb-0 fw-bold">Multi-Day Tour Packages</h1>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('tour-guides.show', $guide) }}" target="_blank">
                        View Public Profile &nearr;
                    </a>
                    <a class="btn btn-success btn-sm fw-bold" href="{{ route('tour-guide.packages.create') }}">
                        <i class="bi bi-plus-lg me-1"></i> Create Tour Package
                    </a>
                </div>
            </div>

            @include('layouts.partials.flash-messages')

            @if($packages->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center mb-4">
                    <div class="mb-3 fs-1">🏔️</div>
                    <h2 class="h4 fw-bold mb-2">No tour packages created yet</h2>
                    <p class="text-muted mb-4">
                        Create structured multi-day excursions (e.g. <em>3-Day Simien Trek</em>, <em>Gondar Castles &amp; Monasteries Circuit</em>) with day-by-day itineraries and fixed package pricing.
                    </p>
                    <div>
                        <a class="btn btn-success fw-bold px-4" href="{{ route('tour-guide.packages.create') }}">
                            <i class="bi bi-plus-circle me-1"></i> Create Your First Tour Package
                        </a>
                    </div>
                </div>
            @else
                <div class="row g-4 mb-4">
                    @foreach($packages as $pkg)
                        <div class="col-md-6">
                            <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                                <div class="position-relative" style="height: 180px; background: #0c1e14;">
                                    <img src="{{ $pkg->coverImageUrl() }}" alt="{{ $pkg->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div class="position-absolute top-0 start-0 m-3 d-flex gap-1">
                                        <span class="badge bg-dark bg-opacity-75 text-white border border-white-50">
                                            {{ $pkg->duration_days }} {{ \Illuminate\Support\Str::plural('Day', $pkg->duration_days) }}
                                        </span>
                                        <span class="badge bg-{{ $pkg->difficulty_level === 'easy' ? 'success' : ($pkg->difficulty_level === 'challenging' ? 'danger' : 'warning') }}-subtle text-{{ $pkg->difficulty_level === 'easy' ? 'success' : ($pkg->difficulty_level === 'challenging' ? 'danger' : 'dark') }} border">
                                            {{ ucfirst($pkg->difficulty_level) }}
                                        </span>
                                    </div>
                                    <div class="position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-{{ $pkg->is_active ? 'success' : 'secondary' }}-subtle text-{{ $pkg->is_active ? 'success' : 'secondary' }} border">
                                            {{ $pkg->is_active ? 'Published' : 'Hidden' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <h2 class="h5 mb-1 text-dark fw-bold">{{ $pkg->title }}</h2>
                                    <p class="small text-muted mb-2">
                                        📍 {{ $pkg->destination?->name ?? 'Ethiopia' }} &bull; Max {{ $pkg->max_group_size }} Guests
                                    </p>
                                    <p class="small text-secondary flex-grow-1 mb-3">
                                        {{ \Illuminate\Support\Str::limit($pkg->description, 140) }}
                                    </p>

                                    @if(!empty($pkg->itineraryList()))
                                        <div class="small text-muted mb-3 bg-light p-2 rounded">
                                            <i class="bi bi-list-check me-1"></i>
                                            {{ count($pkg->itineraryList()) }}-Day detailed itinerary mapped
                                        </div>
                                    @endif

                                    <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                        <div>
                                            <span class="fw-bold text-success fs-5">{{ $pkg->formattedPrice() }}</span>
                                            <small class="text-muted d-block">per excursion</small>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a class="btn btn-outline-primary btn-sm" href="{{ route('tour-guide.packages.edit', $pkg) }}">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('tour-guide.packages.toggle', $pkg) }}">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-outline-secondary btn-sm" type="submit" title="{{ $pkg->is_active ? 'Hide from public profile' : 'Publish on profile' }}">
                                                    {{ $pkg->is_active ? 'Hide' : 'Publish' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('tour-guide.packages.destroy', $pkg) }}" onsubmit="return confirm('Delete this tour package?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Info Card -->
            <div class="card border-0 shadow-sm bg-light p-4 rounded-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="h6 fw-bold mb-1">Public Excursion Showcase</h3>
                        <p class="small text-secondary mb-0">Published tour packages appear directly on your public guide profile so travelers can book multi-day excursions with 1 click.</p>
                    </div>
                    <a class="btn btn-outline-success btn-sm text-nowrap" href="{{ route('tour-guides.show', $guide) }}" target="_blank">
                        Preview Public Profile &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
