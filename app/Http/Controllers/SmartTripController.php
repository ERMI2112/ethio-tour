<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Destination;
use App\Models\Trip;
use App\Services\SmartTripRecommendationService;
use App\Services\TripItemTargetResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartTripController extends Controller
{
    public function index(Request $request): View
    {
        $trips = collect();
        if ($request->user()?->role === 'tourist' && $request->user()?->tourist !== null) {
            $trips = $request->user()->tourist->trips()->with('destinations')->latest('created_at')->get();
        }

        return view('smart-trip.index', [
            'trips' => $trips,
            'destinations' => Destination::query()->orderBy('name')->limit(12)->get(),
        ]);
    }

    public function create(): View
    {
        return view('smart-trip.create', [
            'destinations' => Destination::query()->orderBy('name')->get(),
            'interests' => SmartTripRecommendationService::INTERESTS,
        ]);
    }

    public function store(StoreTripRequest $request, SmartTripRecommendationService $recommendations): RedirectResponse
    {
        $validated = $request->validated();
        $trip = Trip::create([
            'user_id' => $request->user()->user_id,
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'preferences' => array_values($validated['preferences'] ?? []),
            'status' => 'draft',
        ]);
        $trip->destinations()->sync($validated['destination_ids']);
        $recommendations->generate($trip);

        return to_route('smart-trip.show', $trip)->with('success', 'Your Smart Trip was created with a suggested itinerary.');
    }

    public function show(Trip $trip, TripItemTargetResolver $resolver): View
    {
        Gate::authorize('view', $trip);
        $trip->load(['destinations', 'items' => fn ($query) => $query->orderBy('planned_date')->orderBy('sequence')]);

        return view('smart-trip.show', [
            'trip' => $trip,
            'items' => $resolver->presentItems($trip->items),
        ]);
    }

    public function edit(Trip $trip): View
    {
        Gate::authorize('update', $trip);

        return view('smart-trip.edit', [
            'trip' => $trip->load('destinations'),
            'destinations' => Destination::query()->orderBy('name')->get(),
            'interests' => SmartTripRecommendationService::INTERESTS,
        ]);
    }

    public function update(UpdateTripRequest $request, Trip $trip, SmartTripRecommendationService $recommendations): RedirectResponse
    {
        Gate::authorize('update', $trip);
        $validated = $request->validated();
        $trip->update([
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'preferences' => array_values($validated['preferences'] ?? []),
        ]);
        $trip->destinations()->sync($validated['destination_ids']);
        $recommendations->generate($trip, replaceSuggestions: true);

        return to_route('smart-trip.show', $trip)->with('success', 'Your trip details and suggestions were updated.');
    }

    public function suggest(Trip $trip, SmartTripRecommendationService $recommendations): RedirectResponse
    {
        Gate::authorize('update', $trip);
        $count = $recommendations->generate($trip, replaceSuggestions: true);

        return to_route('smart-trip.show', $trip)->with('success', $count > 0 ? 'Suggested itinerary refreshed from public tourism data.' : 'No additional public tourism resources match this trip yet.');
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        Gate::authorize('delete', $trip);
        $trip->delete();

        return to_route('smart-trip.index')->with('success', 'Trip deleted.');
    }

    public function print(Trip $trip, TripItemTargetResolver $resolver): View
    {
        Gate::authorize('view', $trip);
        $trip->load(['destinations', 'items' => fn ($query) => $query->orderBy('planned_date')->orderBy('sequence')]);

        return view('smart-trip.print', [
            'trip' => $trip,
            'items' => $resolver->presentItems($trip->items),
        ]);
    }
}
