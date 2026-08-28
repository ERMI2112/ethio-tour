<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransportationProfileRequest;
use App\Models\Booking;
use App\Models\Review;
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

        $recentRequests = Booking::whereIn('service_id', $serviceIds)
            ->whereHas('transportationReservation')
            ->with(['tourist', 'transportationReservation.vehicle', 'tourismService'])
            ->orderByDesc('booking_date')
            ->take(5)
            ->get();

        $scheduledTripsToday = Booking::whereIn('service_id', $serviceIds)
            ->whereHas('transportationReservation', fn ($query) => $query->whereDate('pickup_at', now()->toDateString()))
            ->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])
            ->count();
        $escrowRevenue = (float) Booking::whereIn('service_id', $serviceIds)
            ->whereHas('transportationReservation')
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        $stats = [
            'serviceCount' => $serviceIds->count(),
            'vehicleCount' => $vehicleCount,
            'activeVehicles' => $activeVehicles,
            'pendingReservations' => $pendingReservations,
            'activeReservations' => $activeReservations,
            'scheduledTripsToday' => $scheduledTripsToday,
            'escrowRevenue' => $escrowRevenue,
            'reviewAverage' => (clone $reviewQuery)->avg('rating'),
            'reviewCount' => (clone $reviewQuery)->count(),
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
