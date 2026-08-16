<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Policies\ReviewPolicy;
use Illuminate\Http\RedirectResponse;

class TouristReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        $booking->loadMissing(['review', 'tourGuideReservation', 'hotelRoomReservation', 'restaurantReservation', 'transportationReservation', 'eventReservation.ticketType.event']);

        if ($booking->review) {
            return back()->with('error', 'This booking already has a review.');
        }

        abort_unless(app(ReviewPolicy::class)->create($request->user(), $booking), 403);

        $booking->review()->create([
            'tourist_id' => $request->user()->tourist->tourist_id,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'review_date' => today(),
        ]);

        return back()->with('success', 'Thank you. Your review was submitted.');
    }
}
