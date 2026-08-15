<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantServiceRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\TourismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = $request->user()->serviceProvider->tourismServices()
            ->with(['category', 'destination'])
            ->orderBy('service_name')
            ->get();

        return view('restaurant.services.index', compact('services'));
    }

    public function create(Request $request): View
    {
        return view('restaurant.services.create', $this->formData());
    }

    public function store(RestaurantServiceRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->tourismServices()->create($request->validated());

        return to_route('restaurant.services.index')->with('success', 'Restaurant service created.');
    }

    public function edit(Request $request, TourismService $tourismService): View
    {
        $this->ensureOwned($request, $tourismService);

        return view('restaurant.services.edit', array_merge(['service' => $tourismService], $this->formData()));
    }

    public function update(RestaurantServiceRequest $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);
        $tourismService->update($request->validated());

        return to_route('restaurant.services.index')->with('success', 'Restaurant service updated.');
    }

    public function destroy(Request $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);

        if ($tourismService->bookings()->exists()) {
            return back()->with('error', 'This service has booking history and cannot be removed.');
        }

        $tourismService->delete();

        return to_route('restaurant.services.index')->with('success', 'Restaurant service removed.');
    }

    private function formData(): array
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
