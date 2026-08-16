<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderReportsController extends Controller
{
    public function index(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        abort_unless($provider, 403);
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $bookings = Booking::whereIn('service_id', $serviceIds)->whereHas('tourismService', fn ($query) => $query->where('provider_id', $provider->provider_id));
        $reviews = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds));

        return view('provider.reports.index', ['provider' => $provider, 'bookingTotal' => (clone $bookings)->count(), 'bookingStatuses' => (clone $bookings)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'), 'reviewCount' => (clone $reviews)->count(), 'reviewAverage' => (clone $reviews)->avg('rating'), 'serviceCount' => $serviceIds->count()]);
    }
}
