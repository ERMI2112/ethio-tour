<?php

namespace App\Services;

use App\Exceptions\TransportationAvailabilityException;
use App\Models\Booking;
use App\Models\TourismService;
use App\Models\TransportationReservation;
use App\Models\TransportationVehicle;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportationAvailabilityService
{
    public const INVENTORY_RESERVING_STATUSES = ['accepted', 'payment_pending', 'confirmed'];

    public function findAvailableVehicles(TourismService $service, string|CarbonInterface $pickupAt, string|CarbonInterface $dropoffAt, int $passengerCount): Collection
    {
        [$pickup, $dropoff] = $this->validatedWindow($pickupAt, $dropoffAt);
        $this->validateService($service);
        $this->validatePassengerCount($passengerCount);

        return $this->availableVehiclesQuery($service, $pickup, $dropoff, $passengerCount)->get();
    }

    public function isVehicleAvailable(TransportationVehicle $vehicle, string|CarbonInterface $pickupAt, string|CarbonInterface $dropoffAt, int $passengerCount, ?TransportationReservation $ignoreReservation = null): bool
    {
        [$pickup, $dropoff] = $this->validatedWindow($pickupAt, $dropoffAt);
        $this->validatePassengerCount($passengerCount);

        if ($vehicle->status !== 'active' || (int) $vehicle->capacity < $passengerCount) {
            return false;
        }

        return ! $vehicle->reservations()
            ->whereHas('booking', fn (Builder $query) => $query->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
            ->where('pickup_at', '<', $dropoff)
            ->where('dropoff_at', '>', $pickup)
            ->when($ignoreReservation, fn (Builder $query) => $query->whereKeyNot($ignoreReservation->transportation_reservation_id))
            ->exists();
    }

    public function allocateAvailableVehicle(TransportationReservation $reservation): TransportationVehicle
    {
        return DB::transaction(function () use ($reservation): TransportationVehicle {
            $reservation->loadMissing('booking.tourismService.serviceProvider', 'vehicle');
            $booking = $reservation->booking;

            if (! $booking instanceof Booking || $booking->guide_id !== null || ! $booking->tourismService) {
                throw new TransportationAvailabilityException('The reservation must belong to a tourism-service booking.');
            }

            if ($booking->tourismService->serviceProvider?->provider_type !== 'transportation_car_rental') {
                throw new TransportationAvailabilityException('Only transportation services can use vehicle availability.');
            }

            if (! in_array($booking->status, self::INVENTORY_RESERVING_STATUSES, true)) {
                throw new TransportationAvailabilityException('The booking is not eligible for vehicle allocation.');
            }

            [$pickup, $dropoff] = $this->validatedWindow($reservation->pickup_at, $reservation->dropoff_at);
            $this->validatePassengerCount((int) $reservation->passenger_count);

            if ($reservation->vehicle_id !== null) {
                $vehicle = TransportationVehicle::query()->whereKey($reservation->vehicle_id)->lockForUpdate()->first();
                if (! $vehicle || (int) $vehicle->service_id !== (int) $booking->service_id || ! $this->isVehicleAvailable($vehicle, $pickup, $dropoff, (int) $reservation->passenger_count, $reservation)) {
                    throw new TransportationAvailabilityException('The allocated vehicle is no longer available.');
                }

                return $vehicle;
            }

            $vehicle = $this->availableVehiclesQuery($booking->tourismService, $pickup, $dropoff, (int) $reservation->passenger_count)->lockForUpdate()->first();
            if (! $vehicle) {
                throw new TransportationAvailabilityException('No available vehicle matches this request.');
            }

            $reservation->forceFill(['vehicle_id' => $vehicle->vehicle_id])->save();

            return $vehicle;
        }, attempts: 3);
    }

    public function validatePassengerCount(int $passengerCount): void
    {
        if ($passengerCount < 1) {
            throw ValidationException::withMessages(['passenger_count' => 'Passenger count must be greater than zero.']);
        }
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public function validatedWindow(string|CarbonInterface $pickupAt, string|CarbonInterface $dropoffAt): array
    {
        try {
            $pickup = CarbonImmutable::parse($pickupAt);
            $dropoff = CarbonImmutable::parse($dropoffAt);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['pickup_at' => 'Pickup and drop-off times must be valid.']);
        }

        if ($dropoff->lte($pickup)) {
            throw ValidationException::withMessages(['dropoff_at' => 'Drop-off must be after pickup.']);
        }

        return [$pickup, $dropoff];
    }

    private function validateService(TourismService $service): void
    {
        $service->loadMissing('serviceProvider');
        if ($service->serviceProvider?->provider_type !== 'transportation_car_rental') {
            throw new TransportationAvailabilityException('Only transportation services can use vehicle availability.');
        }
    }

    private function availableVehiclesQuery(TourismService $service, CarbonImmutable $pickup, CarbonImmutable $dropoff, int $passengerCount): Builder
    {
        return TransportationVehicle::query()
            ->where('service_id', $service->service_id)
            ->where('provider_id', $service->provider_id)
            ->where('status', 'active')
            ->where('capacity', '>=', $passengerCount)
            ->whereDoesntHave('reservations', function (Builder $query) use ($pickup, $dropoff): void {
                $query->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
                    ->where('pickup_at', '<', $dropoff)
                    ->where('dropoff_at', '>', $pickup);
            });
    }
}
