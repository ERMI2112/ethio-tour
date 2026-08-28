<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTourGuideProfileRequest;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TourGuidePortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination', 'verificationDocuments']);
        $bookings = Booking::query()->where('guide_id', $guide->guide_id);

        $pendingRequests = (clone $bookings)->where('status', 'pending')->count();
        $activeBookings = (clone $bookings)->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->count();
        $completedBookings = (clone $bookings)->where('status', 'completed')->count();

        $totalEarnings = (float) ((clone $bookings)->where('status', 'completed')->sum('total_amount') ?? 0);
        $pendingEscrow = (float) ((clone $bookings)->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])->sum('total_amount') ?? 0);
        $monthlyEarnings = (float) ((clone $bookings)
            ->where('status', 'completed')
            ->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount') ?? 0);

        $escortedJourneys = Booking::query()
            ->where('guide_id', $guide->guide_id)
            ->with(['tourist', 'tourGuideReservation'])
            ->orderByDesc('booking_date')
            ->take(6)
            ->get();

        // Profile completeness is derived only from fields currently stored for the guide.
        $profileFields = [
            $guide->full_name,
            $guide->profile_image,
            $guide->bio ?: $guide->expertise,
            $guide->daily_rate,
            $guide->languages,
            $guide->phone_number,
        ];
        $profileCompleteness = (int) round(collect($profileFields)->filter(fn ($value): bool => filled($value))->count() / count($profileFields) * 100);

        $reviewQuery = Review::query()->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id));
        $averageRating = (clone $reviewQuery)->avg('rating');
        $reviewCount = (clone $reviewQuery)->count();

        $stats = [
            'pendingRequests' => $pendingRequests,
            'activeBookings' => $activeBookings,
            'completedBookings' => $completedBookings,
            'averageRating' => $averageRating,
            'reviewCount' => $reviewCount,
            'totalEarnings' => $totalEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'pendingEscrow' => $pendingEscrow,
            'lifetimePayout' => $totalEarnings,
            'profileCompleteness' => $profileCompleteness,
        ];

        return view('tour-guide.dashboard', compact('guide', 'stats', 'escortedJourneys'));
    }

    public function showProfile(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination']);

        return view('tour-guide.profile.show', compact('guide'));
    }

    public function editProfile(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['destination', 'verificationDocuments']);
        $destinations = Destination::orderBy('name')->get();

        return view('tour-guide.profile.edit', compact('guide', 'destinations'));
    }

    public function updateProfile(UpdateTourGuideProfileRequest $request): RedirectResponse
    {
        $guide = $request->user()->tourGuide;
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($guide->profile_image && Storage::disk('public')->exists($guide->profile_image)) {
                Storage::disk('public')->delete($guide->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('guides', 'public');
        }

        if (isset($data['languages']) && is_string($data['languages'])) {
            $data['languages'] = array_values(array_filter(array_map('trim', explode(',', $data['languages']))));
        }

        if (isset($data['specialties']) && is_string($data['specialties'])) {
            $data['specialties'] = array_values(array_filter(array_map('trim', explode(',', $data['specialties']))));
        }

        $guide->update($data);

        return to_route('tour-guide.profile')->with('success', 'Professional tour guide profile updated successfully.');
    }

    public function reviews(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination']);

        $reviews = Review::query()
            ->with(['tourist.user', 'booking'])
            ->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id))
            ->latest('review_date')
            ->paginate(10);

        $allReviews = Review::query()
            ->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id))
            ->get();

        $totalReviews = $allReviews->count();
        $averageRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : null;

        $ratingDistribution = [
            5 => $allReviews->where('rating', 5)->count(),
            4 => $allReviews->where('rating', 4)->count(),
            3 => $allReviews->where('rating', 3)->count(),
            2 => $allReviews->where('rating', 2)->count(),
            1 => $allReviews->where('rating', 1)->count(),
        ];

        return view('tour-guide.reviews', compact('guide', 'reviews', 'totalReviews', 'averageRating', 'ratingDistribution'));
    }

    public function earnings(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination']);

        $completedBookings = Booking::query()
            ->with(['tourist.user', 'payment', 'tourGuideReservation'])
            ->where('guide_id', $guide->guide_id)
            ->where('status', 'completed')
            ->latest('booking_date')
            ->get();

        $pendingBookings = Booking::query()
            ->with(['tourist.user', 'payment', 'tourGuideReservation'])
            ->where('guide_id', $guide->guide_id)
            ->whereIn('status', ['accepted', 'payment_pending', 'confirmed'])
            ->latest('booking_date')
            ->get();

        $lifetimeEarnings = $completedBookings->sum('total_amount');
        $pendingEarnings = $pendingBookings->sum('total_amount');
        $completedCount = $completedBookings->count();
        $averagePerTour = $completedCount > 0 ? round($lifetimeEarnings / $completedCount, 2) : 0;

        return view('tour-guide.earnings', compact(
            'guide',
            'completedBookings',
            'pendingBookings',
            'lifetimeEarnings',
            'pendingEarnings',
            'completedCount',
            'averagePerTour'
        ));
    }

    public function tours(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination']);

        return view('tour-guide.tours', compact('guide'));
    }

    public function settings(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        $guide->load(['user', 'destination']);

        return view('tour-guide.settings', compact('guide'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $guide = $request->user()->tourGuide;

        $validated = $request->validate([
            'daily_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'availability_status' => ['required', 'in:available,unavailable'],
        ]);

        $guide->update($validated);

        return to_route('tour-guide.settings')->with('success', 'Tour guide operational settings updated.');
    }
}
