<?php

namespace App\Services;

use App\Exceptions\RestaurantAvailabilityException;
use App\Models\Booking;
use App\Models\RestaurantReservation;
use App\Models\RestaurantTable;
use App\Models\TourismService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantAvailabilityService
{
    public const INVENTORY_RESERVING_STATUSES = ['accepted', 'payment_pending', 'confirmed'];

    public function findAvailableTables(
        TourismService $service,
        string|CarbonInterface $date,
        string|CarbonInterface $startTime,
        string|CarbonInterface $endTime,
        int $guestCount,
    ): Collection {
        [$reservationDate, $start, $end] = $this->validatedWindow($date, $startTime, $endTime);
        $this->validateRestaurantService($service);
        $this->validateGuestCount($guestCount);

        return $this->availableTablesQuery($service, $reservationDate, $start, $end, $guestCount)->get();
    }

    public function isTableAvailable(
        RestaurantTable $table,
        string|CarbonInterface $date,
        string|CarbonInterface $startTime,
        string|CarbonInterface $endTime,
        int $guestCount,
        ?RestaurantReservation $ignoreReservation = null,
    ): bool {
        [$reservationDate, $start, $end] = $this->validatedWindow($date, $startTime, $endTime);
        $this->validateGuestCount($guestCount);

        if ($table->status !== 'active' || (int) $table->capacity < $guestCount) {
            return false;
        }

        return ! $table->restaurantReservations()
            ->whereDate('reservation_date', $reservationDate->toDateString())
            ->whereHas('booking', fn (Builder $query) => $query->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('end_time', '>', $start->format('H:i:s'))
            ->when($ignoreReservation, fn (Builder $query) => $query->whereKeyNot($ignoreReservation->restaurant_reservation_id))
            ->exists();
    }

    public function allocateAvailableTable(RestaurantReservation $reservation): RestaurantTable
    {
        return DB::transaction(function () use ($reservation): RestaurantTable {
            $reservation->loadMissing('booking.tourismService.serviceProvider', 'restaurantTable');
            $booking = $reservation->booking;

            if (! $booking instanceof Booking || $booking->guide_id !== null || ! $booking->tourismService) {
                throw new RestaurantAvailabilityException('The reservation must belong to a tourism-service booking.');
            }

            if ($booking->tourismService->serviceProvider?->provider_type !== 'restaurant') {
                throw new RestaurantAvailabilityException('Only restaurant services can use table availability.');
            }

            if (! in_array($booking->status, ['accepted', ...self::INVENTORY_RESERVING_STATUSES], true)) {
                throw new RestaurantAvailabilityException('The booking is not eligible for table allocation.');
            }

            [$reservationDate, $start, $end] = $this->validatedWindow($reservation->reservation_date, $reservation->start_time, $reservation->end_time);
            $this->validateGuestCount((int) $reservation->guest_count);

            if ($reservation->table_id !== null) {
                $table = $reservation->restaurantTable;

                if (! $table || (int) $table->provider_id !== (int) $booking->tourismService->provider_id) {
                    throw new RestaurantAvailabilityException('The allocated table does not belong to the booked restaurant.');
                }

                if (! $this->isTableAvailable($table, $reservationDate, $start, $end, (int) $reservation->guest_count, $reservation)) {
                    throw new RestaurantAvailabilityException('The allocated table is no longer available.');
                }

                return $table;
            }

            $table = $this->availableTablesQuery($booking->tourismService, $reservationDate, $start, $end, (int) $reservation->guest_count)
                ->lockForUpdate()
                ->first();

            if (! $table) {
                throw new RestaurantAvailabilityException('No available restaurant table matches this request.');
            }

            $reservation->forceFill(['table_id' => $table->table_id])->save();

            return $table;
        }, attempts: 3);
    }

    public function validateGuestCount(int $guestCount): void
    {
        if ($guestCount < 1) {
            throw ValidationException::withMessages(['guest_count' => 'Guest count must be greater than zero.']);
        }
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: CarbonImmutable} */
    public function validatedWindow(string|CarbonInterface $date, string|CarbonInterface $startTime, string|CarbonInterface $endTime): array
    {
        try {
            $reservationDate = CarbonImmutable::parse($date)->startOfDay();
            $start = CarbonImmutable::parse($reservationDate->toDateString().' '.CarbonImmutable::parse($startTime)->format('H:i:s'));
            $end = CarbonImmutable::parse($reservationDate->toDateString().' '.CarbonImmutable::parse($endTime)->format('H:i:s'));
        } catch (\Throwable) {
            throw ValidationException::withMessages(['start_time' => 'Reservation date and times must be valid.']);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['end_time' => 'End time must be after start time.']);
        }

        return [$reservationDate, $start, $end];
    }

    private function validateRestaurantService(TourismService $service): void
    {
        $service->loadMissing('serviceProvider');

        if ($service->serviceProvider?->provider_type !== 'restaurant' || ! $service->isRestaurantReservationOffering()) {
            throw new RestaurantAvailabilityException('Only restaurant tourism services can use table availability.');
        }
    }

    private function availableTablesQuery(TourismService $service, CarbonImmutable $date, CarbonImmutable $start, CarbonImmutable $end, int $guestCount): Builder
    {
        return RestaurantTable::query()
            ->where('provider_id', $service->provider_id)
            ->where('status', 'active')
            ->where('capacity', '>=', $guestCount)
            ->whereDoesntHave('restaurantReservations', function (Builder $query) use ($date, $start, $end): void {
                $query->whereDate('reservation_date', $date->toDateString())
                    ->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
                    ->where('start_time', '<', $end->format('H:i:s'))
                    ->where('end_time', '>', $start->format('H:i:s'));
            });
    }
}
