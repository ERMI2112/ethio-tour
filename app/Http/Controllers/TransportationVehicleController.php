<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransportationVehicleRequest;
use App\Models\TransportationVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportationVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = $request->user()->serviceProvider->transportationVehicles()->with('tourismService')->orderBy('vehicle_identifier')->get();

        return view('transportation.vehicles.index', compact('vehicles'));
    }

    public function create(Request $request): View
    {
        return view('transportation.vehicles.create', ['services' => $request->user()->serviceProvider->tourismServices()->orderBy('service_name')->get()]);
    }

    public function store(TransportationVehicleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['provider_id'] = $request->user()->serviceProvider->provider_id;
        TransportationVehicle::create($data);

        return to_route('transportation.vehicles.index')->with('success', 'Vehicle added to inventory.');
    }

    public function edit(Request $request, TransportationVehicle $transportationVehicle): View
    {
        $this->ensureOwned($request, $transportationVehicle);

        return view('transportation.vehicles.edit', ['vehicle' => $transportationVehicle, 'services' => $request->user()->serviceProvider->tourismServices()->orderBy('service_name')->get()]);
    }

    public function update(TransportationVehicleRequest $request, TransportationVehicle $transportationVehicle): RedirectResponse
    {
        $this->ensureOwned($request, $transportationVehicle);
        $transportationVehicle->update($request->validated());

        return to_route('transportation.vehicles.index')->with('success', 'Vehicle updated.');
    }

    public function destroy(Request $request, TransportationVehicle $transportationVehicle): RedirectResponse
    {
        $this->ensureOwned($request, $transportationVehicle);
        if ($transportationVehicle->reservations()->exists()) {
            return back()->with('error', 'This vehicle has reservation history. Mark it inactive instead.');
        }
        $transportationVehicle->delete();

        return to_route('transportation.vehicles.index')->with('success', 'Vehicle removed.');
    }

    private function ensureOwned(Request $request, TransportationVehicle $vehicle): void
    {
        abort_unless((int) $vehicle->provider_id === (int) $request->user()->serviceProvider->provider_id, 403);
    }
}
