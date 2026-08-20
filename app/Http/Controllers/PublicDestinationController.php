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

        $destinations = Destination::query()
            ->withCount([
                'heritageSites',
                'culturalEvents as upcoming_events_count' => fn ($query) => $query->where('status', 'published')->whereDate('event_date', '>=', now()->toDateString()),
                'tourismServices as public_services_count' => fn ($query) => $query->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational()),
            ])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        return view('public.destinations.index', compact('destinations', 'search'));
    }

    public function show(Destination $destination): View
    {
        $destination->load([
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
            ->withCount(['heritageSites', 'tourismServices' => fn ($q) => $q->whereHas('serviceProvider', fn ($p) => $p->publiclyOperational())])
            ->limit(3)
            ->get();

        return view('public.destinations.show', compact(
            'destination',
            'hotels',
            'restaurants',
            'transportation',
            'otherServices',
            'serviceRatings',
            'otherDestinations'
        ));
    }
}
