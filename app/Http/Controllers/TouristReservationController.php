<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingAmountService;
use App\Services\NotificationService;
use App\Services\ReviewEligibilityService;
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
                'payment',
            ])
            ->where('tourist_id', $tourist->tourist_id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('booking_id')
            ->paginate(10)
            ->withQueryString();

        return view('tourist.reservations.index', compact('bookings', 'status'));
    }

    public function show(Request $request, Booking $booking, ReviewEligibilityService $eligibility, BookingAmountService $amountService): View
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
            'review',
        ]);
        $reviewEligible = $eligibility->isEligible($booking);
        $transportationRentalDays = $booking->transportationReservation
            ? $amountService->transportationRentalDays(
                $booking->transportationReservation->pickup_at,
                $booking->transportationReservation->dropoff_at,
            )
            : null;
        $transportationDailyRate = $transportationRentalDays && $booking->total_amount !== null
            ? number_format((float) $booking->total_amount / $transportationRentalDays, 2, '.', '')
            : null;

        return view('tourist.reservations.show', compact('booking', 'reviewEligible', 'transportationRentalDays', 'transportationDailyRate'));
    }

    public function cancel(Request $request, Booking $booking, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('cancelTourist', $booking);

        $booking->load(['tourismService.serviceProvider.user', 'tourGuide.user']);
        $booking->update(['status' => 'cancelled']);
        $recipient = $booking->tourismService?->serviceProvider?->user ?? $booking->tourGuide?->user;
        $notifications->createForUserAndAdministrators($recipient, 'booking_cancelled', 'Booking cancelled', 'A tourist cancelled booking #'.$booking->booking_id.'.');

        return back()->with('success', 'Reservation request cancelled.');
    }
}
