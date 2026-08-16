<?php

namespace App\Http\Controllers;

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

        return view('event-organizer.dashboard', ['provider' => $provider, 'eventCount' => $provider->events()->count(), 'publishedCount' => $provider->events()->where('status', 'published')->count(), 'reviewAverage' => $reviewQuery->avg('rating'), 'reviewCount' => (clone $reviewQuery)->count()]);
    }

    public function profile(Request $request): View
    {
        return view('event-organizer.profile', ['provider' => $request->user()->serviceProvider]);
    }
}
