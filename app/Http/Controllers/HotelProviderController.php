<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelProfileRequest;
use App\Models\Booking;
use App\Models\HotelRoom;
use App\Models\Review;
use App\Services\HotelAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider->load(['user', 'tourismServices.category']);
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $reviewQuery = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds));

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

        $totalRoomsCount = $rooms->count();
        $activeRoomsCount = $rooms->where('status', 'active')->count();
        $activeBookedSuites = Booking::query()
            ->whereIn('service_id', $serviceIds)
            ->whereIn('status', HotelAvailabilityService::INVENTORY_RESERVING_STATUSES)
            ->whereHas('hotelRoomReservation', fn ($query) => $query
                ->whereDate('check_in_date', '<=', now()->toDateString())
                ->whereDate('check_out_date', '>', now()->toDateString()))
            ->count();
        $pendingCheckins = $reservationCounts['pending'] + $reservationCounts['payment_pending'];
        $availableRooms = max(0, $activeRoomsCount - $activeBookedSuites);
        $occupancyRate = $activeRoomsCount > 0 ? round(min(100, ($activeBookedSuites / $activeRoomsCount) * 100)) : null;

        // 7-day revenue data from confirmed/completed bookings.
        $weekStart = now()->copy()->subDays(6)->startOfDay();
        $weekEnd = now()->copy()->endOfDay();
        $weeklyRevenue = (float) Booking::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('booking_date', [$weekStart, $weekEnd])
            ->sum('total_amount');
        $dailyRevenue = Booking::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('booking_date', [$weekStart, $weekEnd])
            ->selectRaw('DATE(booking_date) as day, SUM(total_amount) as amount')
            ->groupByRaw('DATE(booking_date)')
            ->pluck('amount', 'day');
        $maxDailyRevenue = max(0, (float) $dailyRevenue->max());
        $weeklyChartDays = collect(range(6, 0))->map(function (int $daysAgo) use ($dailyRevenue, $maxDailyRevenue): array {
            $date = now()->copy()->subDays($daysAgo);
            $amount = (float) ($dailyRevenue[$date->toDateString()] ?? 0);

            return ['day' => $date->format('D'), 'amount' => $amount, 'height' => $maxDailyRevenue > 0 ? round(($amount / $maxDailyRevenue) * 100) : 0];
        })->all();

        // Recent Bookings Feed
        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['tourist', 'hotelRoomReservation.hotelRoom', 'tourismService'])
            ->orderByDesc('booking_date')
            ->take(6)
            ->get();

        $stats = [
            'roomTypeCount' => $provider->tourismServices()->count(),
            'totalRooms' => $rooms->count(),
            'activeRooms' => $rooms->where('status', 'active')->count(),
            'inactiveRooms' => $rooms->where('status', 'inactive')->count(),
            'reservations' => $reservationCounts,
            'upcomingStays' => $upcomingStays,
            'pendingAttention' => $reservationCounts['pending'],
            'reviewAverage' => (clone $reviewQuery)->avg('rating'),
            'reviewCount' => (clone $reviewQuery)->count(),
            'occupancyRate' => $occupancyRate,
            'activeBookedSuites' => $activeBookedSuites,
            'pendingCheckins' => $pendingCheckins,
            'availableRooms' => $availableRooms,
            'weeklyRevenue' => $weeklyRevenue,
            'weeklyChartDays' => $weeklyChartDays,
        ];

        return view('hotel.dashboard', compact('provider', 'stats', 'recentBookings'));
    }

    public function show(Request $request): View
    {
        return view('hotel.profile.show', ['provider' => $request->user()->serviceProvider->load('user')]);
    }

    public function edit(Request $request): View
    {
        return view('hotel.profile.edit', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(HotelProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('hotel.profile')->with('success', 'Hotel profile updated.');
    }
}
