@extends('layouts.app')

@section('title', 'Edit Tour Package · '.$package->title)

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
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.tours') }}">My Tours</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit: {{ $package->title }}</li>
                </ol>
            </nav>

            <div class="mb-4">
                <p class="text-muted small text-uppercase mb-1">Excursion Editor</p>
                <h1 class="h2 mb-0 fw-bold">Edit Tour Package</h1>
            </div>

            @include('layouts.partials.flash-messages')

            <form method="POST" action="{{ route('tour-guide.packages.update', $package) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Package Details -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">1. Basic Information</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="title">Package Title *</label>
                                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $package->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="destination_id">Primary Destination</label>
                                <select class="form-select @error('destination_id') is-invalid @enderror" id="destination_id" name="destination_id">
                                    <option value="">Select Destination (Optional)</option>
                                    @foreach($destinations as $dest)
                                        <option value="{{ $dest->destination_id }}" @selected(old('destination_id', $package->destination_id) == $dest->destination_id)>
                                            {{ $dest->name }} ({{ $dest->location }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="duration_days">Duration (in Days) *</label>
                                <input class="form-control @error('duration_days') is-invalid @enderror" id="duration_days" type="number" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" min="1" max="30" required>
                                @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="price">Package Price (ETB) *</label>
                                <div class="input-group">
                                    <input class="form-control @error('price') is-invalid @enderror" id="price" type="number" step="0.01" name="price" value="{{ old('price', $package->price) }}" min="0" required>
                                    <span class="input-group-text">ETB</span>
                                </div>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="max_group_size">Max Group Size *</label>
                                <input class="form-control @error('max_group_size') is-invalid @enderror" id="max_group_size" type="number" name="max_group_size" value="{{ old('max_group_size', $package->max_group_size) }}" min="1" max="100" required>
                                @error('max_group_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="difficulty_level">Difficulty Level *</label>
                                <select class="form-select @error('difficulty_level') is-invalid @enderror" id="difficulty_level" name="difficulty_level" required>
                                    <option value="easy" @selected(old('difficulty_level', $package->difficulty_level) === 'easy')>Easy</option>
                                    <option value="moderate" @selected(old('difficulty_level', $package->difficulty_level) === 'moderate')>Moderate</option>
                                    <option value="challenging" @selected(old('difficulty_level', $package->difficulty_level) === 'challenging')>Challenging</option>
                                </select>
                                @error('difficulty_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="description">Excursion Overview *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $package->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="cover_image">Update Cover Photo (Optional)</label>
                                @if($package->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ $package->coverImageUrl() }}" alt="Current cover" class="rounded-3 shadow-sm" style="height: 100px; object-fit: cover;">
                                        <span class="small text-muted ms-2">Current cover image</span>
                                    </div>
                                @endif
                                <input class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" type="file" name="cover_image" accept="image/*">
                                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Day-by-Day Itinerary Builder -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">2. Day-by-Day Itinerary</h2>
                    </div>
                    <div class="card-body p-4">
                        @php($existingItinerary = old('itinerary_days', $package->itineraryList()))
                        @php($dayCount = max(count($existingItinerary), (int) old('duration_days', $package->duration_days)))
                        @for($i = 0; $i < $dayCount; $i++)
                            @php($dayData = $existingItinerary[$i] ?? [])
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="fw-bold text-success mb-2">Day {{ $i + 1 }}</div>
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Day Title / Focus</label>
                                    <input class="form-control form-control-sm" name="itinerary_days[{{ $i }}][title]" value="{{ $dayData['title'] ?? '' }}">
                                </div>
                                <div>
                                    <label class="form-label small text-muted">Activities Description</label>
                                    <textarea class="form-control form-control-sm" name="itinerary_days[{{ $i }}][description]" rows="2">{{ $dayData['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Inclusions & Exclusions -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">3. Inclusions &amp; Exclusions</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="included">What is Included (1 per line)</label>
                                <textarea class="form-control" id="included" name="included" rows="4">{{ old('included', implode("\n", $package->includedList())) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="excluded">What is Excluded (1 per line)</label>
                                <textarea class="form-control" id="excluded" name="excluded" rows="4">{{ old('excluded', implode("\n", $package->excludedList())) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a class="btn btn-outline-secondary" href="{{ route('tour-guide.tours') }}">Cancel</a>
                    <button class="btn btn-success fw-bold px-4" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
