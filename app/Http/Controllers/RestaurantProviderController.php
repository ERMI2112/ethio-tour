<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantProfileRequest;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantProviderController extends Controller
{
    public function dashboard(Request $request): View
    {
        $provider = $request->user()->serviceProvider->load(['user', 'tourismServices.category', 'tourismServices.destination']);
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $reviewQuery = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds));

        $tableCount = $provider->restaurantTables()->count();
        $activeTables = $provider->restaurantTables()->where('status', 'active')->count();
        $totalSeatsCapacity = (int) $provider->restaurantTables()->where('status', 'active')->sum('capacity');

        $pendingReservations = Booking::whereIn('service_id', $serviceIds)->where('status', 'pending')->count();
        $upcomingReservations = Booking::whereIn('service_id', $serviceIds)->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->count();

        $today = now()->toDateString();
        $todayReservations = Booking::whereIn('service_id', $serviceIds)
            ->whereHas('restaurantReservation', function ($q) use ($today) {
                $q->whereDate('reservation_date', $today);
            })
            ->with(['tourist', 'restaurantReservation.restaurantTable', 'tourismService'])
            ->orderBy('booking_date', 'desc')
            ->get();

        $todayBookedTables = $todayReservations->whereIn('status', ['accepted', 'payment_pending', 'confirmed', 'completed'])->count();
        $todayBookedSeats = $todayReservations->whereIn('status', ['accepted', 'payment_pending', 'confirmed', 'completed'])->sum(fn ($b) => $b->restaurantReservation?->guest_count ?? 0);
        $peakCapacityPercentage = $tableCount > 0 ? min(100, round(($todayBookedTables / $tableCount) * 100)) : null;

        // Monthly dining revenue
        $monthlyRevenue = (float) Booking::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        // Recent / Today's reservations feed
        $recentReservations = Booking::whereIn('service_id', $serviceIds)
            ->with(['tourist', 'restaurantReservation.restaurantTable', 'tourismService'])
            ->orderByDesc('booking_date')
            ->take(6)
            ->get();

        // Top Menu Dishes Performance Index
        $dishes = $provider->tourismServices()
            ->withCount('bookings')
            ->with('category')
            ->orderByDesc('bookings_count')
            ->get();

        $maxOrders = max(0, (int) $dishes->max('bookings_count'));
        $dishPerformance = $dishes->map(function ($dish) use ($maxOrders) {
            $orders = (int) $dish->bookings_count;
            $pct = $maxOrders > 0 ? round(($orders / $maxOrders) * 100) : 0;

            return [
                'service_id' => $dish->service_id,
                'name' => $dish->service_name,
                'orders' => $orders,
                'rating' => null,
                'price' => (float) $dish->price,
                'category' => $dish->category?->category_name ?? 'Gastronomy',
                'percentage' => min(100, $pct),
            ];
        });

        $stats = [
            'serviceCount' => $serviceIds->count(),
            'tableCount' => $tableCount,
            'activeTables' => $activeTables,
            'totalSeatsCapacity' => $totalSeatsCapacity,
            'todayBookedTables' => $todayBookedTables,
            'todayBookedSeats' => $todayBookedSeats,
            'peakCapacityPercentage' => $peakCapacityPercentage,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingReservations' => $pendingReservations,
            'upcomingReservations' => $upcomingReservations,
            'reviewAverage' => (clone $reviewQuery)->avg('rating'),
            'reviewCount' => (clone $reviewQuery)->count(),
        ];

        return view('restaurant.dashboard', compact('provider', 'stats', 'dishPerformance', 'recentReservations'));
    }

    public function reviews(Request $request): View
    {
        $provider = $request->user()->serviceProvider->load(['user', 'tourismServices.category']);
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $reviews = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds))
            ->with(['tourist', 'booking.tourismService'])
            ->orderByDesc('review_date')
            ->paginate(15);

        $averageRating = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds))->avg('rating');
        $totalReviews = Review::whereHas('booking', fn ($query) => $query->whereIn('service_id', $serviceIds))->count();

        return view('restaurant.reviews.index', compact('provider', 'reviews', 'averageRating', 'totalReviews'));
    }

    public function revenue(Request $request): View
    {
        $provider = $request->user()->serviceProvider->load(['user', 'tourismServices']);
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $bookings = Booking::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['confirmed', 'completed'])
            ->with(['tourist', 'tourismService', 'payment'])
            ->orderByDesc('booking_date')
            ->paginate(15);

        $totalRevenue = Booking::whereIn('service_id', $serviceIds)->whereIn('status', ['confirmed', 'completed'])->sum('total_amount');
        $completedCount = Booking::whereIn('service_id', $serviceIds)->whereIn('status', ['confirmed', 'completed'])->count();

        return view('restaurant.revenue.index', compact('provider', 'bookings', 'totalRevenue', 'completedCount'));
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
