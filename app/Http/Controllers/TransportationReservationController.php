<?php

namespace App\Http\Controllers;

use App\Exceptions\TransportationAvailabilityException;
use App\Models\Booking;
use App\Services\NotificationService;
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
        $status = $request->string('status')->trim()->value();
        $bookings = Booking::with(['tourist', 'tourismService', 'transportationReservation.vehicle'])
            ->whereIn('service_id', $serviceIds)->whereHas('transportationReservation')->when($status, fn ($query) => $query->where('status', $status))->orderByDesc('booking_id')->paginate(10)->withQueryString();

        return view('transportation.reservations.index', compact('bookings', 'status'));
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('manageTransportationProvider', $booking);
        $booking->load(['tourist', 'tourismService.serviceProvider', 'transportationReservation.vehicle', 'payment']);
        abort_unless($booking->transportationReservation !== null, 404);

        return view('transportation.reservations.show', compact('booking'));
    }

    public function accept(Booking $booking, TransportationAvailabilityService $availabilityService, NotificationService $notifications): RedirectResponse
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

        $notifications->createForUser($booking->fresh('tourist')->tourist?->user, 'reservation_accepted', 'Transportation request accepted', 'Your transportation request was accepted and a vehicle was allocated.');

        return back()->with('success', 'Transportation request accepted and a vehicle was allocated.');
    }

    public function reject(Booking $booking, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('manageTransportationProvider', $booking);
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending transportation requests can be rejected.');
        }
        $booking->update(['status' => 'rejected']);
        $notifications->createForUser($booking->fresh('tourist')->tourist?->user, 'reservation_rejected', 'Transportation request rejected', 'Your transportation request was rejected by the provider.');

        return back()->with('success', 'Transportation request rejected.');
    }
}
