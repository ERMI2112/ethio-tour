<?php

namespace App\Http\Controllers;

use App\Exceptions\RestaurantAvailabilityException;
use App\Http\Requests\CheckRestaurantAvailabilityRequest;
use App\Http\Requests\StoreRestaurantReservationRequest;
use App\Models\Booking;
use App\Models\TourismService;
use App\Services\BookingAmountService;
use App\Services\NotificationService;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantTouristReservationController extends Controller
{
    public function checkAvailability(
        CheckRestaurantAvailabilityRequest $request,
        TourismService $tourismService,
        RestaurantAvailabilityService $availabilityService,
    ): RedirectResponse {
        $this->ensureRestaurantService($tourismService);
        $validated = $request->validated();

        try {
            $tables = $availabilityService->findAvailableTables(
                $tourismService,
                $validated['reservation_date'],
                $validated['start_time'],
                $validated['end_time'],
                (int) $validated['guest_count'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (RestaurantAvailabilityException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        if ($tables->isEmpty()) {
            return back()->with('error', 'No restaurant table is available for the selected time and party size.')->withInput();
        }

        return back()->with('success', "Availability confirmed! {$tables->count()} table(s) match your request.")
            ->with('restaurant_available_count', $tables->count())
            ->with('restaurant_reservation_date', $validated['reservation_date'])
            ->with('restaurant_start_time', $validated['start_time'])
            ->with('restaurant_end_time', $validated['end_time'])
            ->with('restaurant_guest_count', $validated['guest_count'])
            ->withInput();
    }

    public function store(
        StoreRestaurantReservationRequest $request,
        TourismService $tourismService,
        RestaurantAvailabilityService $availabilityService,
        BookingAmountService $amountService,
        NotificationService $notifications,
    ): RedirectResponse {
        $this->ensureRestaurantService($tourismService);

        $tourismService->loadMissing('serviceProvider');

        if ($tourismService->serviceProvider?->hasExpiredSubscription()) {
            return back()->with('error', 'This provider\'s subscription has expired, so new bookings are temporarily unavailable.');
        }

        $validated = $request->validated();
        $tourist = $request->user()->tourist;

        try {
            $amountService->calculateRestaurant($tourismService);
            $availabilityService->findAvailableTables(
                $tourismService,
                $validated['reservation_date'],
                $validated['start_time'],
                $validated['end_time'],
                (int) $validated['guest_count'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (RestaurantAvailabilityException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        try {
            $booking = DB::transaction(function () use ($tourist, $tourismService, $validated, $amountService): Booking {
                $lockedService = TourismService::query()->lockForUpdate()->findOrFail($tourismService->service_id);
                $amount = $amountService->calculateRestaurant($lockedService);

                $booking = Booking::create([
                    'tourist_id' => $tourist->tourist_id,
                    'service_id' => $lockedService->service_id,
                    'guide_id' => null,
                    'status' => 'pending',
                    'booking_date' => now(),
                    'total_amount' => $amount['total_amount'],
                    'currency' => $amount['currency'],
                ]);

                $booking->restaurantReservation()->create([
                    'table_id' => null,
                    'reservation_date' => $validated['reservation_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'guest_count' => $validated['guest_count'],
                ]);

                return $booking;
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $notifications->createForUserAndAdministrators($tourismService->serviceProvider?->user, 'reservation_request', 'New restaurant reservation request', 'A tourist requested a table at '.$tourismService->service_name.'.');

        return to_route('tourist.reservations.show', $booking)->with('success', 'Restaurant reservation request submitted successfully.');
    }

    private function ensureRestaurantService(TourismService $service): void
    {
        $service->loadMissing('serviceProvider');

        abort_unless($service->serviceProvider?->provider_type === 'restaurant' && $service->serviceProvider?->isOperational() && $service->isRestaurantReservationOffering(), 404);
    }
}
