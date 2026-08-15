<?php

namespace App\Http\Controllers;

use App\Exceptions\TransportationAvailabilityException;
use App\Models\Booking;
use App\Services\TransportationAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransportationReservationController extends Controller
{
    public function index(Request $request): View
    {
        $serviceIds = $request->user()->serviceProvider->tourismServices()->pluck('service_id');
        $bookings = Booking::with(['tourist', 'tourismService', 'transportationReservation.vehicle'])
            ->whereIn('service_id', $serviceIds)->whereHas('transportationReservation')->orderByDesc('booking_id')->paginate(10);

        return view('transportation.reservations.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('manageTransportationProvider', $booking);
        $booking->load(['tourist', 'tourismService.serviceProvider', 'transportationReservation.vehicle', 'payment']);
        abort_unless($booking->transportationReservation !== null, 404);

        return view('transportation.reservations.show', compact('booking'));
    }

    public function accept(Booking $booking, TransportationAvailabilityService $availabilityService): RedirectResponse
    {
        Gate::authorize('manageTransportationProvider', $booking);
        if ($booking->status !== 'pending' || ! $booking->transportationReservation) {
            return back()->with('error', 'Only pending transportation requests can be accepted.');
        }
        try {
            DB::transaction(function () use ($booking, $availabilityService): void {
                $locked = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
                $locked->update(['status' => 'accepted']);
                $availabilityService->allocateAvailableVehicle($locked->transportationReservation()->firstOrFail()->fresh());
            });
        } catch (TransportationAvailabilityException $exception) {
            return back()->with('error', 'Acceptance failed: '.$exception->getMessage());
        }

        return back()->with('success', 'Transportation request accepted and a vehicle was allocated.');
    }

    public function reject(Booking $booking): RedirectResponse
    {
        Gate::authorize('manageTransportationProvider', $booking);
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending transportation requests can be rejected.');
        }
        $booking->update(['status' => 'rejected']);

        return back()->with('success', 'Transportation request rejected.');
    }
}
