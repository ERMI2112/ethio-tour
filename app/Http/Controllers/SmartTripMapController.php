<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\TripItemTargetResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartTripMapController extends Controller
{
    public function index(Trip $trip): View
    {
        Gate::authorize('view', $trip);

        return view('smart-trip.map', compact('trip'));
    }

    public function data(Trip $trip, TripItemTargetResolver $resolver): JsonResponse
    {
        Gate::authorize('view', $trip);
        $items = $resolver->presentItems($trip->items()->orderBy('planned_date')->orderBy('sequence')->get());
        $markers = collect($items)
            ->filter(fn (array $item): bool => $item['latitude'] !== null && $item['longitude'] !== null)
            ->map(fn (array $item): array => [
                'type' => strtolower(str_replace(' ', '_', $item['type_label'])),
                'title' => $item['title'],
                'summary' => $item['destination'],
                'latitude' => (float) $item['latitude'],
                'longitude' => (float) $item['longitude'],
                'url' => $item['detail_url'],
            ])
            ->values();

        return response()->json(['data' => $markers, 'count' => $markers->count()]);
    }
}
