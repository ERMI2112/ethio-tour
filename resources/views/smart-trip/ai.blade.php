@extends('layouts.app')

@section('title', 'Smart Trip AI · '.$trip->title)

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a class="small text-muted text-decoration-none" href="{{ route('smart-trip.show', $trip) }}">
                &larr; Back to itinerary
            </a>
            <p class="text-uppercase text-success small fw-bold mt-3 mb-1">Smart Trip Intelligence</p>
            <h1 class="display-6 fw-bold">Plan with verified Ethiopian tourism data</h1>
            <p class="text-muted mb-0">Describe what you want to experience. The assistant searches and organizes real public resources for your itinerary.</p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
            📍 {{ $trip->destinations->pluck('name')->join(', ') ?: 'Ethiopia' }}
        </span>
    </div>

    <!-- AI Intent Form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <form method="POST" action="{{ route('smart-trip.ai.generate', $trip) }}">
            @csrf
            <div class="card-body p-4 p-md-5">
                <label class="form-label fw-bold fs-5 mb-2" for="trip-intent">
                    What would you like to experience on this trip?
                </label>
                
                <!-- Inspiration Chips -->
                <div class="mb-3">
                    <span class="small text-muted d-block mb-2">💡 Quick Prompt Ideas (Click to use):</span>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm prompt-chip" 
                                onclick="document.getElementById('trip-intent').value = 'I want to explore medieval castles, UNESCO heritage churches, and learn about imperial Ethiopian history at a relaxed pace.'">
                            🏰 Medieval Castles &amp; Churches
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm prompt-chip"
                                onclick="document.getElementById('trip-intent').value = 'Focus on traditional coffee ceremonies, local dining, authentic cultural experiences, and hiring a top-rated local tour guide.'">
                            ☕ Coffee Ceremony &amp; Culinary
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm prompt-chip"
                                onclick="document.getElementById('trip-intent').value = 'Include vibrant religious festivals like Timkat or Meskel, sacred processions, and cultural evening events during my dates.'">
                            🎉 Festivals &amp; Cultural Events
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm prompt-chip"
                                onclick="document.getElementById('trip-intent').value = 'Plan an active outdoor adventure with 4x4 transport, scenic mountain viewing, nature trails, and reliable hotel stays.'">
                            ⛰️ 4x4 Safari &amp; Nature Trekking
                        </button>
                    </div>
                </div>

                <textarea class="form-control @error('intent') is-invalid @enderror" 
                          id="trip-intent" 
                          name="intent" 
                          rows="4" 
                          maxlength="{{ config('services.openai.max_intent_chars', 2000) }}" 
                          placeholder="For example: I want to visit historic castles in the morning, experience an authentic Ethiopian coffee ceremony in the afternoon, and attend cultural events in the evening." 
                          required>{{ old('intent', $intent) }}</textarea>
                @error('intent')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <p class="form-text text-muted small mt-2 mb-0">
                    Suggestions are strictly verified against current public destinations, attractions, licensed guides, and operational providers.
                </p>
            </div>
            <div class="card-footer bg-light bg-opacity-50 border-top p-4 d-flex justify-content-between align-items-center">
                <a class="btn btn-outline-secondary" href="{{ route('smart-trip.show', $trip) }}">Cancel</a>
                <button class="btn btn-warning fw-bold text-dark px-4 shadow-sm" type="submit">
                    ✨ Generate Verified Itinerary
                </button>
            </div>
        </form>
    </div>

    <!-- AI Results Section -->
    @if($result)
        <div class="alert {{ $result['fallback'] ? 'alert-warning' : 'alert-success' }} rounded-3 shadow-sm mb-4" role="status">
            <div class="d-flex align-items-center gap-2 mb-1">
                <strong>{{ $result['fallback'] ? 'Deterministic fallback' : 'AI-assisted plan' }}</strong>
            </div>
            <p class="mb-0">{{ $result['trip_summary'] }}</p>
        </div>

        @if(!empty($result['warnings']))
            <div class="alert alert-warning rounded-3 mb-4">
                <strong>Attention:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($result['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($result['notes']))
            <div class="alert alert-light border rounded-3 mb-4">
                <strong>Travel Notes:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($result['notes'] as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="h4 fw-bold mb-3">Suggested Itinerary Stops</h2>

        @forelse($result['days'] as $day)
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3" style="background: #eef7f2; border-left: 5px solid #198754;">
                    <h3 class="h5 fw-bold mb-0 text-success">
                        <i class="bi bi-calendar-event me-2"></i>
                        {{ date('l, F j, Y', strtotime($day['date'])) }}
                    </h3>
                    <span class="badge bg-white text-dark border">{{ count($day['items']) }} {{ \Illuminate\Support\Str::plural('suggestion', count($day['items'])) }}</span>
                </div>

                <div class="row g-4">
                    @foreach($day['items'] as $item)
                        @php
                            $lowerTitle = strtolower($item['title']);
                            $type = $item['entity_type'] ?? 'service';
                            $itemImg = asset('images/destinations/gondar-castles.jpg');
                            if (str_contains($lowerTitle, 'coffee') || str_contains($lowerTitle, 'restaurant') || $type === 'restaurant') {
                                $itemImg = asset('images/services/coffee-tasting.jpg');
                            } elseif (str_contains($lowerTitle, '4x4') || str_contains($lowerTitle, 'safari') || $type === 'transportation') {
                                $itemImg = asset('images/services/safari-4x4.jpg');
                            } elseif (str_contains($lowerTitle, 'hotel') || str_contains($lowerTitle, 'suite') || $type === 'hotel') {
                                $itemImg = asset('images/services/hotel-suite.jpg');
                            } elseif (str_contains($lowerTitle, 'timkat') || $type === 'event') {
                                $itemImg = asset('images/events/timkat-festival.jpg');
                            } elseif (str_contains($lowerTitle, 'meskel')) {
                                $itemImg = asset('images/events/meskel-festival.jpg');
                            }
                        @endphp
                        <div class="col-md-6">
                            <article class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden d-flex flex-column">
                                <div class="position-relative" style="height: 160px; background: #0d3824;">
                                    <img src="{{ $itemImg }}" alt="{{ $item['title'] }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white border border-white-50">
                                            {{ str_replace('_', ' ', ucfirst($item['entity_type'])) }}
                                        </span>
                                    </div>
                                    @if($item['rating'] !== null)
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning text-dark fw-bold shadow-sm">
                                                ★ {{ number_format($item['rating'], 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <h4 class="h5 fw-bold mb-2">{{ $item['title'] }}</h4>
                                    <p class="small text-secondary mb-3 flex-grow-1">
                                        {{ $item['reason'] ?? 'Verified Ethiopian tourism experience matching your travel plan.' }}
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 pt-3 border-top mt-auto align-items-center">
                                        @if($item['detail_url'])
                                            <a class="btn btn-outline-secondary btn-sm" href="{{ $item['detail_url'] }}">
                                                Details
                                            </a>
                                        @endif
                                        @if($item['booking_url'])
                                            <a class="btn btn-outline-primary btn-sm" href="{{ $item['booking_url'] }}">
                                                Booking flow
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('smart-trip.items.store', $trip) }}" class="ms-auto">
                                            @csrf
                                            <input type="hidden" name="item_type" value="{{ $item['entity_type'] }}">
                                            <input type="hidden" name="item_id" value="{{ $item['entity_id'] }}">
                                            <input type="hidden" name="planned_date" value="{{ $item['planned_date'] }}">
                                            <button class="btn btn-success btn-sm fw-bold" type="submit">
                                                <i class="bi bi-plus-lg me-1"></i> Add to Itinerary
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <x-ui.empty-state title="No verified suggestions" message="Try a different prompt or adjust your dates and destinations." />
        @endforelse
    @endif
</div>
@endsection
