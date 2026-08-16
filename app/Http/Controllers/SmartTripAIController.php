<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\SmartTripAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartTripAIController extends Controller
{
    public function create(Trip $trip): View
    {
        Gate::authorize('view', $trip);

        return view('smart-trip.ai', [
            'trip' => $trip->load('destinations'),
            'result' => null,
            'intent' => '',
        ]);
    }

    public function generate(Request $request, Trip $trip, SmartTripAIService $ai): View
    {
        Gate::authorize('view', $trip);
        $maxIntent = max(200, (int) config('services.openai.max_intent_chars', 2000));
        $validated = $request->validate([
            'intent' => ['required', 'string', 'max:'.$maxIntent],
        ]);

        return view('smart-trip.ai', [
            'trip' => $trip->load('destinations'),
            'result' => $ai->plan($trip, $validated['intent']),
            'intent' => $validated['intent'],
        ]);
    }
}
