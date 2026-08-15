@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="museum_name">Museum name</label>
        <input id="museum_name" name="museum_name" value="{{ old('museum_name', $museum->museum_name ?? '') }}" class="form-control @error('museum_name') is-invalid @enderror" required>
        @error('museum_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="entrance_fee">Entrance fee (ETB)</label>
        <input id="entrance_fee" name="entrance_fee" type="number" min="0" step="0.01" value="{{ old('entrance_fee', $museum->entrance_fee ?? '') }}" class="form-control @error('entrance_fee') is-invalid @enderror">
        @error('entrance_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="location">Location</label>
        <input id="location" name="location" value="{{ old('location', $museum->location ?? '') }}" class="form-control @error('location') is-invalid @enderror" required>
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="opening_hours">Opening hours</label>
        <input id="opening_hours" name="opening_hours" value="{{ old('opening_hours', $museum->opening_hours ?? '') }}" class="form-control @error('opening_hours') is-invalid @enderror" required>
        @error('opening_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $museum->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="contact_information">Contact information</label>
        <input id="contact_information" name="contact_information" value="{{ old('contact_information', $museum->contact_information ?? '') }}" class="form-control @error('contact_information') is-invalid @enderror">
        @error('contact_information')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="media_path">Media link (optional)</label>
        <input id="media_path" name="media_path" value="{{ old('media_path', $museum->media_path ?? '') }}" class="form-control @error('media_path') is-invalid @enderror">
        @error('media_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
