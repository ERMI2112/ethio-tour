<?php

namespace App\Services;

use App\Models\CulturalEvent;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\Review;
use App\Models\TourismService;
use App\Models\Trip;
use App\Models\TripItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmartTripRecommendationService
{
    public const INTERESTS = ['cultural', 'nature', 'adventure', 'history', 'food', 'events'];

    public function recommendations(Trip $trip): Collection
    {
        $trip->loadMissing('destinations');
        $destinations = $trip->destinations;
        if ($destinations->isEmpty()) {
            return collect();
        }

        $destinationIds = $destinations->pluck('destination_id')->all();
        $destinationNames = $destinations->pluck('name')->filter()->values()->all();
        $serviceItems = TourismService::query()
            ->with(['serviceProvider', 'destination', 'category'])
            ->whereIn('destination_id', $destinationIds)
            ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())
            ->limit(100)
            ->get();
        $serviceRatings = $this->ratingsFor($serviceItems->pluck('service_id')->all());

        $candidates = collect();
        foreach ($serviceItems as $service) {
            $rating = $serviceRatings->get($service->service_id);
            $candidates->push($this->candidate(
                'service',
                $service->service_id,
                $service->service_name,
                implode(' ', array_filter([$service->description, $service->category?->category_name, $service->serviceProvider?->provider_type])),
                $service->destination_id,
                $service->latitude,
                $service->longitude,
                null,
                $rating,
            ));
        }

        $heritageItems = HeritageSite::query()
            ->with('destination')
            ->whereIn('destination_id', $destinationIds)
            ->limit(100)
            ->get();
        foreach ($heritageItems as $heritage) {
            $candidates->push($this->candidate(
                'heritage_site',
                $heritage->heritage_id,
                $heritage->heritage_type,
                implode(' ', array_filter([$heritage->destination?->name, $heritage->opening_hours, 'heritage history cultural'])),
                $heritage->destination_id,
                $heritage->latitude,
                $heritage->longitude,
            ));
        }

        if ($destinationNames !== []) {
            $museumItems = MuseumInformation::query()
                ->where(function ($query) use ($destinationNames): void {
                    foreach ($destinationNames as $name) {
                        $query->orWhere('location', 'like', '%'.$name.'%');
                    }
                })
                ->limit(100)
                ->get();
            foreach ($museumItems as $museum) {
                $destinationId = $this->matchingDestinationId($museum->location, $destinations);
                $candidates->push($this->candidate(
                    'museum',
                    $museum->museum_id,
                    $museum->museum_name,
                    implode(' ', array_filter([$museum->description, $museum->location, 'museum history cultural'])),
                    $destinationId,
                    $museum->latitude,
                    $museum->longitude,
                ));
            }
        }

        $eventItems = CulturalEvent::query()
            ->with(['destination', 'serviceProvider'])
            ->whereIn('destination_id', $destinationIds)
            ->where('status', 'published')
            ->whereBetween('event_date', [$trip->start_date, $trip->end_date])
            ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())
            ->limit(100)
            ->get();
        foreach ($eventItems as $event) {
            $rating = $event->service_id === null ? null : $serviceRatings->get($event->service_id);
            $candidates->push($this->candidate(
                'event',
                $event->event_id,
                $event->event_name,
                implode(' ', array_filter([$event->description, $event->venue, 'event festival cultural'])),
                $event->destination_id,
                $event->latitude,
                $event->longitude,
                $event->event_date?->toDateString(),
                $rating,
            ));
        }

        $existing = $trip->items()
            ->get(['item_type', 'item_id'])
            ->mapWithKeys(fn (TripItem $item): array => [$item->item_type.':'.$item->item_id => true]);
        $days = max(1, $trip->start_date->diffInDays($trip->end_date) + 1);
        $maximum = min(20, max(4, $days * 4));
        $preferences = $trip->preferences ?? [];

        return $candidates
            ->reject(fn (array $candidate): bool => $existing->has($candidate['item_type'].':'.$candidate['item_id']))
            ->map(fn (array $candidate): array => $this->score($candidate, $preferences, $destinations))
            ->sort(function (array $left, array $right): int {
                $score = $right['score'] <=> $left['score'];
                if ($score !== 0) {
                    return $score;
                }

                return [$left['item_type'], $left['item_id']] <=> [$right['item_type'], $right['item_id']];
            })
            ->take($maximum)
            ->values()
            ->map(function (array $candidate, int $index) use ($trip, $days): array {
                $plannedDate = $candidate['planned_date'] ?? $trip->start_date->copy()->addDays(min($days - 1, intdiv($index, 4)))->toDateString();
                $candidate['planned_date'] = $plannedDate;

                return $candidate;
            });
    }

    public function generate(Trip $trip, bool $replaceSuggestions = false): int
    {
        return DB::transaction(function () use ($trip, $replaceSuggestions): int {
            if ($replaceSuggestions) {
                $trip->items()->where('source', 'suggested')->delete();
            }

            $nextSequence = ((int) $trip->items()->max('sequence')) + 1;
            $created = 0;
            foreach ($this->recommendations($trip) as $candidate) {
                $trip->items()->firstOrCreate([
                    'item_type' => $candidate['item_type'],
                    'item_id' => $candidate['item_id'],
                ], [
                    'planned_date' => $candidate['planned_date'],
                    'sequence' => $nextSequence++,
                    'source' => 'suggested',
                    'status' => 'planned',
                ]);
                $created++;
            }

            if ($created > 0 && $trip->status === 'draft') {
                $trip->update(['status' => 'planned']);
            }

            return $created;
        });
    }

    private function candidate(string $type, int $id, string $title, string $text, ?int $destinationId, mixed $latitude, mixed $longitude, ?string $plannedDate = null, ?array $rating = null): array
    {
        return [
            'item_type' => $type,
            'item_id' => $id,
            'title' => $title,
            'text' => strtolower($text),
            'destination_id' => $destinationId,
            'latitude' => $latitude === null ? null : (float) $latitude,
            'longitude' => $longitude === null ? null : (float) $longitude,
            'planned_date' => $plannedDate,
            'rating' => $rating['average_rating'] ?? null,
            'review_count' => $rating['review_count'] ?? 0,
            'score' => 0,
        ];
    }

    private function score(array $candidate, array $preferences, $destinations): array
    {
        $score = 100;
        foreach ($preferences as $preference) {
            foreach ($this->interestKeywords($preference) as $keyword) {
                if (str_contains($candidate['text'], $keyword)) {
                    $score += 25;
                    break;
                }
            }
        }

        if ($candidate['rating'] !== null) {
            $score += ((float) $candidate['rating']) * 5;
            $score += min(10, (int) $candidate['review_count']);
        }

        $destination = collect($destinations)->firstWhere('destination_id', $candidate['destination_id']);
        if ($destination?->latitude !== null && $destination?->longitude !== null && $candidate['latitude'] !== null && $candidate['longitude'] !== null) {
            $distance = $this->distanceKm((float) $destination->latitude, (float) $destination->longitude, $candidate['latitude'], $candidate['longitude']);
            $score += max(0, 25 - min(25, (int) floor($distance / 5)));
        }

        if ($candidate['item_type'] === 'event') {
            $score += 20;
        }

        $candidate['score'] = $score;

        return $candidate;
    }

    private function interestKeywords(string $interest): array
    {
        return match ($interest) {
            'cultural' => ['cultural', 'heritage', 'museum', 'festival', 'history'],
            'nature' => ['nature', 'wildlife', 'park', 'lake', 'mountain', 'outdoor'],
            'adventure' => ['adventure', 'outdoor', 'hiking', 'trek', 'rafting'],
            'history' => ['history', 'historic', 'heritage', 'castle', 'museum'],
            'food' => ['food', 'restaurant', 'dining', 'coffee', 'cafe'],
            'events' => ['event', 'festival', 'concert', 'celebration'],
            default => [],
        };
    }

    private function ratingsFor(array $serviceIds): Collection
    {
        if ($serviceIds === []) {
            return collect();
        }

        return Review::query()
            ->join('bookings', 'bookings.booking_id', '=', 'reviews.booking_id')
            ->whereIn('bookings.service_id', $serviceIds)
            ->selectRaw('bookings.service_id as target_id, AVG(reviews.rating) as average_rating, COUNT(*) as review_count')
            ->groupBy('bookings.service_id')
            ->get()
            ->keyBy('target_id')
            ->map(fn ($rating): array => ['average_rating' => (float) $rating->average_rating, 'review_count' => (int) $rating->review_count]);
    }

    private function matchingDestinationId(?string $location, $destinations): ?int
    {
        $location = strtolower((string) $location);
        $destination = collect($destinations)->first(fn ($item): bool => str_contains($location, strtolower($item->name)));

        return $destination?->destination_id;
    }

    private function distanceKm(float $latitudeOne, float $longitudeOne, float $latitudeTwo, float $longitudeTwo): float
    {
        $latitudeDifference = deg2rad($latitudeTwo - $latitudeOne);
        $longitudeDifference = deg2rad($longitudeTwo - $longitudeOne);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * sin($longitudeDifference / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }
}
