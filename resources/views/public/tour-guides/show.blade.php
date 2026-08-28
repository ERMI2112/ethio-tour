@extends('layouts.app')

@php
    $guideDisplayName = $guide->full_name ?: ($guide->user?->email ?? 'Licensed Tour Guide');
@endphp

@section('title', $guideDisplayName . ' — Certified Tour Guide in Ethiopia')

@section('content')
<div class="container public-catalog-page py-4 py-lg-5">
    <div class="mb-3">
        <a class="small text-success text-decoration-none fw-semibold d-inline-flex align-items-center gap-1" href="{{ route('tour-guides.index') }}">
            &larr; Back to all tour guides
        </a>
    </div>

    {{-- Guide Profile Hero Card --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-4" data-aos="fade-up">
        <div class="p-4 p-md-5" style="background: linear-gradient(135deg, #0d3824 0%, #051a10 100%); color: #fff;">
            <div class="row align-items-center g-4">
                <div class="col-auto">
                    <img src="{{ $guide->profileImageUrl() }}"
                         alt="{{ $guideDisplayName }}"
                         class="rounded-circle border border-3 border-warning shadow"
                         style="width: 120px; height: 120px; object-fit: cover;"
                         width="120" height="120" decoding="async">
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-success text-white">🛡️ Verified Guide</span>
                        <span class="badge {{ $guide->availability_status === 'available' ? 'bg-emerald-600 text-white' : 'bg-secondary text-white' }}" style="{{ $guide->availability_status === 'available' ? 'background-color: #059669;' : '' }}">
                            ● {{ ucfirst($guide->availability_status) }}
                        </span>
                        @if($guide->destination)
                            <span class="badge bg-warning text-dark fw-semibold">
                                📍 {{ $guide->destination->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="h2 text-white mb-1 fw-bold">{{ $guideDisplayName }}</h1>

                    <div class="d-flex flex-wrap align-items-center gap-3 text-white-50 small mt-2">
                        @if($guide->years_of_experience)
                            <span>🏆 <strong>{{ $guide->years_of_experience }}</strong> years certified</span>
                        @endif
                        @if($guide->average_rating !== null)
                            <span class="text-warning fw-semibold">★ {{ number_format((float) $guide->average_rating, 1) }} ({{ $guide->review_count ?? 0 }} reviews)</span>
                        @endif
                        <span>🛡️ Bureau-Accredited</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Content --}}
        <div class="col-lg-8" data-aos="fade-up">
            <article class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    @if($guide->bio)
                        <h2 class="h5 fw-bold text-dark mb-3">About {{ $guideDisplayName }}</h2>
                        <div class="text-secondary leading-relaxed mb-4">
                            {{ $guide->bio }}
                        </div>
                    @endif

                    <h2 class="h5 fw-bold text-dark mb-2">Areas of Expertise</h2>
                    <p class="text-secondary leading-relaxed mb-4">
                        {{ $guide->expertise }}
                    </p>

                    <h2 class="h5 fw-bold text-dark mb-3">Specialties &amp; Highlights</h2>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach($guide->specialtiesList() as $specialty)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-medium">
                                ★ {{ $specialty }}
                            </span>
                        @endforeach
                    </div>

                    <h2 class="h5 fw-bold text-dark mb-3">Languages Spoken</h2>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($guide->languagesList() as $lang)
                            <span class="badge bg-light text-dark border px-3 py-2 fw-medium">
                                🗣 {{ $lang }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </article>

            {{-- Multi-Day Tour Packages Showcase --}}
            @if($guide->tourPackages->isNotEmpty())
                <section class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="h5 mb-1 fw-bold text-dark">Multi-Day Excursion Packages</h2>
                                <p class="text-muted small mb-0">Pre-designed guided adventures &mdash; book a complete multi-day experience in 1 click.</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                {{ $guide->tourPackages->count() }} {{ \Illuminate\Support\Str::plural('package', $guide->tourPackages->count()) }}
                            </span>
                        </div>

                        @foreach($guide->tourPackages as $pkg)
                            <div class="border rounded-4 overflow-hidden mb-4 shadow-sm">
                                {{-- Package Header with Cover Image --}}
                                <div class="position-relative" style="height: 200px; background: #0c1e14;">
                                    <img src="{{ $pkg->coverImageUrl() }}" alt="{{ $pkg->title }}" class="w-100 h-100" style="object-fit: cover; opacity: 0.85;" loading="lazy" decoding="async">
                                    <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge bg-white text-dark fw-bold">{{ $pkg->duration_days }} {{ \Illuminate\Support\Str::plural('Day', $pkg->duration_days) }} / {{ $pkg->duration_days - 1 }} {{ \Illuminate\Support\Str::plural('Night', max(1, $pkg->duration_days - 1)) }}</span>
                                            <span class="badge bg-{{ $pkg->difficulty_level === 'easy' ? 'success' : ($pkg->difficulty_level === 'challenging' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($pkg->difficulty_level) }}
                                            </span>
                                            @if($pkg->destination)
                                                <span class="badge bg-warning text-dark">📍 {{ $pkg->destination->name }}</span>
                                            @endif
                                        </div>
                                        <h3 class="h4 fw-bold text-white mb-0">{{ $pkg->title }}</h3>
                                    </div>
                                </div>

                                {{-- Package Body --}}
                                <div class="p-4">
                                    <p class="text-secondary mb-4">{{ $pkg->description }}</p>

                                    {{-- Day-by-Day Itinerary Accordion --}}
                                    @if(!empty($pkg->itineraryList()))
                                        <h4 class="h6 fw-bold text-dark text-uppercase mb-3">Day-by-Day Itinerary</h4>
                                        <div class="accordion mb-4" id="itinerary-{{ $pkg->package_id }}">
                                            @foreach($pkg->itineraryList() as $day)
                                                <div class="accordion-item border-0 border-bottom">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} fw-bold small" type="button" data-bs-toggle="collapse" data-bs-target="#day-{{ $pkg->package_id }}-{{ $day['day'] ?? $loop->iteration }}">
                                                            <span class="badge bg-success me-2">Day {{ $day['day'] ?? $loop->iteration }}</span>
                                                            {{ $day['title'] ?? 'Day '.($day['day'] ?? $loop->iteration) }}
                                                        </button>
                                                    </h2>
                                                    <div id="day-{{ $pkg->package_id }}-{{ $day['day'] ?? $loop->iteration }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#itinerary-{{ $pkg->package_id }}">
                                                        <div class="accordion-body small text-secondary">
                                                            {{ $day['description'] ?? 'Activities and experiences for this day.' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Inclusions / Exclusions --}}
                                    <div class="row g-3 mb-4">
                                        @if(!empty($pkg->includedList()))
                                            <div class="col-md-6">
                                                <h4 class="h6 fw-bold text-success mb-2">✓ What's Included</h4>
                                                <ul class="list-unstyled small text-secondary mb-0">
                                                    @foreach($pkg->includedList() as $item)
                                                        <li class="mb-1"><span class="text-success me-1">✓</span> {{ $item }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if(!empty($pkg->excludedList()))
                                            <div class="col-md-6">
                                                <h4 class="h6 fw-bold text-danger mb-2">✗ Not Included</h4>
                                                <ul class="list-unstyled small text-secondary mb-0">
                                                    @foreach($pkg->excludedList() as $item)
                                                        <li class="mb-1"><span class="text-danger me-1">✗</span> {{ $item }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Pricing & 1-Click Booking --}}
                                    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 bg-light rounded-3">
                                        <div>
                                            <span class="text-muted small">Package Price (up to {{ $pkg->max_group_size }} guests)</span>
                                            <div class="fw-bold fs-4 text-success">{{ $pkg->formattedPrice() }}</div>
                                        </div>
                                        @auth
                                            @if(auth()->user()->role === 'tourist' && $guide->availability_status === 'available')
                                                <a class="btn btn-success btn-lg fw-bold shadow-sm" href="{{ route('tour-guides.book', $guide) }}">
                                                    Book This Excursion &rarr;
                                                </a>
                                            @endif
                                        @else
                                            <a class="btn btn-success btn-lg fw-bold shadow-sm" href="{{ route('login') }}">
                                                Log in to Book &rarr;
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Guide Reviews Section --}}
            <section class="card border-0 shadow-sm" data-aos="fade-up">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5 mb-0 fw-bold text-dark">Traveler Reviews</h2>
                        <x-reviews.rating-summary :average="$guide->average_rating" :count="$guide->review_count" />
                    </div>
                    <x-reviews.review-list :reviews="$guide->reviews" />
                </div>
            </section>
        </div>

        {{-- Booking Sidebar --}}
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="80">
            <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted small text-uppercase">Daily Guide Rate</span>
                        <div class="display-6 fw-bold text-dark">
                            {{ $guide->daily_rate === null ? 'Rate on request' : number_format((float) $guide->daily_rate, 2) . ' ETB' }}
                        </div>
                        <span class="small text-muted">Per day of private guided excursion</span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 small">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-success fw-bold">✓</span>
                            <span>Direct coordination with licensed guide</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-success fw-bold">✓</span>
                            <span>Bureau verified &amp; safety accredited</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-success fw-bold">✓</span>
                            <span>Secure in-platform booking &amp; escrow</span>
                        </div>
                    </div>

                    @auth
                        @if (auth()->user()->role === 'tourist' && $guide->availability_status === 'available' && $guide->daily_rate !== null)
                            <a class="btn btn-success btn-lg w-100 fw-bold shadow-sm" href="{{ route('tour-guides.book', $guide) }}">
                                Request Guided Tour &rarr;
                            </a>
                        @elseif (auth()->user()->role === 'tourist' && $guide->daily_rate === null)
                            <div class="alert alert-secondary small mb-0">
                                This guide has not configured a public rate yet.
                            </div>
                        @elseif (auth()->user()->role === 'tourist')
                            <div class="alert alert-secondary small mb-0">
                                This guide is currently unavailable or booked.
                            </div>
                        @else
                            <div class="alert alert-info small mb-0">
                                Only tourists can submit guide booking requests.
                            </div>
                        @endif
                    @else
                        <a class="btn btn-success btn-lg w-100 fw-bold shadow-sm" href="{{ route('login') }}">
                            Log in to request tour
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
