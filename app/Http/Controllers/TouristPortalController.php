<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\ReviewEligibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TouristPortalController extends Controller
{
    public function dashboard(Request $request, ReviewEligibilityService $eligibility, PaymentService $payments): View
    {
        $tourist = $request->user()->tourist;
        abort_unless($tourist, 403, 'Tourist profile not found.');

        $bookings = $this->bookingQuery($tourist->tourist_id)->limit(30)->get();
        $upcomingBookings = $bookings->filter(fn (Booking $booking): bool => $this->isUpcoming($booking))->take(5)->values();
        $reviewOpportunities = $bookings->filter(fn (Booking $booking): bool => $eligibility->isEligible($booking))->take(5)->values();
        $attention = collect();

        foreach ($bookings as $booking) {
            if ($booking->status === 'pending') {
                $attention->push(['label' => 'Booking request awaiting provider review', 'booking' => $booking]);
            } elseif ($payments->canPay($booking) && $booking->payment?->status !== 'success') {
                $attention->push(['label' => $booking->status === 'payment_pending' ? 'Payment needs to be completed' : 'Accepted booking is ready for payment', 'booking' => $booking]);
            }
        }

        $recentNotifications = $request->user()->notifications()->latest('sent_date')->limit(5)->get();
        $unreadNotificationCount = $request->user()->notifications()->where('read_status', false)->count();
        $trips = $tourist->trips()->with('destinations')->withCount('items')->latest('created_at')->limit(3)->get();
        $reviews = $tourist->reviews()->with('booking')->latest('review_date')->limit(3)->get();

        return view('tourist.dashboard', compact(
            'tourist',
            'upcomingBookings',
            'attention',
            'recentNotifications',
            'unreadNotificationCount',
            'trips',
            'reviews',
            'reviewOpportunities',
            'payments',
        ));
    }

    public function reviews(Request $request, ReviewEligibilityService $eligibility): View
    {
        $tourist = $request->user()->tourist;
        abort_unless($tourist, 403, 'Tourist profile not found.');

        $bookings = $this->bookingQuery($tourist->tourist_id)->limit(50)->get();
        $reviewOpportunities = $bookings->filter(fn (Booking $booking): bool => $eligibility->isEligible($booking))->values();
        $reviews = $tourist->reviews()->with('booking')->latest('review_date')->paginate(10);

        return view('tourist.reviews.index', compact('reviews', 'reviewOpportunities'));
    }

    private function bookingQuery(int $touristId)
    {
        return Booking::query()
            ->with([
                'tourismService.serviceProvider',
                'tourismService.hotelRoomType',
                'hotelRoomReservation',
                'restaurantReservation',
                'tourGuide',
                'tourGuideReservation',
                'transportationReservation',
                'eventReservation.ticketType.event',
                'payment',
                'review',
            ])
            ->where('tourist_id', $touristId)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->orderByDesc('booking_id');
    }

    private function isUpcoming(Booking $booking): bool
    {
        if (in_array($booking->status, ['cancelled', 'rejected', 'completed'], true)) {
            return false;
        }

        $date = $booking->hotelRoomReservation?->check_in_date
            ?? $booking->tourGuideReservation?->start_date
            ?? $booking->restaurantReservation?->reservation_date
            ?? $booking->transportationReservation?->pickup_at
            ?? $booking->eventReservation?->ticketType?->event?->event_date;

        return $date === null || $date->isToday() || $date->isFuture();
    }
}
