@extends('layouts.auth')

@section('title', 'Create an account · Ethio Tour')

@section('auth-content')
<div class="auth-split-card auth-split-card--register shadow-lg" data-aos="fade-up">
    {{-- Left Side: Deep Emerald Showcase Banner --}}
    <div class="auth-showcase-side">
        <div class="auth-showcase-content">
            <span class="auth-brand-kicker">ETHIO TOUR</span>
            <h2 class="auth-showcase-title">Start your<br><em>journey.</em></h2>
            <p class="auth-showcase-desc">One account for every destination, every itinerary draft, and verified bookings across Ethiopia.</p>
        </div>
    </div>

    {{-- Right Side: Form --}}
    <div class="auth-form-side">
        <div class="auth-form-header">
            <h1 class="auth-form-title">Create an account</h1>
            <div class="auth-title-bar" aria-hidden="true"></div>
            <p class="auth-form-subtext">Join travelers, certified tour guides, and verified hospitality providers.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf

            {{-- Account Type --}}
            <div class="auth-field-group mb-3">
                <label class="auth-label" for="account_type">Account type</label>
                <select class="form-select auth-select @error('account_type') is-invalid @enderror"
                        id="account_type"
                        name="account_type"
                        required>
                    <option value="tourist" @selected(old('account_type') === 'tourist')>Tourist / Traveler</option>
                    <option value="tour_guide" @selected(old('account_type') === 'tour_guide')>Tour Guide</option>
                    <option value="service_provider" @selected(old('account_type') === 'service_provider')>Tourism Service Provider</option>
                </select>
                @error('account_type')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tourist-Specific Fields --}}
            <div id="tourist-fields">
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="full_name">Full name</label>
                    <div class="auth-input-wrap">
                        <input class="form-control auth-input"
                               id="full_name"
                               name="full_name"
                               value="{{ old('full_name') }}"
                               placeholder="e.g. Abebe Bekele">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="nationality">Nationality</label>
                    <div class="auth-input-wrap">
                        <input class="form-control auth-input"
                               id="nationality"
                               name="nationality"
                               value="{{ old('nationality') }}"
                               placeholder="e.g. Ethiopian">
                    </div>
                </div>
            </div>

            {{-- Guide-Specific Fields --}}
            <div id="guide-fields" class="d-none">
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="license_number">License number</label>
                    <div class="auth-input-wrap">
                        <input class="form-control auth-input"
                               id="license_number"
                               name="license_number"
                               value="{{ old('license_number') }}"
                               placeholder="e.g. TG-GDR-2024-001">
                    </div>
                </div>
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="expertise">Expertise &amp; Specialties</label>
                    <textarea class="form-control auth-input"
                              id="expertise"
                              name="expertise"
                              rows="2"
                              placeholder="e.g. Gondar castles, Simien trekking, Lalibela rock churches...">{{ old('expertise') }}</textarea>
                </div>
            </div>

            {{-- Provider-Specific Fields --}}
            <div id="provider-fields" class="d-none">
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="business_name">Business name</label>
                    <div class="auth-input-wrap">
                        <input class="form-control auth-input"
                               id="business_name"
                               name="business_name"
                               value="{{ old('business_name') }}"
                               placeholder="e.g. Goha Hotel">
                    </div>
                </div>
                <div class="auth-field-group mb-3">
                    <label class="auth-label" for="provider_type">Provider vertical</label>
                    <select class="form-select auth-select" id="provider_type" name="provider_type">
                        <option value="hotel">Hotel &amp; Lodging</option>
                        <option value="restaurant">Restaurant &amp; Dining</option>
                        <option value="transportation_car_rental">Transportation &amp; Car Rental</option>
                        <option value="event_organizer">Event Organizer</option>
                    </select>
                </div>
            </div>

            {{-- Email --}}
            <div class="auth-field-group mb-3">
                <label class="auth-label" for="reg_email">Email address</label>
                <div class="auth-input-wrap">
                    <input class="form-control auth-input @error('email') is-invalid @enderror"
                           id="reg_email"
                           name="email"
                           type="email"
                           value="{{ old('email') }}"
                           placeholder="name@example.com"
                           required>
                    <span class="auth-field-icon" aria-hidden="true">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                        </svg>
                    </span>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password & Confirmation --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="auth-field-group mb-0">
                        <label class="auth-label" for="reg_password">Password</label>
                        <div class="auth-input-wrap">
                            <input class="form-control auth-input"
                                   id="reg_password"
                                   name="password"
                                   type="password"
                                   placeholder="••••••••"
                                   required>
                            <button type="button"
                                    class="auth-password-toggle"
                                    aria-label="Toggle password visibility"
                                    onclick="toggleAuthPassword('reg_password', this)">
                                <svg class="toggle-icon-show" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Use 8 characters or more.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-field-group mb-0">
                        <label class="auth-label" for="password_confirmation">Confirm password</label>
                        <div class="auth-input-wrap">
                            <input class="form-control auth-input"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   placeholder="••••••••"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn auth-submit-btn mb-4" type="submit">Create account</button>
        </form>

        <div class="auth-footer-text">
            <span>Already have an account?</span>
            <a class="auth-link ms-1" href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
const accountTypeSelect = document.getElementById('account_type');
const roleFields = {
    tourist: document.getElementById('tourist-fields'),
    tour_guide: document.getElementById('guide-fields'),
    service_provider: document.getElementById('provider-fields')
};

function updateRoleFields() {
    if (!accountTypeSelect) return;
    Object.entries(roleFields).forEach(([key, field]) => {
        if (field) {
            field.classList.toggle('d-none', key !== accountTypeSelect.value);
        }
    });
}

if (accountTypeSelect) {
    accountTypeSelect.addEventListener('change', updateRoleFields);
    updateRoleFields();
}

function toggleAuthPassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword
        ? '<svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 4.5c4.289 0 7.39 3.018 7.915 3.5-.327.302-1.976 1.774-4.055 2.74l.5.498zm-3.255-3.255A3 3 0 0 0 8 5.5a3 3 0 0 0-2.74 1.765l.77.77A2 2 0 0 1 8 6.5a2 2 0 0 1 2 2c0 .356-.094.689-.255.978l.76.76z"/><path d="M.146.146a.5.5 0 0 1 .708 0l15 15a.5.5 0 0 1-.708.708l-1.5-1.5A8.5 8.5 0 0 1 8 16C3 16 0 8 0 8a13 13 0 0 1 3.14-3.832L.146.854a.5.5 0 0 1 0-.708M3.92 5.334l1.19 1.19A4.5 4.5 0 0 0 8 13.5c3.275 0 5.875-1.895 7.155-3.5-.327-.302-1.976-1.774-4.055-2.74l-.794-.794A3 3 0 0 1 8 7.5a3 3 0 0 1-2.89-2.166z"/></svg>'
        : '<svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>';
}
</script>
@endpush
@endsection
