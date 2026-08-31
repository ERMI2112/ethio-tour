<?php

namespace App\Http\Controllers;

use App\Exceptions\RestaurantAvailabilityException;
use App\Models\Booking;
use App\Services\NotificationService;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RestaurantReservationController extends Controller
{
    public function index(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $status = $request->string('status')->trim()->value();

        $bookings = Booking::query()
            ->with(['tourist', 'tourismService', 'restaurantReservation.restaurantTable'])
            ->whereIn('service_id', $serviceIds)
            ->whereHas('restaurantReservation')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('booking_id')
            ->paginate(10)
            ->withQueryString();

        return view('restaurant.reservations.index', compact('bookings', 'status'));
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('manageRestaurantProvider', $booking);

        $booking->load(['tourist', 'tourismService.serviceProvider', 'restaurantReservation.restaurantTable', 'payment']);

        abort_unless($booking->restaurantReservation !== null, 404);

        return view('restaurant.reservations.show', compact('booking'));
    }

    public function accept(Booking $booking, RestaurantAvailabilityService $availabilityService, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('manageRestaurantProvider', $booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be accepted.');
        }

        $reservation = $booking->restaurantReservation;

        if (! $reservation) {
            return back()->with('error', 'Restaurant reservation details are missing.');
        }

        try {
            DB::transaction(function () use ($booking, $reservation, $availabilityService): void {
                $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
                $lockedBooking->update(['status' => 'accepted']);
                $availabilityService->allocateAvailableTable($reservation->fresh());
            });
        } catch (RestaurantAvailabilityException $exception) {
            return back()->with('error', 'Acceptance failed: '.$exception->getMessage());
        }

        $notifications->createForUserAndAdministrators(
            $booking->fresh('tourist')->tourist?->user,
            'reservation_accepted',
            'Restaurant reservation accepted',
            'Your restaurant reservation was accepted and a table was allocated.',
            null,
            route('tourist.reservations.show', $booking),
        );

        return back()->with('success', 'Reservation accepted and a table was allocated.');
    }

    public function reject(Booking $booking, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('manageRestaurantProvider', $booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be rejected.');
        }

        $booking->update(['status' => 'rejected']);
        $notifications->createForUserAndAdministrators(
            $booking->fresh('tourist')->tourist?->user,
            'reservation_rejected',
            'Restaurant reservation rejected',
            'Your restaurant reservation request was rejected.',
            null,
            route('tourist.reservations.show', $booking),
        );

        return back()->with('success', 'Reservation request rejected.');
    }
}
