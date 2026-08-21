@extends('layouts.app')

@section('title', 'Create Tour Package · Tour Guide')

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
                    <li class="breadcrumb-item active" aria-current="page">Create Package</li>
                </ol>
            </nav>

            <div class="mb-4">
                <p class="text-muted small text-uppercase mb-1">Excursion Builder</p>
                <h1 class="h2 mb-0 fw-bold">Create Multi-Day Tour Package</h1>
            </div>

            @include('layouts.partials.flash-messages')

            <form method="POST" action="{{ route('tour-guide.packages.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Basic Package Details -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h2 class="h5 fw-bold mb-0">1. Basic Information</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold" for="title">Package Title *</label>
                                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. 3-Day Simien Mountains Wilderness Trekking Expedition" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="destination_id">Primary Destination</label>
                                <select class="form-select @error('destination_id') is-invalid @enderror" id="destination_id" name="destination_id">
                                    <option value="">Select Destination (Optional)</option>
                                    @foreach($destinations as $dest)
                                        <option value="{{ $dest->destination_id }}" @selected(old('destination_id', $guide->primary_destination_id) == $dest->destination_id)>
                                            {{ $dest->name }} ({{ $dest->location }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="duration_days">Duration (in Days) *</label>
                                <input class="form-control @error('duration_days') is-invalid @enderror" id="duration_days" type="number" name="duration_days" value="{{ old('duration_days', 3) }}" min="1" max="30" required>
                                @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="price">Package Price (ETB) *</label>
                                <div class="input-group">
                                    <input class="form-control @error('price') is-invalid @enderror" id="price" type="number" step="0.01" name="price" value="{{ old('price', 5000) }}" min="0" required>
                                    <span class="input-group-text">ETB</span>
                                </div>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="max_group_size">Max Group Size (Guests) *</label>
                                <input class="form-control @error('max_group_size') is-invalid @enderror" id="max_group_size" type="number" name="max_group_size" value="{{ old('max_group_size', 8) }}" min="1" max="100" required>
                                @error('max_group_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="difficulty_level">Difficulty Level *</label>
                                <select class="form-select @error('difficulty_level') is-invalid @enderror" id="difficulty_level" name="difficulty_level" required>
                                    <option value="easy" @selected(old('difficulty_level') === 'easy')>Easy (Gentle walking)</option>
                                    <option value="moderate" @selected(old('difficulty_level', 'moderate') === 'moderate')>Moderate (Active trails)</option>
                                    <option value="challenging" @selected(old('difficulty_level') === 'challenging')>Challenging (High altitude / Rugged)</option>
                                </select>
                                @error('difficulty_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="description">Excursion Overview &amp; Highlights *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Describe the journey, what makes this excursion unique, scenic spots, and what travelers will learn." required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" for="cover_image">Cover Photography (Optional)</label>
                                <input class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" type="file" name="cover_image" accept="image/*">
                                <div class="form-text small">High-resolution landscape photo recommended (JPG, PNG, WebP up to 5MB).</div>
                                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Day-by-Day Itinerary Builder -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 fw-bold mb-0">2. Day-by-Day Itinerary</h2>
                            <p class="text-muted small mb-0">Break down the daily schedule and key experiences.</p>
                        </div>
                    </div>
                    <div class="card-body p-4" id="itinerary-days-container">
                        @for($i = 0; $i < 3; $i++)
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="fw-bold text-success mb-2">Day {{ $i + 1 }}</div>
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Day Title / Focus</label>
                                    <input class="form-control form-control-sm" name="itinerary_days[{{ $i }}][title]" value="{{ old('itinerary_days.'.$i.'.title', $i === 0 ? 'Arrival & Historic Castle Exploration' : ($i === 1 ? 'Highland Scenic Escarpment & Murals' : 'Traditional Ceremony & Farewell')) }}" placeholder="e.g. Departure from Debark & Jinbar Falls Hike">
                                </div>
                                <div>
                                    <label class="form-label small text-muted">Day Activities Description</label>
                                    <textarea class="form-control form-control-sm" name="itinerary_days[{{ $i }}][description]" rows="2" placeholder="Describe morning sights, afternoon trek, lunch stops, and evening camp or lodge details.">{{ old('itinerary_days.'.$i.'.description') }}</textarea>
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
                                <textarea class="form-control" id="included" name="included" rows="4" placeholder="Licensed English-speaking Tour Guide&#10;All National Park Entry Permits&#10;4x4 Transport from City Center&#10;Traditional Ethiopian Coffee Ceremony">{{ old('included', "Licensed Tour Guide Services\nAll Site Entry Permits\nLocal Transportation Coordination") }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="excluded">What is Excluded (1 per line)</label>
                                <textarea class="form-control" id="excluded" name="excluded" rows="4" placeholder="Personal Travel Insurance&#10;Alcoholic Beverages&#10;Gratuities for local scouts">{{ old('excluded', "Personal Travel Insurance\nGratuities / Tips\nSouvenirs & Personal Expenses") }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a class="btn btn-outline-secondary" href="{{ route('tour-guide.tours') }}">Cancel</a>
                    <button class="btn btn-success fw-bold px-4" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Publish Tour Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
