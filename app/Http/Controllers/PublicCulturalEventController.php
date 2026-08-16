<?php

namespace App\Http\Controllers;

use App\Models\CulturalEvent;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCulturalEventController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $events = CulturalEvent::query()->with(['destination', 'serviceProvider', 'ticketTypes'])->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational())->where('status', 'published')->whereDate('event_date', '>=', today())->when($search, fn ($q) => $q->where(function ($q) use ($search): void {
            $q->where('event_name', 'like', "%{$search}%")->orWhere('venue', 'like', "%{$search}%");
        }))->orderBy('event_date')->get();

        return view('public.events.index', compact('events', 'search'));
    }

    public function show(CulturalEvent $culturalEvent): View
    {
        $culturalEvent->load(['destination', 'serviceProvider', 'ticketTypes']);
        abort_unless($culturalEvent->status === 'published' && $culturalEvent->serviceProvider?->isOperational(), 404);
        $reviewQuery = Review::with('tourist')->whereHas('booking', fn ($query) => $query->where('service_id', $culturalEvent->service_id));
        $reviewAverage = (clone $reviewQuery)->avg('rating');
        $reviewCount = (clone $reviewQuery)->count();
        $reviews = $reviewQuery->latest('review_date')->limit(10)->get();

        return view('public.events.show', compact('culturalEvent', 'reviewAverage', 'reviewCount', 'reviews') + ['event' => $culturalEvent]);
    }
}
