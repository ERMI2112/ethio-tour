<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelProfileRequest;
use App\Models\Booking;
use App\Models\HotelRoom;
use App\Services\HotelAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');

        $rooms = HotelRoom::query()
            ->withCount('hotelRoomReservations')
            ->whereHas('hotelRoomType.tourismService', fn ($query) => $query->whereIn('service_id', $serviceIds))
            ->get();

        $statuses = ['pending', 'accepted', 'payment_pending', 'confirmed', 'cancelled', 'completed', 'rejected'];
        $counts = Booking::query()
            ->whereIn('service_id', $serviceIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reservationCounts = [];
        foreach ($statuses as $status) {
            $reservationCounts[$status] = (int) ($counts[$status] ?? 0);
        }

        $upcomingStays = Booking::query()
            ->whereIn('service_id', $serviceIds)
            ->whereIn('status', HotelAvailabilityService::INVENTORY_RESERVING_STATUSES)
            ->whereHas('hotelRoomReservation', fn ($query) => $query->whereDate('check_in_date', '>=', now()->toDateString()))
            ->count();

        $stats = [
            'roomTypeCount' => $provider->tourismServices()->count(),
            'totalRooms' => $rooms->count(),
            'activeRooms' => $rooms->where('status', 'active')->count(),
            'inactiveRooms' => $rooms->where('status', 'inactive')->count(),
            'reservations' => $reservationCounts,
            'upcomingStays' => $upcomingStays,
            'pendingAttention' => $reservationCounts['pending'],
        ];

        return view('hotel.dashboard', compact('provider', 'stats'));
    }

    public function show(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $provider->loadCount('tourismServices');

        return view('hotel.profile.show', compact('provider'));
    }

    public function edit(Request $request): View
    {
        return view('hotel.profile.edit', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(HotelProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('hotel.profile')->with('success', 'Hotel provider profile updated.');
    }
}
