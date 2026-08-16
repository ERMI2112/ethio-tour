<?php

namespace App\Services;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourismService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    private const PER_PAGE = 20;

    public function search(Request $request): LengthAwarePaginator
    {
        $term = $request->string('q')->trim()->value();
        $type = $request->string('type')->trim()->value();
        $destination = $request->integer('destination') ?: null;
        $category = $request->integer('category') ?: null;
        $date = $this->normalizeDate($request->input('date'));
        $minimumRating = $this->minimumRating($request->input('rating'));
        $types = $type && in_array($type, $this->types(), true) ? [$type] : $this->types();
        $results = collect();

        if (in_array('destinations', $types, true)) {
            $items = Destination::query()
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                }))
                ->limit(40)
                ->get();

            $results = $results->merge($items->map(fn (Destination $item): array => $this->result(
                'destination',
                'Destinations',
                $item->name,
                $item->description,
                $item->location,
                route('destinations.show', $item),
                $this->score($term, $item->name, $item->description, $item->location),
                null,
                $this->mapUrl('destinations', $item->latitude, $item->longitude, $item->name),
                'destination',
                $item->destination_id,
            )));
        }

        if (in_array('heritage', $types, true)) {
            $items = HeritageSite::query()
                ->with('destination')
                ->when($destination, fn ($query) => $query->where('destination_id', $destination))
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('heritage_type', 'like', "%{$term}%")
                        ->orWhereHas('destination', fn ($destination) => $destination->where('name', 'like', "%{$term}%"));
                }))
                ->limit(40)
                ->get();

            $results = $results->merge($items->map(fn (HeritageSite $item): array => $this->result(
                'heritage',
                'Heritage Sites',
                $item->heritage_type,
                $item->destination?->name,
                $item->destination?->name,
                route('heritage-sites.show', $item),
                $this->score($term, $item->heritage_type, $item->destination?->name),
                null,
                $this->mapUrl('heritage_sites', $item->latitude, $item->longitude, $item->heritage_type),
                'heritage_site',
                $item->heritage_id,
            )));
        }

        if (in_array('museums', $types, true)) {
            $items = MuseumInformation::query()
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('museum_name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                }))
                ->limit(40)
                ->get();

            $results = $results->merge($items->map(fn (MuseumInformation $item): array => $this->result(
                'museum',
                'Museums',
                $item->museum_name,
                $item->description,
                $item->location,
                route('museums.show', $item),
                $this->score($term, $item->museum_name, $item->description, $item->location),
                null,
                $this->mapUrl('museums', $item->latitude, $item->longitude, $item->museum_name),
                'museum',
                $item->museum_id,
            )));
        }

        if (in_array('services', $types, true) || array_intersect(['hotels', 'restaurants', 'transportation'], $types)) {
            $serviceTypes = array_intersect(['hotels', 'restaurants', 'transportation'], $types);
            $items = TourismService::query()
                ->with(['serviceProvider', 'destination', 'category'])
                ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())
                ->when($serviceTypes && ! in_array('services', $types, true), fn ($query) => $query->whereHas('serviceProvider', fn ($provider) => $provider->whereIn('provider_type', $this->providerTypes($serviceTypes))))
                ->when($destination, fn ($query) => $query->where('destination_id', $destination))
                ->when($category, fn ($query) => $query->where('category_id', $category))
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('service_name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('category', fn ($category) => $category->where('category_name', 'like', "%{$term}%"))
                        ->orWhereHas('destination', fn ($destination) => $destination->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('serviceProvider', fn ($provider) => $provider->where('business_name', 'like', "%{$term}%"));
                }))
                ->limit(40)
                ->get();
            $ratings = $this->ratingsFor('service_id', $items->pluck('service_id')->all());

            $results = $results->merge($items->map(fn (TourismService $item): array => $this->serviceResult($item, $term, $this->rating($ratings, $item->service_id))));
        }

        if (in_array('guides', $types, true)) {
            $items = TourGuide::query()
                ->with('user')
                ->where('verification_status', 'verified')
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('expertise', 'like', "%{$term}%")
                        ->orWhere('license_number', 'like', "%{$term}%");
                }))
                ->limit(40)
                ->get();
            $ratings = $this->ratingsFor('guide_id', $items->pluck('guide_id')->all());

            $results = $results->merge($items->map(fn (TourGuide $item): array => $this->result(
                'guide',
                'Tour Guides',
                'Tour Guide Profile',
                $item->expertise,
                $item->availability_status,
                route('tour-guides.show', $item),
                $this->score($term, $item->expertise, $item->license_number),
                $this->rating($ratings, $item->guide_id),
                null,
                'guide',
                $item->guide_id,
            )));
        }

        if (in_array('events', $types, true)) {
            $items = CulturalEvent::query()
                ->with(['destination', 'serviceProvider'])
                ->where('status', 'published')
                ->whereDate('event_date', '>=', today())
                ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())
                ->when($destination, fn ($query) => $query->where('destination_id', $destination))
                ->when($date, fn ($query) => $query->whereDate('event_date', $date))
                ->when($term, fn ($query) => $query->where(function ($query) use ($term): void {
                    $query->where('event_name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('venue', 'like', "%{$term}%")
                        ->orWhereHas('destination', fn ($destination) => $destination->where('name', 'like', "%{$term}%"));
                }))
                ->limit(40)
                ->get();
            $ratings = $this->ratingsFor('service_id', $items->pluck('service_id')->filter()->all());

            $results = $results->merge($items->map(fn (CulturalEvent $item): array => $this->result(
                'event',
                'Events',
                $item->event_name,
                $item->description,
                $item->destination?->name.' · '.$item->venue,
                route('events.show', $item),
                $this->score($term, $item->event_name, $item->description, $item->venue, $item->destination?->name),
                $this->rating($ratings, $item->service_id),
                $this->mapUrl('events', $item->latitude, $item->longitude, $item->event_name),
                'event',
                $item->event_id,
            )));
        }

        $results = $this->filterByRating($results, $minimumRating)->sortByDesc('score')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator($results->forPage($page, self::PER_PAGE)->values(), $results->count(), self::PER_PAGE, $page, ['path' => $request->url(), 'query' => $request->query()]);
    }

    public function types(): array
    {
        return ['destinations', 'heritage', 'museums', 'hotels', 'restaurants', 'transportation', 'guides', 'events', 'services'];
    }

    public function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function result(string $type, string $label, string $title, ?string $summary, ?string $destination, string $url, int $score, ?float $rating = null, ?string $mapUrl = null, ?string $tripItemType = null, ?int $tripItemId = null): array
    {
        return ['type' => $type, 'type_label' => $label, 'title' => $title, 'summary' => $summary, 'destination' => $destination, 'rating' => $rating, 'url' => $url, 'map_url' => $mapUrl, 'score' => $score, 'trip_item_type' => $tripItemType, 'trip_item_id' => $tripItemId];
    }

    private function serviceResult(TourismService $service, string $term, ?float $rating): array
    {
        $type = match ($service->serviceProvider?->provider_type) {
            'hotel' => 'hotel',
            'restaurant' => 'restaurant',
            'transportation_car_rental' => 'transportation',
            default => 'service',
        };
        $mapCategory = match ($type) {
            'hotel' => 'hotels',
            'restaurant' => 'restaurants',
            'transportation' => 'transportation',
            default => 'services',
        };

        return $this->result(
            $type,
            $type === 'service' ? 'Tourism Services' : ucfirst($type.'s'),
            $service->service_name,
            $service->description,
            $service->destination?->name.' · '.$service->serviceProvider?->business_name,
            route('tourism-services.show', $service),
            $this->score($term, $service->service_name, $service->description, $service->destination?->name, $service->category?->category_name, $service->serviceProvider?->business_name),
            $rating,
            $this->mapUrl($mapCategory, $service->latitude, $service->longitude, $service->service_name),
            'service',
            $service->service_id,
        );
    }

    private function ratingsFor(string $bookingColumn, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Review::query()
            ->join('bookings', 'bookings.booking_id', '=', 'reviews.booking_id')
            ->whereIn('bookings.'.$bookingColumn, $ids)
            ->selectRaw('bookings.'.$bookingColumn.' as target_id, AVG(reviews.rating) as average_rating')
            ->groupBy('bookings.'.$bookingColumn)
            ->pluck('average_rating', 'target_id');
    }

    private function rating(Collection $ratings, mixed $id): ?float
    {
        $rating = $ratings->get($id);

        return $rating === null ? null : (float) $rating;
    }

    private function filterByRating(Collection $results, ?float $minimumRating): Collection
    {
        if ($minimumRating === null) {
            return $results;
        }

        return $results->filter(fn (array $result): bool => $result['rating'] !== null && $result['rating'] >= $minimumRating);
    }

    private function minimumRating(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $rating = (float) $value;

        return $rating >= 1 && $rating <= 5 ? $rating : null;
    }

    private function mapUrl(string $category, mixed $latitude, mixed $longitude, ?string $query = null): ?string
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $parameters = ['category' => $category];
        if ($query) {
            $parameters['q'] = $query;
        }

        return route('map', $parameters);
    }

    private function score(string $term, ?string ...$fields): int
    {
        if ($term === '') {
            return 0;
        }

        $term = strtolower($term);
        $name = strtolower((string) ($fields[0] ?? ''));
        if ($name === $term) {
            return 1000;
        }
        if (str_starts_with($name, $term)) {
            return 700;
        }
        if (str_contains($name, $term)) {
            return 500;
        }

        foreach (array_slice($fields, 1) as $index => $field) {
            $value = strtolower((string) $field);
            if ($value === $term) {
                return max(300 - ($index * 10), 1);
            }
            if (str_starts_with($value, $term)) {
                return max(200 - ($index * 10), 1);
            }
            if (str_contains($value, $term)) {
                return max(100 - ($index * 10), 1);
            }
        }

        return 0;
    }

    private function providerTypes(array $types): array
    {
        return array_values(array_filter(array_map(fn (string $type) => match ($type) {
            'hotels' => 'hotel',
            'restaurants' => 'restaurant',
            'transportation' => 'transportation_car_rental',
            default => null,
        }, $types)));
    }
}
