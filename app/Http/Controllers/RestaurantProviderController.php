<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantProfileRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');

        $stats = [
            'serviceCount' => $serviceIds->count(),
            'tableCount' => $provider->restaurantTables()->count(),
            'activeTables' => $provider->restaurantTables()->where('status', 'active')->count(),
            'pendingReservations' => Booking::whereIn('service_id', $serviceIds)->where('status', 'pending')->count(),
            'upcomingReservations' => Booking::whereIn('service_id', $serviceIds)->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->count(),
        ];

        return view('restaurant.dashboard', compact('provider', 'stats'));
    }

    public function show(Request $request): View
    {
        return view('restaurant.profile.show', ['provider' => $request->user()->serviceProvider->load('user')]);
    }

    public function edit(Request $request): View
    {
        return view('restaurant.profile.edit', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(RestaurantProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('restaurant.profile')->with('success', 'Restaurant profile updated.');
    }
}
