@extends('layouts.app')

@section('title', 'Tour Guide Settings')

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
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h1 class="h4 mb-0 fw-bold">Operational Settings</h1>
                    <p class="text-muted small mb-0">Configure your booking rate, contact preferences, and calendar availability.</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tour-guide.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="daily_rate">Default Daily Guide Rate (ETB)</label>
                                <div class="input-group">
                                    <input class="form-control @error('daily_rate') is-invalid @enderror"
                                           id="daily_rate"
                                           name="daily_rate"
                                           type="number"
                                           min="0"
                                           step="0.01"
                                           value="{{ old('daily_rate', $guide->daily_rate) }}">
                                    <span class="input-group-text">ETB / day</span>
                                    @error('daily_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">This rate is frozen at booking creation for all new tourist requests.</div>
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
                                <label class="form-label fw-semibold" for="availability_status">Booking Availability Mode</label>
                                <select class="form-select @error('availability_status') is-invalid @enderror"
                                        id="availability_status"
                                        name="availability_status"
                                        required>
                                    <option value="available" @selected(old('availability_status', $guide->availability_status) === 'available')>
                                        Active &bull; Accepting New Tourist Requests
                                    </option>
                                    <option value="unavailable" @selected(old('availability_status', $guide->availability_status) === 'unavailable')>
                                        Paused &bull; Block New Requests (Vacation / Fully Booked)
                                    </option>
                                </select>
                                @error('availability_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex gap-2">
                            <button class="btn btn-primary px-4" type="submit">Save settings</button>
                            <a class="btn btn-outline-secondary" href="{{ route('tour-guide.dashboard') }}">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light p-4">
                <h2 class="h6 fw-bold mb-2">Bureau Accreditation &amp; Governance</h2>
                <p class="small text-secondary mb-0">
                    License ID <strong class="text-dark">{{ $guide->license_number }}</strong> is managed and audited by the Ethiopian Tourism Bureau. License updates and verification status changes require official Bureau review.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
