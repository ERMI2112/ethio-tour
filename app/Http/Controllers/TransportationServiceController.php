<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransportationServiceRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\TourismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportationServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = $request->user()->serviceProvider->tourismServices()->with(['category', 'destination'])->orderBy('service_name')->get();

        return view('transportation.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('transportation.services.create', $this->formData());
    }

    public function store(TransportationServiceRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->tourismServices()->create($request->validated());

        return to_route('transportation.services.index')->with('success', 'Transportation service created.');
    }

    public function edit(Request $request, TourismService $tourismService): View
    {
        $this->ensureOwned($request, $tourismService);

        return view('transportation.services.edit', array_merge(['service' => $tourismService], $this->formData()));
    }

    public function update(TransportationServiceRequest $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);
        $tourismService->update($request->validated());

        return to_route('transportation.services.index')->with('success', 'Transportation service updated.');
    }

    public function destroy(Request $request, TourismService $tourismService): RedirectResponse
    {
        $this->ensureOwned($request, $tourismService);
        if ($tourismService->bookings()->exists() || $tourismService->transportationVehicles()->exists()) {
            return back()->with('error', 'This service has booking or vehicle history and cannot be removed.');
        }
        $tourismService->delete();

        return to_route('transportation.services.index')->with('success', 'Transportation service removed.');
    }

    private function formData(): array
    {
        return ['categories' => Category::orderBy('category_name')->get(), 'destinations' => Destination::orderBy('name')->get()];
    }

    private function ensureOwned(Request $request, TourismService $service): void
    {
        abort_unless((int) $service->provider_id === (int) $request->user()->serviceProvider->provider_id, 403);
    }
}
