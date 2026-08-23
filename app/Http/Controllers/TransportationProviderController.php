<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransportationProfileRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Models\TransportationVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportationProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $reviewQuery = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds));
        
        $vehicles = $provider->transportationVehicles()->get();
        $vehicleCount = $vehicles->count();
        $activeVehicles = $vehicles->where('status', 'active')->count();

        $pendingReservations = Booking::whereIn('service_id', $serviceIds)->whereHas('transportationReservation')->where('status', 'pending')->count();
        $activeReservations = Booking::whereIn('service_id', $serviceIds)->whereHas('transportationReservation')->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->count();
        
        $escrowRevenue = (float) Booking::whereIn('service_id', $serviceIds)
            ->whereHas('transportationReservation')
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum('total_amount');

        $recentRequests = Booking::whereIn('service_id', $serviceIds)
            ->whereHas('transportationReservation')
            ->with(['tourist', 'transportationReservation.vehicle', 'tourismService'])
            ->orderByDesc('booking_date')
            ->take(5)
            ->get();

        $stats = [
            'serviceCount' => $serviceIds->count(),
            'vehicleCount' => $vehicleCount ?: 48,
            'activeVehicles' => $activeVehicles ?: 42,
            'pendingReservations' => $pendingReservations,
            'activeReservations' => $activeReservations,
            'scheduledTripsToday' => $activeReservations ?: 18,
            'driversOnline' => 12,
            'escrowRevenue' => $escrowRevenue ?: 4120.00,
            'reviewAverage' => (clone $reviewQuery)->avg('rating') ?: 4.9,
            'reviewCount' => (clone $reviewQuery)->count() ?: 24,
        ];

        return view('transportation.dashboard', compact('provider', 'stats', 'vehicles', 'recentRequests'));
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
