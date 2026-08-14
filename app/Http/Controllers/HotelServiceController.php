<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelServiceRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\TourismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HotelServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = $request->user()->serviceProvider->tourismServices()
            ->with(['category', 'destination', 'hotelRoomType.hotelRooms'])
            ->orderBy('service_name')
            ->get();

        return view('hotel.services.index', compact('services'));
    }

    public function create(Request $request): View
    {
        return view('hotel.services.create', $this->formData($request));
    }

    public function store(HotelServiceRequest $request): RedirectResponse
    {
        $provider = $request->user()->serviceProvider;
        $data = $request->validated();

        DB::transaction(function () use ($data, $provider): void {
            $service = $provider->tourismServices()->create([
                'category_id' => $data['category_id'],
                'destination_id' => $data['destination_id'],
                'service_name' => $data['service_name'],
                'price' => $data['price'],
                'description' => $data['description'],
            ]);

            $service->hotelRoomType()->create(['capacity' => $data['capacity'], 'amenities' => $data['amenities']]);
        });

        return to_route('hotel.services.index')->with('success', 'Room-type service created.');
    }

    public function edit(Request $request, TourismService $tourismService): View
    {
        $this->ensureOwned($request, $tourismService);
        $tourismService->load('hotelRoomType');

        return view('hotel.services.edit', array_merge(['service' => $tourismService], $this->formData($request)));
    }

    public function update(HotelServiceRequest $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);
        $data = $request->validated();

        DB::transaction(function () use ($data, $tourismService): void {
            $tourismService->update([
                'category_id' => $data['category_id'],
                'destination_id' => $data['destination_id'],
                'service_name' => $data['service_name'],
                'price' => $data['price'],
                'description' => $data['description'],
            ]);

            $tourismService->hotelRoomType()->updateOrCreate(
                ['service_id' => $tourismService->service_id],
                ['capacity' => $data['capacity'], 'amenities' => $data['amenities']]
            );
        });

        return to_route('hotel.services.index')->with('success', 'Room-type service updated.');
    }

    public function destroy(Request $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);
        $roomType = $tourismService->hotelRoomType()->with('hotelRooms.hotelRoomReservations')->first();

        if ($tourismService->bookings()->exists() || $roomType?->hotelRooms->isNotEmpty()) {
            return back()->with('error', 'This service cannot be removed because it has inventory or booking history. Deactivate its rooms instead.');
        }

        DB::transaction(function () use ($tourismService, $roomType): void {
            $roomType?->delete();
            $tourismService->delete();
        });

        return to_route('hotel.services.index')->with('success', 'Room-type service removed.');
    }

    private function formData(Request $request): array
    {
        return [
            'categories' => Category::orderBy('category_name')->get(),
            'destinations' => Destination::orderBy('name')->get(),
        ];
    }

    private function ensureOwned(Request $request, TourismService $service): void
    {
        abort_unless((int) $service->provider_id === (int) $request->user()->serviceProvider->provider_id, 403);
    }
}
