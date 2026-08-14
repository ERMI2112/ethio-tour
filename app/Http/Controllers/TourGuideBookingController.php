<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTourGuideBookingRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Models\TourGuide;
use App\Services\TourGuideAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TourGuideBookingController extends Controller
{
    public function create(TourGuide $guide): View
    {
        $this->ensureBookable($guide);
        $guide->load('user');
        $guide->average_rating = Review::query()->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id))->avg('rating');

        return view('public.tour-guides.book', compact('guide'));
    }

    public function store(
        StoreTourGuideBookingRequest $request,
        TourGuide $guide,
        TourGuideAvailabilityService $availabilityService,
    ): RedirectResponse {
        $this->ensureBookable($guide);
        $validated = $request->validated();
        $tourist = $request->user()->tourist;

        if (! $availabilityService->isGuideAvailable($guide, $validated['start_date'], $validated['end_date'])) {
            return back()->withInput()->with('error', 'The guide is not available for the selected date range.');
        }

        if ($availabilityService->hasOverlappingTouristRequest($tourist->tourist_id, $guide, $validated['start_date'], $validated['end_date'])) {
            return back()->withInput()->with('error', 'You already have an active overlapping request for this guide.');
        }

        $booking = DB::transaction(function () use ($tourist, $guide, $validated): Booking {
            $booking = Booking::create([
                'tourist_id' => $tourist->tourist_id,
                'service_id' => null,
                'guide_id' => $guide->guide_id,
                'status' => 'pending',
                'booking_date' => now(),
            ]);

            $booking->tourGuideReservation()->create([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'number_of_tourists' => $validated['number_of_tourists'],
            ]);

            return $booking;
        });

        return to_route('tourist.reservations.show', $booking)->with('success', 'Guide booking request submitted successfully.');
    }

    private function ensureBookable(TourGuide $guide): void
    {
        $guide->loadMissing('user');

        abort_unless($guide->verification_status === 'verified' && $guide->user?->is_active, 404);
    }
}
