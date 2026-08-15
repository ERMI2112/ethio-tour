<?php

namespace App\Http\Controllers;

use App\Exceptions\TransportationAvailabilityException;
use App\Http\Requests\CheckTransportationAvailabilityRequest;
use App\Http\Requests\StoreTransportationReservationRequest;
use App\Models\Booking;
use App\Models\TourismService;
use App\Services\TransportationAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportationTouristReservationController extends Controller
{
    public function checkAvailability(CheckTransportationAvailabilityRequest $request, TourismService $tourismService, TransportationAvailabilityService $availabilityService): RedirectResponse
    {
        $this->ensureTransportationService($tourismService);
        $data = $request->validated();
        try {
            $vehicles = $availabilityService->findAvailableVehicles($tourismService, $data['pickup_at'], $data['dropoff_at'], (int) $data['passenger_count']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (TransportationAvailabilityException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        if ($vehicles->isEmpty()) {
            return back()->with('error', 'No vehicle is available for the selected rental window.')->withInput();
        }

        return back()->with('success', "Availability confirmed! {$vehicles->count()} vehicle(s) match your request.")->withInput();
    }

    public function store(StoreTransportationReservationRequest $request, TourismService $tourismService, TransportationAvailabilityService $availabilityService): RedirectResponse
    {
        $this->ensureTransportationService($tourismService);
        $data = $request->validated();
        $tourist = $request->user()->tourist;

        try {
            $vehicles = $availabilityService->findAvailableVehicles($tourismService, $data['pickup_at'], $data['dropoff_at'], (int) $data['passenger_count']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }
        if ($vehicles->isEmpty()) {
            return back()->with('error', 'No vehicle is available for the selected rental window.')->withInput();
        }

        $booking = DB::transaction(function () use ($tourist, $tourismService, $data): Booking {
            $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $tourismService->service_id, 'guide_id' => null, 'status' => 'pending', 'booking_date' => now()]);
            $booking->transportationReservation()->create([
                'pickup_location' => $data['pickup_location'], 'dropoff_location' => $data['dropoff_location'],
                'pickup_at' => $data['pickup_at'], 'dropoff_at' => $data['dropoff_at'], 'passenger_count' => $data['passenger_count'],
            ]);

            return $booking;
        });

        return to_route('tourist.reservations.show', $booking)->with('success', 'Transportation request submitted successfully. Pending provider review.');
    }

    private function ensureTransportationService(TourismService $service): void
    {
        $service->loadMissing('serviceProvider');
        abort_unless($service->serviceProvider?->provider_type === 'transportation_car_rental' && $service->serviceProvider?->isOperational(), 404);
    }
}
