@extends('layouts.app')

@section('title', 'Edit Attraction · '.$attraction->name)

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('bureau.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('bureau.attractions.index') }}">Attractions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit: {{ $attraction->name }}</li>
                </ol>
            </nav>

            <div class="mb-4">
                <span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Regional Heritage Curation</span>
                <h1 class="ws-title">Edit Heritage Site / Attraction</h1>
                <p class="ws-lead">Update operational hours, entry tariffs, coordinates, and visitor details for {{ $attraction->name }}.</p>
            </div>

            @include('layouts.partials.flash-messages')

            <form method="POST" action="{{ route('bureau.attractions.update', $attraction) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">1. Basic Attraction Details</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="destination_id">Destination Jurisdiction *</label>
                                <select class="form-select @error('destination_id') is-invalid @enderror" id="destination_id" name="destination_id" required>
                                    @foreach($destinations as $d)
                                        <option value="{{ $d->destination_id }}" @selected(old('destination_id', $attraction->destination_id) == $d->destination_id)>{{ $d->name }} ({{ $d->location }})</option>
                                    @endforeach
                                </select>
                                @error('destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="category">Attraction Category *</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" @selected(old('category', $attraction->category) === $cat)>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="name">Attraction / Heritage Site Name *</label>
                                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $attraction->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="description">Historical &amp; Cultural Description *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $attraction->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $attraction->is_featured))>
                                    <label class="form-check-label fw-bold" for="is_featured">Highlight as Featured Attraction on destination landing pages</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & GPS Coordinates -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">2. Location &amp; Map Coordinates</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="location_address">Physical Address / Landmark Reference</label>
                                <input class="form-control @error('location_address') is-invalid @enderror" id="location_address" name="location_address" value="{{ old('location_address', $attraction->location_address) }}">
                                @error('location_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="latitude">Latitude (Decimal Degrees)</label>
                                <input class="form-control @error('latitude') is-invalid @enderror" id="latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $attraction->latitude) }}">
                                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="longitude">Longitude (Decimal Degrees)</label>
                                <input class="form-control @error('longitude') is-invalid @enderror" id="longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $attraction->longitude) }}">
                                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operating Hours & Admission -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">3. Visiting Hours &amp; Entry Tariffs</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="opening_hours">Opening Hours</label>
                                <input class="form-control @error('opening_hours') is-invalid @enderror" id="opening_hours" name="opening_hours" value="{{ old('opening_hours', $attraction->opening_hours) }}">
                                @error('opening_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="entry_fee">Admission Tariff (ETB)</label>
                                <div class="input-group">
                                    <input class="form-control @error('entry_fee') is-invalid @enderror" id="entry_fee" type="number" step="0.01" name="entry_fee" value="{{ old('entry_fee', $attraction->entry_fee) }}">
                                    <span class="input-group-text">ETB</span>
                                </div>
                                @error('entry_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photography -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">4. Photography</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="mb-2">
                                    <img src="{{ $attraction->primaryImageUrl() }}" alt="{{ $attraction->name }}" class="rounded-3 shadow-sm" style="max-height: 120px; object-fit: cover;">
                                    <span class="small text-muted ms-2">Current primary photography</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="image">Replace Photo File (JPG, PNG, WebP up to 5MB)</label>
                                <input class="form-control @error('image') is-invalid @enderror" id="image" type="file" name="image" accept="image/*">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="image_url">Or Photo Asset URL</label>
                                <input class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="e.g. /images/destinations/gondar-castles.jpg">
                                @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a class="btn btn-outline-secondary" href="{{ route('bureau.attractions.index') }}">Cancel</a>
                    <button class="btn btn-success fw-bold px-4" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Save Attraction Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
