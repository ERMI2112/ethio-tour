<?php

namespace App\Http\Controllers;

use App\Exceptions\TourGuideAvailabilityException;
use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use App\Services\NotificationService;
use App\Services\TourGuideAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TourGuideBookingRequestController extends Controller
{
    public function availability(Request $request): View
    {
        $guide = $request->user()->tourGuide;

        $blockingReservations = TourGuideReservation::query()
            ->with(['booking.tourist'])
            ->whereHas('booking', fn ($query) => $query
                ->where('guide_id', $guide->guide_id)
                ->whereIn('status', TourGuideAvailabilityService::INVENTORY_RESERVING_STATUSES))
            ->orderBy('start_date')
            ->get();

        $pendingRequests = Booking::query()
            ->with(['tourist', 'tourGuideReservation'])
            ->where('guide_id', $guide->guide_id)
            ->where('status', 'pending')
            ->whereHas('tourGuideReservation')
            ->orderBy('booking_date')
            ->get();

        return view('tour-guide.availability', compact('guide', 'blockingReservations', 'pendingRequests'));
    }

    public function index(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $status = $request->string('status')->trim()->value();

        $bookings = Booking::query()
            ->with(['tourist', 'tourGuideReservation'])
            ->where('guide_id', $guide->guide_id)
            ->whereHas('tourGuideReservation')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('booking_date')
            ->paginate(10)
            ->withQueryString();

        return view('tour-guide.requests.index', compact('bookings', 'status'));
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('manageTourGuide', $booking);

        $booking->load(['tourist', 'tourGuideReservation', 'payment']);

        return view('tour-guide.requests.show', compact('booking'));
    }

    public function accept(Booking $booking, TourGuideAvailabilityService $availabilityService, Request $request, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('manageTourGuide', $booking);

        try {
            DB::transaction(function () use ($booking, $availabilityService, $request): void {
                $lockedGuide = TourGuide::query()->lockForUpdate()->findOrFail($request->user()->tourGuide->guide_id);
                $lockedBooking = Booking::query()
                    ->with('tourGuideReservation')
                    ->lockForUpdate()
                    ->findOrFail($booking->booking_id);

                if ((int) $lockedBooking->guide_id !== (int) $lockedGuide->guide_id) {
                    throw new TourGuideAvailabilityException('This booking does not belong to the authenticated guide.');
                }

                if ($lockedGuide->verification_status !== 'verified') {
                    throw new TourGuideAvailabilityException('Only verified tour guides can accept booking requests.');
                }

                if ($lockedBooking->status !== 'pending') {
                    throw new TourGuideAvailabilityException('Only pending booking requests can be accepted.');
                }

                $reservation = $lockedBooking->tourGuideReservation;

                if (! $reservation) {
                    throw new TourGuideAvailabilityException('Tour date and party-size details are missing for this request.');
                }

                $availabilityService->assertGuideAvailable($lockedGuide, $reservation);
                $lockedBooking->update(['status' => 'accepted']);
            }, attempts: 3);
        } catch (TourGuideAvailabilityException|ValidationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $notifications->createForUser($booking->fresh('tourist')->tourist?->user, 'booking_accepted', 'Tour guide request accepted', 'Your tour guide booking request was accepted.');

        return back()->with('success', 'Booking request accepted.');
    }

    public function reject(Booking $booking, Request $request, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('manageTourGuide', $booking);

        try {
            DB::transaction(function () use ($booking, $request): void {
                $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->booking_id);

                if ((int) $lockedBooking->guide_id !== (int) $request->user()->tourGuide->guide_id) {
                    throw new TourGuideAvailabilityException('This booking does not belong to the authenticated guide.');
                }

                if ($lockedBooking->status !== 'pending') {
                    throw new TourGuideAvailabilityException('Only pending booking requests can be rejected.');
                }

                $lockedBooking->update(['status' => 'rejected']);
            }, attempts: 3);
        } catch (TourGuideAvailabilityException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $notifications->createForUser($booking->fresh('tourist')->tourist?->user, 'booking_rejected', 'Tour guide request rejected', 'Your tour guide booking request was rejected.');

        return back()->with('success', 'Booking request rejected.');
    }
}
