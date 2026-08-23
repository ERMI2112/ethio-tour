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

        $stats = [
            'registrationsSecured' => '8,420 Passports',
            'escrowVolume' => 45200.00,
            'venueUtilization' => 94,
            'daysToCelebration' => 6,
            'eventCount' => $eventCount,
            'publishedCount' => $publishedCount,
            'reviewAverage' => $reviewQuery->avg('rating') ?: 4.9,
            'reviewCount' => (clone $reviewQuery)->count() ?: 124,
        ];

        return view('event-organizer.dashboard', compact('provider', 'stats', 'events', 'eventCount', 'publishedCount'));
    }

    public function profile(Request $request): View
    {
        return view('event-organizer.profile', ['provider' => $request->user()->serviceProvider]);
    }
}
