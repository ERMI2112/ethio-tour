<?php

namespace App\Http\Controllers;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismService;
use App\Support\PortalCatalog;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(): View
    {
        $destinations = Schema::hasTable('destinations')
            ? Destination::query()->orderBy('name')->limit(6)->get()
            : collect();

        $publicServices = fn () => TourismService::query()
            ->with(['destination', 'category', 'serviceProvider'])
            ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational());

        $experiences = Schema::hasTable('tourism_services') && Schema::hasTable('service_providers')
            ? $publicServices()->orderBy('service_name')->limit(6)->get()
            : collect();

        $gondar = $destinations->firstWhere('name', 'Gondar');
        if (! $gondar && Schema::hasTable('destinations')) {
            $gondar = Destination::query()->where('name', 'Gondar')->first();
        }

        $gondarServices = $gondar && Schema::hasTable('tourism_services') && Schema::hasTable('service_providers')
            ? $publicServices()->where('destination_id', $gondar->destination_id)->orderBy('service_name')->limit(4)->get()
            : collect();

        $accommodations = Schema::hasTable('tourism_services') && Schema::hasTable('service_providers')
            ? $publicServices()->whereHas('serviceProvider', fn ($query) => $query->where('provider_type', 'hotel'))->orderBy('service_name')->limit(4)->get()
            : collect();

        $dining = Schema::hasTable('tourism_services') && Schema::hasTable('service_providers')
            ? $publicServices()->whereHas('serviceProvider', fn ($query) => $query->where('provider_type', 'restaurant'))->orderBy('service_name')->limit(4)->get()
            : collect();

        $transport = Schema::hasTable('tourism_services') && Schema::hasTable('service_providers')
            ? $publicServices()->whereHas('serviceProvider', fn ($query) => $query->where('provider_type', 'transportation_car_rental'))->orderBy('service_name')->limit(4)->get()
            : collect();

        $events = Schema::hasTable('cultural_events') && Schema::hasTable('service_providers')
            ? CulturalEvent::query()->with(['destination', 'serviceProvider'])->where('status', 'published')->whereDate('event_date', '>=', today())->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->orderBy('event_date')->limit(6)->get()
            : collect();

        $gondarEvents = $gondar && Schema::hasTable('cultural_events') && Schema::hasTable('service_providers')
            ? CulturalEvent::query()->where('destination_id', $gondar->destination_id)->where('status', 'published')->whereDate('event_date', '>=', today())->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->orderBy('event_date')->limit(6)->get()
            : collect();

        $guides = Schema::hasTable('tour_guides') && Schema::hasTable('users')
            ? TourGuide::query()->with('user')->where('verification_status', 'verified')->whereHas('user', fn ($query) => $query->where('is_active', true))->orderBy('guide_id')->limit(4)->get()
            : collect();

        $attractions = $gondar && Schema::hasTable('attractions')
            ? $gondar->attractions()->orderByDesc('is_featured')->limit(4)->get()
            : collect();

        $heritageSites = $gondar && Schema::hasTable('heritage_sites')
            ? HeritageSite::query()->with('destination')->where('destination_id', $gondar->destination_id)->limit(3)->get()
            : collect();

        $museums = Schema::hasTable('museum_information')
            ? MuseumInformation::query()->where('location', 'like', '%Gondar%')->orderBy('museum_name')->limit(3)->get()
            : collect();

        $reviews = Schema::hasTable('reviews') && Schema::hasTable('bookings')
            ? Review::query()->with(['tourist', 'booking.tourismService.serviceProvider', 'booking.tourGuide.user'])
                ->where(function ($query): void {
                    $query->whereHas('booking.tourismService', fn ($service) => $service->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational()))
                        ->orWhereHas('booking.tourGuide', fn ($guide) => $guide->where('verification_status', 'verified')->whereHas('user', fn ($user) => $user->where('is_active', true)));
                })
                ->latest('review_date')
                ->limit(3)
                ->get()
            : collect();

        $publishedDestinationCount = Schema::hasTable('destinations')
            ? Destination::query()->count()
            : 0;
        $operationalProviderCount = Schema::hasTable('service_providers') && Schema::hasTable('users')
            ? ServiceProvider::query()->publiclyOperational()->count()
            : 0;
        $verifiedGuideCount = Schema::hasTable('tour_guides') && Schema::hasTable('users')
            ? TourGuide::query()->where('verification_status', 'verified')->whereHas('user', fn ($query) => $query->where('is_active', true))->count()
            : 0;
        $publishedEventCount = Schema::hasTable('cultural_events') && Schema::hasTable('service_providers')
            ? CulturalEvent::query()->where('status', 'published')->whereDate('event_date', '>=', today())->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->count()
            : 0;

        $portals = PortalCatalog::all();

        return view('welcome', compact(
            'destinations',
            'experiences',
            'gondar',
            'gondarServices',
            'gondarEvents',
            'accommodations',
            'dining',
            'transport',
            'events',
            'guides',
            'attractions',
            'heritageSites',
            'museums',
            'reviews',
            'publishedDestinationCount',
            'operationalProviderCount',
            'verifiedGuideCount',
            'publishedEventCount',
            'portals',
        ));
    }
}
