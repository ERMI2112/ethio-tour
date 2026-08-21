<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDestinationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $category = $request->string('category')->trim()->value();
        $region = $request->string('region')->trim()->value();
        $amenity = $request->string('amenity')->trim()->value();
        $sort = $request->string('sort')->trim()->value() ?: 'recommended';

        // Base query with counts
        $baseQuery = Destination::query()
            ->withCount([
                'attractions',
                'heritageSites',
                'culturalEvents as upcoming_events_count' => fn ($query) => $query->where('status', 'published')->whereDate('event_date', '>=', now()->toDateString()),
                'tourismServices as public_services_count' => fn ($query) => $query->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational()),
            ]);

        // Filtered Query
        $destinationsQuery = (clone $baseQuery)
            ->when($search !== '', fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($region !== '', fn ($q) => $q->where('region', $region))
            ->when($amenity !== '', fn ($q) => $q->whereJsonContains('amenities', $amenity));

        // Sorting
        match ($sort) {
            'name_asc' => $destinationsQuery->orderBy('name', 'asc'),
            'name_desc' => $destinationsQuery->orderBy('name', 'desc'),
            'attractions_count' => $destinationsQuery->orderByDesc('attractions_count')->orderBy('name'),
            'services_count' => $destinationsQuery->orderByDesc('public_services_count')->orderBy('name'),
            default => $destinationsQuery->orderByDesc('is_featured')->orderByDesc('attractions_count')->orderBy('name'),
        };

        $destinations = $destinationsQuery->get();

        // Calculate sidebar facets
        $allDestinations = Destination::all();
        $totalSpacesCount = $allDestinations->count();

        $categoryCounts = [];
        foreach (Destination::CATEGORIES as $catKey => $catLabel) {
            $count = $allDestinations->where('category', $catKey)->count();
            if ($count > 0) {
                $categoryCounts[$catKey] = [
                    'label' => $catLabel,
                    'count' => $count,
                ];
            }
        }

        $regionCounts = [];
        foreach (Destination::REGIONS as $regionKey => $regionLabel) {
            $count = $allDestinations->where('region', $regionKey)->count();
            if ($count > 0) {
                $regionCounts[$regionKey] = [
                    'label' => $regionKey,
                    'circuit' => $regionLabel,
                    'count' => $count,
                ];
            }
        }

        $commonAmenities = [
            'Guided Tours',
            'UNESCO Certified',
            'Photography Allowed',
            'Family Friendly',
            'Scenic Viewpoint',
            'Eco-Friendly',
            'Wi-Fi',
            'Luxury Eco-Resort',
            'Hiking Trails',
        ];
        $amenityCounts = [];
        foreach ($commonAmenities as $am) {
            $count = $allDestinations->filter(fn ($d) => is_array($d->amenities) && in_array($am, $d->amenities, true))->count();
            if ($count > 0) {
                $amenityCounts[$am] = $count;
            }
        }

        return view('public.destinations.index', compact(
            'destinations',
            'search',
            'category',
            'region',
            'amenity',
            'sort',
            'totalSpacesCount',
            'categoryCounts',
            'regionCounts',
            'amenityCounts'
        ));
    }

    public function show(Destination $destination): View
    {
        $destination->load([
            'attractions' => fn ($query) => $query->orderByDesc('is_featured')->orderBy('name'),
            'heritageSites' => fn ($query) => $query->orderBy('heritage_type'),
            'culturalEvents' => fn ($query) => $query
                ->where('status', 'published')
                ->whereDate('event_date', '>=', now()->toDateString())
                ->with('ticketTypes')
                ->orderBy('event_date'),
            'tourismServices' => fn ($query) => $query
                ->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational())
                ->with(['category', 'serviceProvider', 'hotelRoomType', 'transportationVehicles'])
                ->orderBy('service_name'),
        ]);

        $attractions = $destination->attractions;
        $hotels = $destination->tourismServices->filter(fn ($s) => $s->serviceProvider?->provider_type === 'hotel')->values();
        $restaurants = $destination->tourismServices->filter(fn ($s) => $s->serviceProvider?->provider_type === 'restaurant')->values();
        $transportation = $destination->tourismServices->filter(fn ($s) => $s->serviceProvider?->provider_type === 'transportation_car_rental')->values();
        $otherServices = $destination->tourismServices->reject(fn ($s) => in_array($s->serviceProvider?->provider_type, ['hotel', 'restaurant', 'transportation_car_rental'], true))->values();

        // Calculate authoritative average ratings for services from real completed booking reviews
        $serviceIds = $destination->tourismServices->pluck('service_id')->filter()->all();
        $serviceRatings = [];
        if (! empty($serviceIds)) {
            $serviceRatings = Review::query()
                ->join('bookings', 'reviews.booking_id', '=', 'bookings.booking_id')
                ->whereIn('bookings.service_id', $serviceIds)
                ->selectRaw('bookings.service_id, AVG(reviews.rating) as avg_rating, COUNT(reviews.review_id) as review_count')
                ->groupBy('bookings.service_id')
                ->get()
                ->keyBy('service_id')
                ->map(fn ($row) => [
                    'avg' => round((float) $row->avg_rating, 1),
                    'count' => (int) $row->review_count,
                ])
                ->all();
        }

        $otherDestinations = Destination::query()
            ->where('destination_id', '!=', $destination->destination_id)
            ->withCount(['attractions', 'heritageSites', 'tourismServices' => fn ($q) => $q->whereHas('serviceProvider', fn ($p) => $p->publiclyOperational())])
            ->limit(3)
            ->get();

        return view('public.destinations.show', compact(
            'destination',
            'attractions',
            'hotels',
            'restaurants',
            'transportation',
            'otherServices',
            'serviceRatings',
            'otherDestinations'
        ));
    }
}
