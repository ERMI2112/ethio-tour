@extends('layouts.app')

@section('title', 'Edit Tour Guide Profile')

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
                    <li class="breadcrumb-item"><a href="{{ route('tour-guide.profile') }}">My Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h1 class="h4 mb-0 fw-bold">Edit Professional Profile</h1>
                    <p class="text-muted small mb-0">Keep your public expertise, photo, languages, rates, and availability up to date.</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tour-guide.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Profile Photo --}}
                        <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                            <div class="col-auto">
                                <img src="{{ $guide->profileImageUrl() }}"
                                     alt="{{ $guide->user?->name ?? 'Tour Guide' }}"
                                     class="rounded-circle border border-2 shadow-sm"
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <div class="col">
                                <label class="form-label fw-semibold mb-1" for="profile_image">Profile Picture / Avatar</label>
                                <input class="form-control @error('profile_image') is-invalid @enderror"
                                       id="profile_image"
                                       name="profile_image"
                                       type="file"
                                       accept="image/jpeg,image/png,image/jpg,image/webp">
                                <div class="form-text">Recommended: Square headshot (JPG, PNG, WebP up to 4MB).</div>
                                @error('profile_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 2: Core Details --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="daily_rate">Daily Guide Rate (ETB)</label>
                                <div class="input-group">
                                    <input class="form-control @error('daily_rate') is-invalid @enderror"
                                           id="daily_rate"
                                           name="daily_rate"
                                           type="number"
                                           min="0"
                                           step="0.01"
                                           placeholder="e.g. 2000"
                                           value="{{ old('daily_rate', $guide->daily_rate) }}">
                                    <span class="input-group-text">ETB / day</span>
                                    @error('daily_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="availability_status">Availability Status</label>
                                <select class="form-select @error('availability_status') is-invalid @enderror"
                                        id="availability_status"
                                        name="availability_status"
                                        required>
                                    <option value="available" @selected(old('availability_status', $guide->availability_status) === 'available')>Available for Bookings</option>
                                    <option value="unavailable" @selected(old('availability_status', $guide->availability_status) === 'unavailable')>Currently Unavailable / Blocked</option>
                                </select>
                                @error('availability_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="phone_number">Contact Phone Number</label>
                                <input class="form-control @error('phone_number') is-invalid @enderror"
                                       id="phone_number"
                                       name="phone_number"
                                       type="tel"
                                       placeholder="+251 91 123 4567"
                                       value="{{ old('phone_number', $guide->phone_number) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="years_of_experience">Years of Experience</label>
                                <input class="form-control @error('years_of_experience') is-invalid @enderror"
                                       id="years_of_experience"
                                       name="years_of_experience"
                                       type="number"
                                       min="0"
                                       max="50"
                                       placeholder="e.g. 8"
                                       value="{{ old('years_of_experience', $guide->years_of_experience) }}">
                                @error('years_of_experience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="primary_destination_id">Primary Operating Destination</label>
                                <select class="form-select @error('primary_destination_id') is-invalid @enderror"
                                        id="primary_destination_id"
                                        name="primary_destination_id">
                                    <option value="">-- Select Destination Circuit --</option>
                                    @foreach($destinations as $dest)
                                        <option value="{{ $dest->destination_id }}"
                                            @selected(old('primary_destination_id', $guide->primary_destination_id) == $dest->destination_id)>
                                            {{ $dest->name }} ({{ $dest->location }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('primary_destination_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="specialties">Specialty Tags</label>
                                <input class="form-control @error('specialties') is-invalid @enderror"
                                       id="specialties"
                                       name="specialties"
                                       type="text"
                                       placeholder="UNESCO Heritage, Trekking, Coffee Ceremony, Photography"
                                       value="{{ old('specialties', is_array($guide->specialties) ? implode(', ', $guide->specialties) : $guide->specialties) }}">
                                <div class="form-text">Comma-separated tags.</div>
                                @error('specialties')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Languages Spoken --}}
                        <div class="mb-4 pb-4 border-bottom">
                            <label class="form-label fw-semibold d-block mb-2">Languages Spoken</label>
                            @php
                                $availableLanguages = ['Amharic', 'English', 'French', 'German', 'Italian', 'Spanish', 'Oromo', 'Tigrinya', 'Arabic', 'Mandarin'];
                                $currentLangs = $guide->languagesList();
                            @endphp
                            <div class="row g-2">
                                @foreach($availableLanguages as $lang)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="languages[]"
                                                   value="{{ $lang }}"
                                                   id="lang_{{ $lang }}"
                                                   @checked(in_array($lang, (array) old('languages', $currentLangs)))>
                                            <label class="form-check-label" for="lang_{{ $lang }}">
                                                {{ $lang }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Section 4: Narrative Expertise & Biography --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="expertise">Areas of Expertise</label>
                            <textarea class="form-control @error('expertise') is-invalid @enderror"
                                      id="expertise"
                                      name="expertise"
                                      rows="3"
                                      required>{{ old('expertise', $guide->expertise) }}</textarea>
                            <div class="form-text">Highlight the circuits, historical knowledge, and types of tours you lead.</div>
                            @error('expertise')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="bio">Detailed Professional Biography</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror"
                                      id="bio"
                                      name="bio"
                                      rows="5"
                                      placeholder="Introduce yourself to travelers, share your journey as a licensed guide, and explain what makes your tours unique and authentic...">{{ old('bio', $guide->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2 pt-2 border-top mb-4">
                            <button class="btn btn-primary px-4" type="submit">Save profile changes</button>
                            <a class="btn btn-outline-secondary px-3" href="{{ route('tour-guide.profile') }}">Cancel</a>
                        </div>
                    </form>

                        <div class="card border rounded-3 mb-4">
                            <div class="card-header bg-white"><h2 class="h6 mb-0">Verification documents</h2></div>
                            <div class="card-body">
                                <p class="small text-muted">Upload a readable license and identity document. Files are stored privately and reviewed by the Tourism Bureau.</p>
                                <form method="POST" action="{{ route('tour-guide.verification-documents.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end mb-3">
                                    @csrf
                                    <div class="col-md-4"><label class="form-label" for="guide_document_type">Document type</label><select class="form-select" id="guide_document_type" name="document_type" required><option value="license">Professional license</option><option value="identity">Identity document</option><option value="other">Other</option></select></div>
                                    <div class="col-md-5"><label class="form-label" for="guide_document">File</label><input class="form-control" id="guide_document" type="file" name="document" accept="application/pdf,image/jpeg,image/png,image/webp" required></div>
                                    <div class="col-md-3"><button class="btn btn-outline-primary w-100" type="submit">Upload document</button></div>
                                </form>
                                @forelse($guide->verificationDocuments as $document)<div class="d-flex justify-content-between align-items-center border-top py-2"><span class="small">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }} · {{ $document->original_name }}</span><x-ui.status-badge :status="$document->status" /></div>@empty<div class="small text-muted">No documents uploaded yet.</div>@endforelse
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
