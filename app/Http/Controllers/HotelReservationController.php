<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckHotelAvailabilityRequest;
use App\Http\Requests\StoreHotelReservationRequest;
use App\Models\Booking;
use App\Models\TourismService;
use App\Services\HotelAvailabilityService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotelReservationController extends Controller
{
    public function checkAvailability(CheckHotelAvailabilityRequest $request, TourismService $tourismService, HotelAvailabilityService $availabilityService)
    {
        $tourismService->loadMissing(['serviceProvider', 'hotelRoomType']);

        if ($tourismService->serviceProvider?->provider_type !== 'hotel' || ! $tourismService->serviceProvider?->isOperational() || ! $tourismService->hotelRoomType) {
            return back()->with('error', 'The selected service is not a hotel room-type service.');
        }

        $validated = $request->validated();

        try {
            $availableRooms = $availabilityService->findAvailableRooms(
                $tourismService,
                $validated['check_in_date'],
                $validated['check_out_date'],
                (int) $validated['guest_count']
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $count = $availableRooms->count();

        if ($count === 0) {
            return back()->with('error', 'No physical rooms are available for the selected dates and guest count.')->withInput();
        }

        return back()->with('success', "Availability confirmed! {$count} room(s) available for your dates.")->with([
            'available_count' => $count,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'guest_count' => $validated['guest_count'],
        ])->withInput();
    }

    public function store(StoreHotelReservationRequest $request, TourismService $tourismService, HotelAvailabilityService $availabilityService, NotificationService $notifications): RedirectResponse
    {
        $tourismService->loadMissing(['serviceProvider', 'hotelRoomType']);

        if ($tourismService->serviceProvider?->provider_type !== 'hotel' || ! $tourismService->serviceProvider?->isOperational() || ! $tourismService->hotelRoomType) {
            return back()->with('error', 'This service is not available for hotel reservation.');
        }

        $validated = $request->validated();
        $tourist = $request->user()->tourist;

        if (! $tourist) {
            return back()->with('error', 'You must have an active tourist profile to make a reservation.');
        }

        if ((int) $validated['guest_count'] > (int) $tourismService->hotelRoomType->capacity) {
            return back()->withErrors(['guest_count' => 'Guest count exceeds room capacity.'])->withInput();
        }

        $availableRooms = $availabilityService->findAvailableRooms(
            $tourismService,
            $validated['check_in_date'],
            $validated['check_out_date'],
            (int) $validated['guest_count']
        );

        if ($availableRooms->isEmpty()) {
            return back()->with('error', 'No rooms are available for the selected dates.')->withInput();
        }

        $booking = DB::transaction(function () use ($tourist, $tourismService, $validated) {
            $nights = (int) date_diff(date_create($validated['check_in_date']), date_create($validated['check_out_date']))->days;

            $booking = Booking::create([
                'tourist_id' => $tourist->tourist_id,
                'service_id' => $tourismService->service_id,
                'guide_id' => null,
                'status' => 'pending',
                'booking_date' => now(),
                'total_amount' => number_format($nights * (float) $tourismService->price, 2, '.', ''),
                'currency' => 'ETB',
            ]);

            $booking->hotelRoomReservation()->create([
                'room_id' => null,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'guest_count' => $validated['guest_count'],
            ]);

            return $booking;
        });

        $notifications->createForUserAndAdministrators($tourismService->serviceProvider?->user, 'booking_request', 'New hotel reservation request', 'A tourist submitted a reservation request for '.$tourismService->service_name.'.');

        return to_route('tourist.reservations.show', $booking)->with('success', 'Reservation request submitted successfully! Pending hotel review.');
    }
}
