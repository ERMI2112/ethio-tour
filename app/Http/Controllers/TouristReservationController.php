<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TouristReservationController extends Controller
{
    public function index(Request $request): View
    {
        $tourist = $request->user()->tourist;

        if (! $tourist) {
            abort(403, 'Tourist profile not found.');
        }

        $status = $request->string('status')->trim()->value();

        $bookings = Booking::query()
            ->with([
                'tourismService.serviceProvider',
                'tourismService.hotelRoomType',
                'hotelRoomReservation.hotelRoom',
                'restaurantReservation.restaurantTable',
                'tourGuide',
                'tourGuideReservation',
                'transportationReservation.vehicle',
                'eventReservation.ticketType.event',
            ])
            ->where('tourist_id', $tourist->tourist_id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('booking_id')
            ->paginate(10)
            ->withQueryString();

        return view('tourist.reservations.index', compact('bookings', 'status'));
    }

    public function show(Request $request, Booking $booking): View
    {
        Gate::authorize('viewTourist', $booking);

        $booking->load([
            'tourismService.serviceProvider',
            'tourismService.destination',
            'tourismService.hotelRoomType',
            'hotelRoomReservation.hotelRoom',
            'restaurantReservation.restaurantTable',
            'tourGuide',
            'tourGuideReservation',
            'transportationReservation.vehicle',
            'eventReservation.ticketType.event',
            'payment',
        ]);

        return view('tourist.reservations.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('cancelTourist', $booking);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Reservation request cancelled.');
    }
}
