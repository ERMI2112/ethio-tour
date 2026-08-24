<?php

namespace App\Http\Controllers;

use App\Models\Destination;
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
        $destinationId = $request->string('destination')->trim()->value();
        $language = $request->string('language')->trim()->value();
        $sort = $request->string('sort', 'recommended')->trim()->value();

        $guides = TourGuide::query()
            ->with(['user', 'destination'])
            ->where('verification_status', 'verified')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('expertise', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('languages', 'like', "%{$search}%")
                        ->orWhere('specialties', 'like', "%{$search}%")
                        ->orWhereHas('destination', fn ($dq) => $dq->where('name', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', "%{$search}%"));
                });
            })
            ->when($destinationId !== '', function ($query) use ($destinationId) {
                if (is_numeric($destinationId)) {
                    $query->where('primary_destination_id', $destinationId);
                } else {
                    $query->whereHas('destination', fn ($dq) => $dq->where('slug', $destinationId)->orWhere('name', 'like', "%{$destinationId}%"));
                }
            })
            ->when($language !== '', function ($query) use ($language) {
                $query->where(function ($q) use ($language) {
                    $q->where('languages', 'like', "%{$language}%");
                });
            })
            ->when(in_array($availability, ['available', 'unavailable'], true), fn ($query) => $query->where('availability_status', $availability))
            ->when($sort === 'rate_low', fn ($q) => $q->orderBy('daily_rate', 'asc'))
            ->when($sort === 'rate_high', fn ($q) => $q->orderBy('daily_rate', 'desc'))
            ->when($sort === 'experience', fn ($q) => $q->orderBy('years_of_experience', 'desc'))
            ->when($sort === 'recommended', fn ($q) => $q->orderByRaw('CASE WHEN availability_status = "available" THEN 0 ELSE 1 END')->orderByDesc('years_of_experience')->orderBy('full_name'))
            ->get();

        $this->attachRatings($guides);

        $destinations = Destination::orderBy('name')->get();

        return view('public.tour-guides.index', compact(
            'guides',
            'search',
            'availability',
            'destinationId',
            'language',
            'sort',
            'destinations'
        ));
    }

    public function show(TourGuide $guide): View
    {
        $this->ensurePubliclyEligible($guide);
        $guide->load(['user', 'destination', 'tourPackages' => fn ($q) => $q->active()->with('destination')->latest()]);
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
            $guide->average_rating = (clone $query)->avg('rating');
            $guide->review_count = (clone $query)->count();
            $guide->reviews = $query->with('tourist')->latest('review_date')->limit(10)->get();
        });
    }
}
