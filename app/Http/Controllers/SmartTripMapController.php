<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\TripItemTargetResolver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartTripMapController extends Controller
{
    public function index(Trip $trip, TripItemTargetResolver $resolver): View
    {
        Gate::authorize('view', $trip);

        $items = $resolver->presentItems($trip->items()->orderBy('planned_date')->orderBy('sequence')->get());
        $mappedItems = collect($items)
            ->filter(fn (array $item): bool => $item['latitude'] !== null && $item['longitude'] !== null)
            ->values();

        $routeData = $this->buildRouteMetrics($trip, $mappedItems);

        return view('smart-trip.map', [
            'trip' => $trip,
            'mappedItems' => $mappedItems,
            'routeData' => $routeData,
        ]);
    }

    public function data(Trip $trip, TripItemTargetResolver $resolver): JsonResponse
    {
        Gate::authorize('view', $trip);

        $items = $resolver->presentItems($trip->items()->orderBy('planned_date')->orderBy('sequence')->get());
        $mappedItems = collect($items)
            ->filter(fn (array $item): bool => $item['latitude'] !== null && $item['longitude'] !== null)
            ->values();

        $routeData = $this->buildRouteMetrics($trip, $mappedItems);

        $markers = $mappedItems->map(function (array $item, int $index) use ($trip): array {
            $plannedDate = $item['planned_date'] ? Carbon::parse($item['planned_date']) : null;
            $dayNumber = $plannedDate && $trip->start_date
                ? max(1, $trip->start_date->diffInDays($plannedDate) + 1)
                : ($index + 1);

            return [
                'sequence_number' => $index + 1,
                'day_number' => $dayNumber,
                'type' => strtolower(str_replace(' ', '_', $item['type_label'])),
                'title' => $item['title'],
                'summary' => $item['destination'],
                'latitude' => (float) $item['latitude'],
                'longitude' => (float) $item['longitude'],
                'url' => $item['detail_url'],
                'image_url' => $item['image_url'] ?? null,
                'price_hint' => $item['price_hint'] ?? null,
                'notes' => $item['notes'] ?? null,
            ];
        })->values();

        return response()->json([
            'data' => $markers,
            'count' => $markers->count(),
            'route_segments' => $routeData['segments'],
            'total_distance_km' => $routeData['total_distance_km'],
            'formatted_total_distance' => $routeData['formatted_total_distance'],
            'total_duration_minutes' => $routeData['total_duration_minutes'],
            'formatted_total_duration' => $routeData['formatted_total_duration'],
        ]);
    }

    /**
     * Calculate route segments, driving distance, and travel time across sequential waypoints.
     *
     * @param  Collection<int, array>  $mappedItems
     */
    private function buildRouteMetrics(Trip $trip, $mappedItems): array
    {
        $segments = [];
        $totalDistanceKm = 0.0;
        $totalMinutes = 0;

        for ($i = 0; $i < $mappedItems->count() - 1; $i++) {
            $from = $mappedItems[$i];
            $to = $mappedItems[$i + 1];

            $lat1 = (float) $from['latitude'];
            $lon1 = (float) $from['longitude'];
            $lat2 = (float) $to['latitude'];
            $lon2 = (float) $to['longitude'];

            // Haversine direct distance
            $haversineKm = $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);

            // Driving adjustment factor for Ethiopian mountain topography & highway curves (~1.30x)
            $drivingKm = round($haversineKm * 1.30, 1);
            if ($drivingKm < 1.0 && $haversineKm > 0) {
                $drivingKm = round($haversineKm, 1);
            }

            // Estimate travel time at average 55 km/h driving speed
            $minutes = (int) max(5, round(($drivingKm / 55.0) * 60));

            $totalDistanceKm += $drivingKm;
            $totalMinutes += $minutes;

            $segments[] = [
                'leg_number' => $i + 1,
                'from_title' => $from['title'],
                'from_coords' => [$lat1, $lon1],
                'to_title' => $to['title'],
                'to_coords' => [$lat2, $lon2],
                'distance_km' => $drivingKm,
                'formatted_distance' => number_format($drivingKm, 1).' km',
                'duration_minutes' => $minutes,
                'formatted_duration' => $this->formatDuration($minutes),
                'polyline' => [
                    [$lat1, $lon1],
                    [$lat2, $lon2],
                ],
            ];
        }

        return [
            'segments' => $segments,
            'total_distance_km' => round($totalDistanceKm, 1),
            'formatted_total_distance' => number_format($totalDistanceKm, 1).' km',
            'total_duration_minutes' => $totalMinutes,
            'formatted_total_duration' => $this->formatDuration($totalMinutes),
        ];
    }

    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' mins';
        }

        $hours = floor($minutes / 60);
        $remainingMins = $minutes % 60;

        if ($remainingMins === 0) {
            return $hours.' hr'.($hours > 1 ? 's' : '');
        }

        return $hours.' hr'.($hours > 1 ? 's' : '').' '.$remainingMins.' min'.($remainingMins > 1 ? 's' : '');
    }
}
