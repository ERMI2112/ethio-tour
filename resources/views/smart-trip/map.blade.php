@extends('layouts.app')
@section('title', 'Map · '.$trip->title)
@section('content')
<div class="container-fluid py-4 py-lg-5"><div class="container"><div class="mb-4"><a class="small" href="{{ route('smart-trip.show', $trip) }}">&larr; Back to itinerary</a><p class="text-uppercase text-success small fw-semibold mt-3 mb-1">Smart Trip map</p><h1 class="h2">{{ $trip->title }}</h1><p class="text-muted mb-0">Only itinerary items with real coordinates are shown.</p></div><div id="map-status" class="alert alert-info d-none" role="status">No mapped itinerary items are available yet.</div><div class="map-shell position-relative"><div id="tourism-map" class="rounded-3 shadow-sm" data-endpoint="{{ route('smart-trip.map.data', $trip) }}" aria-label="Smart Trip itinerary map"></div><div id="map-loading" class="map-overlay badge rounded-pill text-bg-light shadow-sm">Loading itinerary map…</div></div><div class="d-flex justify-content-between align-items-center mt-3"><p id="map-result-count" class="text-muted small mb-0" aria-live="polite"></p><p class="text-muted small mb-0">Map data is private to this saved trip.</p></div><form id="map-filters" class="d-none"><input type="hidden" name="trip" value="{{ $trip->trip_id }}"></form><button id="map-near-me" class="d-none" type="button">Near me</button></div></div>
@endsection
@push('scripts')
    @vite('resources/js/map.js')
@endpush
