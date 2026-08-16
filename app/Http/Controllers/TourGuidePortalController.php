<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTourGuideProfileRequest;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourGuidePortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load('user');
        $bookings = Booking::query()->where('guide_id', $guide->guide_id);

        $stats = [
            'pendingRequests' => (clone $bookings)->where('status', 'pending')->count(),
            'activeBookings' => (clone $bookings)->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->count(),
            'completedBookings' => (clone $bookings)->where('status', 'completed')->count(),
            'averageRating' => Review::query()->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id))->avg('rating'),
            'reviewCount' => Review::query()->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id))->count(),
        ];

        return view('tour-guide.dashboard', compact('guide', 'stats'));
    }

    public function showProfile(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load('user');

        return view('tour-guide.profile.show', compact('guide'));
    }

    public function editProfile(Request $request): View
    {
        return view('tour-guide.profile.edit', ['guide' => $request->user()->tourGuide]);
    }

    public function updateProfile(UpdateTourGuideProfileRequest $request): RedirectResponse
    {
        $request->user()->tourGuide->update($request->validated());

        return to_route('tour-guide.profile')->with('success', 'Tour guide profile updated.');
    }
}
