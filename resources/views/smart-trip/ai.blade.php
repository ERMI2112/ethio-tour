@extends('layouts.app')
@section('title', 'Smart Trip AI · '.$trip->title)
@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><a class="small" href="{{ route('smart-trip.show', $trip) }}">&larr; Back to itinerary</a><p class="text-uppercase text-success small fw-semibold mt-3 mb-1">Smart Trip intelligence</p><h1 class="h2">Plan with verified tourism data</h1><p class="text-muted mb-0">Describe what you want to experience. The assistant can explain and organize real public resources; it cannot book, pay, or change reservations.</p></div>
        <span class="badge text-bg-light text-success">{{ $trip->destinations->pluck('name')->join(', ') }}</span>
    </div>

    <form method="POST" action="{{ route('smart-trip.ai.generate', $trip) }}" class="card border-0 shadow-sm mb-4">
        @csrf
        <div class="card-body p-4"><label class="form-label fw-semibold" for="trip-intent">What would you like to do?</label><textarea class="form-control @error('intent') is-invalid @enderror" id="trip-intent" name="intent" rows="4" maxlength="{{ config('services.openai.max_intent_chars', 2000) }}" placeholder="For example: I enjoy history and local food. Prioritize cultural places, a relaxed pace, and events during my dates." required>{{ old('intent', $intent) }}</textarea>@error('intent')<div class="invalid-feedback">{{ $message }}</div>@enderror<p class="form-text mb-0">Use preferences, pace, interests, or practical constraints. Suggestions are always checked against current public data.</p></div>
        <div class="card-footer bg-white border-0 p-4"><button class="btn btn-success" type="submit">Generate verified suggestions</button></div>
    </form>

    @if($result)
        <div class="alert {{ $result['fallback'] ? 'alert-warning' : 'alert-success' }}" role="status"><strong>{{ $result['fallback'] ? 'Deterministic fallback' : 'AI-assisted plan' }}</strong><br>{{ $result['trip_summary'] }}</div>
        @if($result['warnings'])<div class="alert alert-warning"><strong>Warnings</strong><ul class="mb-0">@foreach($result['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>@endif
        @if($result['notes'])<div class="alert alert-light border"><strong>Notes</strong><ul class="mb-0">@foreach($result['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul></div>@endif
        @forelse($result['days'] as $day)
            <section class="mb-4"><h2 class="h4 border-bottom pb-2">{{ date('l, F j, Y', strtotime($day['date'])) }}</h2><div class="row g-3">@foreach($day['items'] as $item)<div class="col-md-6"><article class="card border-0 shadow-sm h-100"><div class="card-body"><span class="badge text-bg-light text-success mb-2">{{ str_replace('_', ' ', ucfirst($item['entity_type'])) }}</span><h3 class="h5">{{ $item['title'] }}</h3>@if($item['rating'] !== null)<p class="small text-warning mb-2">{{ number_format($item['rating'], 1) }} / 5</p>@endif<p class="small text-muted">{{ $item['reason'] }}</p><div class="d-flex flex-wrap gap-2">@if($item['detail_url'])<a class="btn btn-outline-success btn-sm" href="{{ $item['detail_url'] }}">View details</a>@endif @if($item['booking_url'])<a class="btn btn-outline-primary btn-sm" href="{{ $item['booking_url'] }}">Review booking flow</a>@endif<form method="POST" action="{{ route('smart-trip.items.store', $trip) }}">@csrf<input type="hidden" name="item_type" value="{{ $item['entity_type'] }}"><input type="hidden" name="item_id" value="{{ $item['entity_id'] }}"><input type="hidden" name="planned_date" value="{{ $item['planned_date'] }}"><button class="btn btn-primary btn-sm" type="submit">Add to itinerary</button></form></div></div></article></div>@endforeach</div></section>
        @empty
            <x-ui.empty-state title="No verified suggestions" message="Try a different request or use the deterministic itinerary suggestions." />
        @endforelse
    @endif
</div>
@endsection
