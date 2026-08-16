<?php

namespace App\Services;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourismService;
use App\Models\TripItem;
use Illuminate\Database\Eloquent\Model;

class TripItemTargetResolver
{
    public const TYPES = ['destination', 'heritage_site', 'museum', 'service', 'guide', 'event'];

    private const TARGETS = [
        'destination' => Destination::class,
        'heritage_site' => HeritageSite::class,
        'museum' => MuseumInformation::class,
        'service' => TourismService::class,
        'guide' => TourGuide::class,
        'event' => CulturalEvent::class,
    ];

    public function resolve(string $type, int $id): ?Model
    {
        $class = self::TARGETS[$type] ?? null;
        if ($class === null) {
            return null;
        }

        $query = $class::query();
        match ($type) {
            'heritage_site' => $query->with('destination'),
            'service' => $query->with(['serviceProvider.user', 'destination', 'category']),
            'guide' => $query->with('user'),
            'event' => $query->with(['destination', 'serviceProvider.user']),
            default => $query,
        };

        $target = $query->whereKey($id)->first();
        if ($target === null || ! $this->isPublic($type, $target)) {
            return null;
        }

        return $target;
    }

    public function present(TripItem $item): ?array
    {
        $target = $this->resolve($item->item_type, (int) $item->item_id);
        if ($target === null) {
            return null;
        }

        $description = $this->describe($item->item_type, $target);

        return $description + [
            'trip_item_id' => $item->trip_item_id,
            'planned_date' => $item->planned_date?->toDateString(),
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'sequence' => $item->sequence,
            'notes' => $item->notes,
            'status' => $item->status,
            'source' => $item->source,
        ];
    }

    public function presentItems($items): array
    {
        return collect($items)
            ->sortBy(['planned_date', 'sequence'])
            ->map(fn (TripItem $item): ?array => $this->present($item))
            ->filter()
            ->values()
            ->all();
    }

    public function describe(string $type, Model $target): array
    {
        $title = match ($type) {
            'destination' => $target->name,
            'heritage_site' => $target->heritage_type,
            'museum' => $target->museum_name,
            'service' => $target->service_name,
            'guide' => 'Tour Guide Profile',
            'event' => $target->event_name,
        };
        $destination = match ($type) {
            'destination' => $target->location,
            'heritage_site' => $target->destination?->name,
            'museum' => $target->location,
            'service' => $target->destination?->name,
            'guide' => null,
            'event' => $target->destination?->name,
        };
        $summary = match ($type) {
            'destination' => $target->description,
            'heritage_site' => $target->destination?->name.' · Open '.$target->opening_hours,
            'museum' => $target->description,
            'service' => $target->description,
            'guide' => $target->expertise,
            'event' => $target->description.' · '.$target->venue,
        };
        $rating = $this->rating($type, $target);

        return [
            'item_type' => $type,
            'item_id' => $target->getKey(),
            'type' => $this->typeLabel($type, $target),
            'type_label' => $this->typeLabel($type, $target),
            'title' => $title,
            'summary' => $summary,
            'destination' => $destination,
            'rating' => $rating,
            'detail_url' => $this->detailUrl($type, $target),
            'map_url' => $this->mapUrl($type, $target, $title),
            'booking_url' => $this->bookingUrl($type, $target),
            'latitude' => $target->latitude ?? null,
            'longitude' => $target->longitude ?? null,
        ];
    }

    private function isPublic(string $type, Model $target): bool
    {
        return match ($type) {
            'service' => $target->serviceProvider?->isOperational() === true,
            'guide' => $target->verification_status === 'verified' && $target->user?->is_active === true,
            'event' => $target->status === 'published'
                && ($target->event_date?->isFuture() || $target->event_date?->isToday())
                && $target->serviceProvider?->isOperational() === true,
            default => true,
        };
    }

    private function typeLabel(string $type, Model $target): string
    {
        if ($type === 'service') {
            return match ($target->serviceProvider?->provider_type) {
                'hotel' => 'Hotel',
                'restaurant' => 'Restaurant',
                'transportation_car_rental' => 'Transportation',
                default => 'Tourism Service',
            };
        }

        return match ($type) {
            'destination' => 'Destination',
            'heritage_site' => 'Heritage Site',
            'museum' => 'Museum',
            'guide' => 'Tour Guide',
            'event' => 'Event',
        };
    }

    private function detailUrl(string $type, Model $target): string
    {
        return match ($type) {
            'destination' => route('destinations.show', $target),
            'heritage_site' => route('heritage-sites.show', $target),
            'museum' => route('museums.show', $target),
            'service' => $target->serviceProvider?->provider_type === 'transportation_car_rental'
                ? route('transportation.show', $target)
                : route('tourism-services.show', $target),
            'guide' => route('tour-guides.show', $target),
            'event' => route('events.show', $target),
        };
    }

    private function bookingUrl(string $type, Model $target): ?string
    {
        return match ($type) {
            'service' => $this->serviceBookingUrl($target),
            'guide' => $target->availability_status === 'available' && $target->daily_rate !== null
                ? route('tour-guides.book', $target)
                : null,
            'event' => route('events.show', $target),
            default => null,
        };
    }

    private function serviceBookingUrl(TourismService $service): string
    {
        return match ($service->serviceProvider?->provider_type) {
            'transportation_car_rental' => route('transportation.show', $service),
            default => route('tourism-services.show', $service),
        };
    }

    private function mapUrl(string $type, Model $target, string $title): ?string
    {
        if ($target->latitude === null || $target->longitude === null) {
            return null;
        }

        $category = match ($type) {
            'destination' => 'destinations',
            'heritage_site' => 'heritage_sites',
            'museum' => 'museums',
            'service' => match ($target->serviceProvider?->provider_type) {
                'hotel' => 'hotels',
                'restaurant' => 'restaurants',
                'transportation_car_rental' => 'transportation',
                default => 'services',
            },
            'event' => 'events',
            default => null,
        };

        return $category === null ? null : route('map', ['category' => $category, 'q' => $title]);
    }

    private function rating(string $type, Model $target): ?float
    {
        $column = match ($type) {
            'service', 'event' => 'service_id',
            'guide' => 'guide_id',
            default => null,
        };
        if ($column === null) {
            return null;
        }

        $targetId = $type === 'event' ? $target->service_id : $target->getKey();
        if ($targetId === null) {
            return null;
        }

        $average = Review::query()
            ->whereHas('booking', fn ($query) => $query->where($column, $targetId))
            ->avg('rating');

        return $average === null ? null : (float) $average;
    }
}
