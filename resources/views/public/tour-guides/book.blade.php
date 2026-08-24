@extends('layouts.app')

@php
    $displayName = $guide->full_name ?: ($guide->user?->email ?? 'Licensed Tour Guide');
    $availableLanguages = $guide->languagesList();
@endphp

@section('title', 'Request Tour Guide · ' . $displayName)

@section('content')
<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary" href="{{ route('tour-guides.show', $guide) }}">
                    &larr; Back to {{ $displayName }}
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                {{-- Header with Guide Portrait & Credentials --}}
                <div class="p-4 bg-light border-bottom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $guide->profileImageUrl() }}"
                                 alt="{{ $displayName }}"
                                 class="rounded-circle border border-2 border-white shadow-sm"
                                 style="width: 68px; height: 68px; object-fit: cover;">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-0.5">
                                    <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem; font-weight: 700;">
                                        🛡️ Verified Guide
                                    </span>
                                    @if($guide->destination)
                                        <span class="text-muted small">📍 {{ $guide->destination->name }}</span>
                                    @endif
                                </div>
                                <h1 class="h4 fw-bold text-dark mb-0" style="font-family: var(--font-display);">Request {{ $displayName }}</h1>
                                <p class="text-secondary small mb-0">{{ $guide->expertise }}</p>
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em;">Daily Rate</span>
                            <strong class="fs-5 text-dark font-monospace">{{ number_format((float) $guide->daily_rate, 2) }} ETB</strong>
                            <span class="text-muted small d-block">per day</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    @include('layouts.partials.flash-messages')

                    <form method="POST" action="{{ route('tour-guides.book.store', $guide) }}" id="guide-booking-form">
                        @csrf

                        <h2 class="h6 fw-bold text-uppercase text-muted mb-3" style="letter-spacing: 0.08em; font-size: 0.78rem;">
                            1. Journey Dates &amp; Party Size
                        </h2>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="start_date">Tour start date</label>
                                <input class="form-control rounded-3 @error('start_date') is-invalid @enderror"
                                       type="date"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ old('start_date') }}"
                                       min="{{ now()->toDateString() }}"
                                       required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="end_date">Tour end date</label>
                                <input class="form-control rounded-3 @error('end_date') is-invalid @enderror"
                                       type="date"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ old('end_date') }}"
                                       min="{{ now()->addDay()->toDateString() }}"
                                       required>
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="number_of_tourists">Party size (Tourists)</label>
                                <input class="form-control rounded-3 @error('number_of_tourists') is-invalid @enderror"
                                       type="number"
                                       id="number_of_tourists"
                                       name="number_of_tourists"
                                       value="{{ old('number_of_tourists', 1) }}"
                                       min="1"
                                       max="50"
                                       required>
                                @error('number_of_tourists')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h2 class="h6 fw-bold text-uppercase text-muted mb-3" style="letter-spacing: 0.08em; font-size: 0.78rem;">
                            2. Special Interests &amp; Language Preference
                        </h2>

                        {{-- Language Preference --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark" for="language_preference">Preferred Tour Language</label>
                            <select class="form-select rounded-3 @error('language_preference') is-invalid @enderror"
                                    id="language_preference"
                                    name="language_preference">
                                <option value="">Select guide language...</option>
                                @foreach($availableLanguages as $lang)
                                    <option value="{{ $lang }}" @selected(old('language_preference') === $lang || (old('language_preference') === null && $loop->first))>
                                        {{ $lang }} (Fluent Guide Language)
                                    </option>
                                @endforeach
                                <option value="English" @selected(old('language_preference') === 'English')>English</option>
                                <option value="Amharic" @selected(old('language_preference') === 'Amharic')>Amharic (አማርኛ)</option>
                            </select>
                            <span class="text-muted small" style="font-size: 0.75rem;">This guide offers tours in: {{ implode(', ', $availableLanguages) }}.</span>
                            @error('language_preference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Special Interests & Focus Areas --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">What are your special interests?</label>
                            <p class="text-muted small mb-2" style="font-size: 0.8rem;">Select topics or focus areas you want the guide to emphasize:</p>

                            @php
                                $suggestedInterests = [
                                    '🏰 Castle & Imperial History',
                                    '⛰️ Simien High-Altitude Trekking',
                                    '⛪ Ancient Monasteries & Rock-Hewn Churches',
                                    '🦅 Endemic Wildlife & Bird Watching',
                                    '☕ Traditional Ethiopian Coffee Ceremony',
                                    '📸 Photography & Scenic Viewpoints',
                                    '🍷 Habesha Traditional Dining & Live Azmari Music',
                                    '🚶 Old Town Architecture & Cultural Walks',
                                ];
                            @endphp

                            <div class="d-flex flex-wrap gap-2 mb-2" id="interest-pills">
                                @foreach($suggestedInterests as $interest)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 interest-chip"
                                            data-interest="{{ $interest }}"
                                            style="font-size: 0.78rem;">
                                        + {{ $interest }}
                                    </button>
                                @endforeach
                            </div>

                            <input type="text"
                                   class="form-control rounded-3 @error('special_interests') is-invalid @enderror"
                                   id="special_interests"
                                   name="special_interests"
                                   value="{{ old('special_interests') }}"
                                   placeholder="e.g. 17th Century Imperial History, Gelada Baboon Photography, Fasting Food Circuit">
                            @error('special_interests')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h2 class="h6 fw-bold text-uppercase text-muted mb-3" style="letter-spacing: 0.08em; font-size: 0.78rem;">
                            3. What do you want to achieve? (Trip Goals &amp; Requests)
                        </h2>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark" for="notes">Tell the guide what you'd like to achieve during this journey</label>
                            <textarea class="form-control rounded-3 @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Share your goals, expectations, physical fitness level, dietary preferences, or specific sites you must see (e.g., 'We want to photograph the ceiling of Debre Berhan Selassie at quiet hours and do an easy 3-hour hike in Simien...').">{{ old('notes') }}</textarea>
                            <span class="text-muted small" style="font-size: 0.75rem;">Your guide will review these details before confirming your booking.</span>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-info-subtle border-0 rounded-3 p-3.5 mb-4 small text-secondary">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill text-info fs-5 mt-0.5"></i>
                                <div>
                                    <strong class="text-dark d-block mb-0.5">No immediate payment required</strong>
                                    Your request is submitted as <strong>Pending</strong>. The daily rate is frozen on the booking ledger and payment will only be requested after the guide accepts your booking.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-vn-red fw-bold rounded-pill px-4 py-2.5 shadow-sm" type="submit">
                                Submit Booking Request &rarr;
                            </button>
                            <a class="btn btn-light border rounded-pill px-4 py-2.5 fw-semibold text-muted" href="{{ route('tour-guides.show', $guide) }}">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('special_interests');
    const chips = document.querySelectorAll('.interest-chip');

    chips.forEach(chip => {
        chip.addEventListener('click', function () {
            const val = this.getAttribute('data-interest').replace(/^[^a-zA-Z0-9]+/, '').trim();
            let current = input.value.trim();
            let items = current ? current.split(',').map(s => s.trim()) : [];

            if (items.includes(val)) {
                items = items.filter(s => s !== val);
                this.classList.remove('btn-success', 'text-white');
                this.classList.add('btn-outline-secondary');
                this.innerHTML = '+ ' + this.getAttribute('data-interest');
            } else {
                items.push(val);
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-success', 'text-white');
                this.innerHTML = '✓ ' + this.getAttribute('data-interest');
            }
            input.value = items.join(', ');
        });
    });
});
</script>
@endpush
@endsection
