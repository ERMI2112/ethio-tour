<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransportationProfileRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportationProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $stats = [
            'serviceCount' => $serviceIds->count(),
            'vehicleCount' => $provider->transportationVehicles()->count(),
            'activeVehicles' => $provider->transportationVehicles()->where('status', 'active')->count(),
            'pendingReservations' => Booking::whereIn('service_id', $serviceIds)->whereHas('transportationReservation')->where('status', 'pending')->count(),
        ];

        return view('transportation.dashboard', compact('provider', 'stats'));
    }

    public function show(Request $request): View
    {
        return view('transportation.profile.show', ['provider' => $request->user()->serviceProvider->load('user')]);
    }

    public function edit(Request $request): View
    {
        return view('transportation.profile.edit', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(TransportationProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('transportation.profile')->with('success', 'Transportation profile updated.');
    }
}
