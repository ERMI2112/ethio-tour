@extends('layouts.app')

@section('title', 'Event Organizer Dashboard · '.$provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    <div class="ws-page-header workspace-page-header mb-4 pb-3 border-bottom"><div><span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Event organizer portal</span><h1 class="ws-title mb-1">{{ $provider->business_name }}</h1><p class="ws-lead mb-0">{{ $provider->user?->email }}</p></div><div class="d-flex gap-2"><a class="btn btn-dark btn-sm" href="{{ route('event-organizer.events.create') }}">Create event</a><a class="btn btn-outline-secondary btn-sm" href="{{ route('event-organizer.profile') }}">Profile</a></div></div>
    <h2 class="h5 mb-3">Needs attention</h2>
    @if($pendingEventBookings > 0)<div class="alert alert-warning">{{ $pendingEventBookings }} event booking request{{ $pendingEventBookings === 1 ? '' : 's' }} are waiting for review. <a href="{{ route('event-organizer.events.bookings') }}">Review bookings</a></div>@else<div class="alert alert-success">No event booking requests are waiting.</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Confirmed registrations" icon="ticket-detailed" :value="$stats['registrationsSecured']" hint="Tickets in accepted or paid bookings" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Confirmed ticket value" icon="cash-stack" :value="number_format($stats['escrowVolume'], 2).' ETB'" hint="Confirmed and completed bookings" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Published events" icon="calendar-event" :value="$publishedCount" :hint="$eventCount.' total event records'" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Next event" icon="clock" :value="$stats['nextEvent']?->event_name ?: 'Not scheduled'" :hint="$stats['daysToCelebration'] !== null ? $stats['daysToCelebration'].' day(s)' : 'No upcoming event date'" /></div>
    </div>

    <h2 class="h5 mb-3">Operational insights</h2>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white d-flex justify-content-between"><div><h2 class="h5 mb-0">Your events</h2><small class="text-muted">Current event and ticket inventory</small></div><a href="{{ route('event-organizer.events.index') }}" class="small">Manage</a></div><div class="list-group list-group-flush">
            @forelse($events as $event)<div class="list-group-item"><div class="d-flex justify-content-between gap-3"><div><strong>{{ $event->event_name }}</strong><small class="d-block text-muted">{{ $event->event_date?->format('M j, Y') ?: 'Date not set' }} · {{ $event->venue ?: 'Venue not set' }}</small></div><x-ui.status-badge :status="$event->status" /></div><div class="small text-muted mt-2">{{ $event->ticketTypes->count() }} ticket type(s) · {{ $event->ticketTypes->sum('quantity') }} total ticket capacity</div></div>@empty<div class="list-group-item text-muted">No events created yet.</div>@endforelse
        </div></div></div>
        <div class="col-lg-5"><div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Recent event bookings</h2></div><div class="list-group list-group-flush">
            @forelse($eventBookings as $booking)<div class="list-group-item"><strong>{{ $booking->eventReservation?->ticketType?->event?->event_name ?: 'Event' }}</strong><small class="d-block text-muted">{{ $booking->tourist?->full_name ?: $booking->tourist?->user?->email ?: 'Tourist' }} · {{ $booking->eventReservation?->quantity ?: 0 }} ticket(s)</small><div class="mt-2"><x-ui.status-badge :status="$booking->status" /></div></div>@empty<div class="list-group-item text-muted">No event bookings recorded yet.</div>@endforelse
        </div><div class="card-footer bg-white"><a href="{{ route('event-organizer.events.bookings') }}" class="small">View all bookings</a></div></div><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h2 class="h6">Reviews</h2>@if($stats['reviewCount'] > 0)<p class="mb-0">{{ number_format($stats['reviewAverage'], 1) }} / 5 from {{ $stats['reviewCount'] }} review(s).</p>@else<p class="text-muted small mb-0">No reviews recorded yet.</p>@endif</div></div></div>
    </div>
</div>
@endsection
