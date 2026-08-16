<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\TourGuide;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTourGuideController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $availability = $request->string('availability')->trim()->value();

        $guides = TourGuide::query()
            ->with('user')
            ->where('verification_status', 'verified')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->when($search, fn ($query) => $query->where('expertise', 'like', "%{$search}%"))
            ->when(in_array($availability, ['available', 'unavailable'], true), fn ($query) => $query->where('availability_status', $availability))
            ->orderBy('expertise')
            ->get();

        $this->attachRatings($guides);

        return view('public.tour-guides.index', compact('guides', 'search', 'availability'));
    }

    public function show(TourGuide $guide): View
    {
        $this->ensurePubliclyEligible($guide);
        $guide->load('user');
        $this->attachRatings(collect([$guide]));

        return view('public.tour-guides.show', compact('guide'));
    }

    private function ensurePubliclyEligible(TourGuide $guide): void
    {
        $guide->loadMissing('user');

        abort_unless($guide->verification_status === 'verified' && $guide->user?->is_active, 404);
    }

    private function attachRatings($guides): void
    {
        $guides->each(function (TourGuide $guide): void {
            $query = Review::query()->whereHas('booking', fn ($query) => $query->where('guide_id', $guide->guide_id));
            $guide->average_rating = (clone $query)
                ->avg('rating');
            $guide->review_count = (clone $query)->count();
            $guide->reviews = $query->with('tourist')->latest('review_date')->limit(10)->get();
        });
    }
}
