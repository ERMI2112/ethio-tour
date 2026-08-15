<?php

namespace App\Http\Controllers;

use App\Models\TourismService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTransportationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $services = TourismService::query()
            ->with(['serviceProvider', 'destination'])
            ->whereHas('serviceProvider', fn ($query) => $query->where('provider_type', 'transportation_car_rental')->publiclyOperational())
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('service_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('service_name')->get();

        return view('public.transportation.index', compact('services', 'search'));
    }

    public function show(TourismService $tourismService): View
    {
        $tourismService->load(['serviceProvider', 'destination', 'transportationVehicles' => fn ($query) => $query->where('status', 'active')]);
        abort_unless($tourismService->serviceProvider?->provider_type === 'transportation_car_rental' && $tourismService->serviceProvider?->isOperational(), 404);

        return view('public.transportation.show', ['service' => $tourismService]);
    }
}
