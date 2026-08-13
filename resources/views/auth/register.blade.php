@extends('layouts.auth')

@section('title', 'Register')

@section('auth-content')
    <h1 class="h3 mb-2">Create an account</h1>
    <p class="text-muted">Choose a permitted public account type. Bureau Officer and Administrator accounts are provisioned through controlled processes.</p>
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="mb-3"><label class="form-label" for="account_type">Account type</label><select class="form-select @error('account_type') is-invalid @enderror" id="account_type" name="account_type" required><option value="tourist" @selected(old('account_type') === 'tourist')>Tourist</option><option value="tour_guide" @selected(old('account_type') === 'tour_guide')>Tour Guide</option><option value="service_provider" @selected(old('account_type') === 'service_provider')>Tourism Service Provider</option></select>@error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required></div><div class="col-md-6 mb-3"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required></div></div>
        <div id="tourist-fields"><div class="mb-3"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}"></div><div class="mb-3"><label class="form-label" for="nationality">Nationality</label><input class="form-control" id="nationality" name="nationality" value="{{ old('nationality') }}"></div></div>
        <div id="guide-fields" class="d-none"><div class="mb-3"><label class="form-label" for="license_number">License number</label><input class="form-control" id="license_number" name="license_number" value="{{ old('license_number') }}"></div><div class="mb-3"><label class="form-label" for="expertise">Expertise</label><textarea class="form-control" id="expertise" name="expertise">{{ old('expertise') }}</textarea></div></div>
        <div id="provider-fields" class="d-none"><div class="mb-3"><label class="form-label" for="business_name">Business name</label><input class="form-control" id="business_name" name="business_name" value="{{ old('business_name') }}"></div><div class="mb-3"><label class="form-label" for="provider_type">Provider type</label><select class="form-select" id="provider_type" name="provider_type"><option value="hotel">Hotel</option><option value="restaurant">Restaurant</option><option value="transportation_car_rental">Transportation &amp; Car Rental</option><option value="event_organizer">Event Organizer</option></select></div></div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
    </form>
    <p class="text-center small mt-3 mb-0">Already registered? <a href="{{ route('login') }}">Log in</a></p>
    @push('scripts')<script>const type=document.getElementById('account_type'); const fields={tourist:document.getElementById('tourist-fields'),tour_guide:document.getElementById('guide-fields'),service_provider:document.getElementById('provider-fields')}; function updateFields(){Object.entries(fields).forEach(([key,field])=>field.classList.toggle('d-none',key!==type.value));} type.addEventListener('change',updateFields);updateFields();</script>@endpush
@endsection
