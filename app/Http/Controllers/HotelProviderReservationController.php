<?php

namespace App\Http\Controllers;

use App\Exceptions\HotelAvailabilityException;
use App\Models\Booking;
use App\Services\HotelAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HotelProviderReservationController extends Controller
{
    public function index(Request $request): View
    {
        $provider = $request->user()->serviceProvider;

        if (! $provider || $provider->provider_type !== 'hotel') {
            abort(403, 'Hotel provider profile required.');
        }

        $serviceIds = $provider->tourismServices()->pluck('service_id');
        $status = $request->string('status')->trim()->value();

        $bookings = Booking::query()
            ->with([
                'tourist',
                'tourismService.hotelRoomType',
                'hotelRoomReservation.hotelRoom',
            ])
            ->whereIn('service_id', $serviceIds)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('booking_id')
            ->paginate(10)
            ->withQueryString();

        return view('hotel.reservations.index', compact('bookings', 'status'));
    }

    public function show(Request $request, Booking $booking): View
    {
        Gate::authorize('manageHotelProvider', $booking);

        $booking->load([
            'tourist',
            'tourismService.hotelRoomType',
            'hotelRoomReservation.hotelRoom',
            'payment',
        ]);

        return view('hotel.reservations.show', compact('booking'));
    }

    public function accept(Request $request, Booking $booking, HotelAvailabilityService $availabilityService): RedirectResponse
    {
        Gate::authorize('manageHotelProvider', $booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be accepted.');
        }

        $reservation = $booking->hotelRoomReservation;

        if (! $reservation) {
            return back()->with('error', 'Hotel reservation details missing.');
        }

        try {
            DB::transaction(function () use ($booking, $reservation, $availabilityService): void {
                $booking->update(['status' => 'accepted']);
                $allocatedRoom = $availabilityService->allocateAvailableRoom($reservation);
                $booking->update(['status' => 'payment_pending']);
            });
        } catch (HotelAvailabilityException $e) {
            return back()->with('error', 'Acceptance failed: '.$e->getMessage());
        }

        return back()->with('success', 'Reservation accepted successfully. Physical room allocated and status updated to payment_pending.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('manageHotelProvider', $booking);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be rejected.');
        }

        DB::transaction(function () use ($booking): void {
            $booking->update(['status' => 'rejected']);
        });

        return back()->with('success', 'Reservation request rejected.');
    }
}
