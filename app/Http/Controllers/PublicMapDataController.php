<?php

namespace App\Http\Controllers;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\TourismService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMapDataController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $category = $request->string('category')->trim()->value();
        $search = $request->string('q')->trim()->value();
        $destinationId = $request->integer('destination');
        $allowed = ['destinations', 'heritage_sites', 'museums', 'services', 'hotels', 'restaurants', 'transportation', 'events'];
        $serviceType = match ($category) {
            'hotels' => 'hotel',
            'restaurants' => 'restaurant',
            'transportation' => 'transportation_car_rental',
            default => null,
        };
        $categories = $category && in_array($category, $allowed, true) ? [$serviceType ? 'services' : $category] : $allowed;
        $markers = collect();

        if (in_array('destinations', $categories, true)) {
            $markers = $markers->merge(Destination::query()->whereNotNull('latitude')->whereNotNull('longitude')->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%");
            }))->get()->map(fn (Destination $destination): array => $this->marker('destination', $destination->name, $destination->description, $destination->latitude, $destination->longitude, route('destinations.show', $destination))));
        }

        if (in_array('heritage_sites', $categories, true)) {
            $markers = $markers->merge(HeritageSite::query()->with('destination')->whereNotNull('latitude')->whereNotNull('longitude')->when($destinationId, fn ($query) => $query->where('destination_id', $destinationId))->when($search, fn ($query) => $query->where('heritage_type', 'like', "%{$search}%"))->get()->map(fn (HeritageSite $site): array => $this->marker('heritage_site', $site->heritage_type, $site->destination?->name, $site->latitude, $site->longitude, route('heritage-sites.show', $site))));
        }

        if (in_array('museums', $categories, true)) {
            $markers = $markers->merge(MuseumInformation::query()->whereNotNull('latitude')->whereNotNull('longitude')->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('museum_name', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%");
            }))->get()->map(fn (MuseumInformation $museum): array => $this->marker('museum', $museum->museum_name, $museum->description, $museum->latitude, $museum->longitude, route('museums.show', $museum))));
        }

        if (in_array('services', $categories, true)) {
            $markers = $markers->merge(TourismService::query()->with('serviceProvider')->whereNotNull('latitude')->whereNotNull('longitude')->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->when($serviceType, fn ($query) => $query->whereHas('serviceProvider', fn ($provider) => $provider->where('provider_type', $serviceType)))->when($destinationId, fn ($query) => $query->where('destination_id', $destinationId))->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('service_name', 'like', "%{$search}%")->orWhereHas('serviceProvider', fn ($provider) => $provider->where('business_name', 'like', "%{$search}%"));
            }))->get()->map(fn (TourismService $service): array => $this->marker($this->serviceCategory($service), $service->service_name, $service->serviceProvider?->business_name, $service->latitude, $service->longitude, route('tourism-services.show', $service))));
        }

        if (in_array('events', $categories, true)) {
            $markers = $markers->merge(CulturalEvent::query()->with('serviceProvider')->whereNotNull('latitude')->whereNotNull('longitude')->where('status', 'published')->whereDate('event_date', '>=', today())->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->when($destinationId, fn ($query) => $query->where('destination_id', $destinationId))->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('event_name', 'like', "%{$search}%")->orWhere('venue', 'like', "%{$search}%");
            }))->get()->map(fn (CulturalEvent $event): array => $this->marker('event', $event->event_name, $event->event_date?->toDateString().' · '.$event->venue, $event->latitude, $event->longitude, route('events.show', $event))));
        }

        return response()->json(['data' => $markers->values(), 'count' => $markers->count()]);
    }

    private function marker(string $type, string $title, ?string $summary, mixed $latitude, mixed $longitude, string $url): array
    {
        return ['type' => $type, 'title' => $title, 'summary' => $summary, 'latitude' => (float) $latitude, 'longitude' => (float) $longitude, 'url' => $url];
    }

    private function serviceCategory(TourismService $service): string
    {
        return match ($service->serviceProvider?->provider_type) {
            'hotel' => 'hotel',
            'restaurant' => 'restaurant',
            'transportation_car_rental' => 'transportation',
            default => 'tourism_service',
        };
    }
}
