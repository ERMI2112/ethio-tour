<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class EventOrganizerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;

        return view('event-organizer.dashboard', ['provider' => $provider, 'eventCount' => $provider->events()->count(), 'publishedCount' => $provider->events()->where('status', 'published')->count()]);
    }

    public function profile(Request $request): View
    {
        return view('event-organizer.profile', ['provider' => $request->user()->serviceProvider]);
    }
}
