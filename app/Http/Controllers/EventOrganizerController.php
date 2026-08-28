<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventOrganizerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $reviewQuery = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds));

        $events = $provider->events()->with('ticketTypes')->get();
        $eventCount = $events->count();
        $publishedCount = $events->where('status', 'published')->count();

        $eventIds = $events->modelKeys();
        $registrationsSecured = Booking::whereHas('eventReservation.ticketType', fn ($query) => $query->whereIn('event_id', $eventIds))
            ->whereIn('status', ['accepted', 'payment_pending', 'confirmed', 'completed'])
            ->with('eventReservation')
            ->get()
            ->sum(fn (Booking $booking): int => (int) ($booking->eventReservation?->quantity ?? 0));
        $escrowVolume = (float) Booking::whereHas('eventReservation.ticketType', fn ($query) => $query->whereIn('event_id', $eventIds))
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum('total_amount');
        $eventBookings = Booking::whereHas('eventReservation.ticketType', fn ($query) => $query->whereIn('event_id', $eventIds))
            ->with(['tourist', 'eventReservation.ticketType.event'])
            ->latest('booking_date')
            ->limit(6)
            ->get();
        $pendingEventBookings = Booking::whereHas('eventReservation.ticketType', fn ($query) => $query->whereIn('event_id', $eventIds))
            ->where('status', 'pending')
            ->count();
        $nextEvent = $events->whereIn('status', ['published', 'draft'])->filter(fn ($event) => $event->event_date?->isFuture() || $event->event_date?->isToday())->sortBy('event_date')->first();

        $stats = [
            'registrationsSecured' => $registrationsSecured,
            'escrowVolume' => $escrowVolume,
            'venueUtilization' => null,
            'daysToCelebration' => $nextEvent?->event_date ? now()->startOfDay()->diffInDays($nextEvent->event_date->startOfDay(), false) : null,
            'eventCount' => $eventCount,
            'publishedCount' => $publishedCount,
            'reviewAverage' => $reviewQuery->avg('rating'),
            'reviewCount' => (clone $reviewQuery)->count(),
            'nextEvent' => $nextEvent,
        ];

        return view('event-organizer.dashboard', compact('provider', 'stats', 'events', 'eventBookings', 'eventCount', 'publishedCount', 'pendingEventBookings'));
    }

    public function profile(Request $request): View
    {
        return view('event-organizer.profile', ['provider' => $request->user()->serviceProvider]);
    }
}
