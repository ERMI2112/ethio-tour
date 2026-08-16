<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\TourismService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTourismServiceController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category');
        $destinationId = $request->integer('destination');
        $search = $request->string('q')->trim()->value();

        $services = TourismService::query()
            ->with(['category', 'destination', 'serviceProvider'])
            ->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($destinationId, fn ($query) => $query->where('destination_id', $destinationId))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('service_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('serviceProvider', fn ($query) => $query->where('business_name', 'like', "%{$search}%"));
            }))
            ->orderBy('service_name')
            ->get();

        $categories = Category::orderBy('category_name')->get();
        $destinations = Destination::orderBy('name')->get();

        return view('public.tourism-services.index', compact('services', 'categories', 'destinations', 'categoryId', 'destinationId', 'search'));
    }

    public function show(TourismService $tourismService): View
    {
        abort_unless($tourismService->serviceProvider?->isOperational(), 404);
        $tourismService->load(['category', 'destination', 'serviceProvider', 'hotelRoomType']);
        $isRestaurant = $tourismService->serviceProvider?->provider_type === 'restaurant';
        $isRestaurantReservationOffering = $isRestaurant && $tourismService->isRestaurantReservationOffering();
        $reviewQuery = Review::with('tourist')->whereHas('booking', fn ($query) => $query->where('service_id', $tourismService->service_id));
        $reviewAverage = (clone $reviewQuery)->avg('rating');
        $reviewCount = (clone $reviewQuery)->count();
        $reviews = $reviewQuery->latest('review_date')->limit(10)->get();

        return view('public.tourism-services.show', compact('tourismService', 'isRestaurant', 'isRestaurantReservationOffering', 'reviewAverage', 'reviewCount', 'reviews'));
    }
}
