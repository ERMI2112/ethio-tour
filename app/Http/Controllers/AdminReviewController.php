<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::with(['tourist', 'booking.tourGuide', 'booking.tourismService'])->latest('review_date')->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(Request $request, Review $review, AuditService $audit): RedirectResponse
    {
        Gate::authorize('delete', $review);
        $reviewId = $review->review_id;
        $audit->record($request->user(), 'review_removed', Review::class, $reviewId, ['reason' => $request->string('reason')->trim()->value() ?: null]);
        $review->delete();

        return back()->with('success', 'Review removed from public display.');
    }
}
