<?php

namespace App\Services;

use App\Exceptions\HotelAvailabilityException;
use App\Models\Booking;
use App\Models\HotelRoom;
use App\Models\HotelRoomReservation;
use App\Models\HotelRoomType;
use App\Models\TourismService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotelAvailabilityService
{
    public const INVENTORY_RESERVING_STATUSES = [
        'accepted',
        'payment_pending',
        'confirmed',
    ];

    public function findAvailableRooms(
        TourismService $tourismService,
        string|CarbonInterface $checkInDate,
        string|CarbonInterface $checkOutDate,
        int $guestCount,
    ): Collection {
        [$checkIn, $checkOut] = $this->validatedDateRange($checkInDate, $checkOutDate);
        $roomType = $this->hotelRoomTypeFor($tourismService);
        $this->validateGuestCapacity($roomType, $guestCount);

        return $this->availableRoomsQuery($roomType, $checkIn, $checkOut)->get();
    }

    public function isRoomAvailable(
        HotelRoom $room,
        string|CarbonInterface $checkInDate,
        string|CarbonInterface $checkOutDate,
        int $guestCount,
        ?HotelRoomReservation $ignoreReservation = null,
    ): bool {
        [$checkIn, $checkOut] = $this->validatedDateRange($checkInDate, $checkOutDate);
        $room->loadMissing('hotelRoomType');

        if ($room->status !== 'active') {
            return false;
        }

        $this->validateGuestCapacity($room->hotelRoomType, $guestCount);

        return ! $room->hotelRoomReservations()
            ->whereHas('booking', fn (Builder $query) => $query->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
            ->whereDate('check_in_date', '<', $checkOut->toDateString())
            ->whereDate('check_out_date', '>', $checkIn->toDateString())
            ->when($ignoreReservation, fn (Builder $query) => $query->whereKeyNot($ignoreReservation->hotel_reservation_id))
            ->exists();
    }

    public function validateGuestCapacity(HotelRoomType $roomType, int $guestCount): void
    {
        if ($guestCount < 1) {
            throw ValidationException::withMessages(['guest_count' => 'Guest count must be greater than zero.']);
        }

        if ($guestCount > $roomType->capacity) {
            throw ValidationException::withMessages(['guest_count' => 'Guest count exceeds the room capacity.']);
        }
    }

    public function allocateAvailableRoom(HotelRoomReservation $reservation): HotelRoom
    {
        return DB::transaction(function () use ($reservation): HotelRoom {
            $reservation->loadMissing('booking.tourismService.hotelRoomType', 'hotelRoom');
            $booking = $reservation->booking;

            if (! $booking instanceof Booking || ! $booking->tourismService || $booking->guide_id !== null) {
                throw new HotelAvailabilityException('The reservation must belong to a tourism-service booking.');
            }

            if (! in_array($booking->status, self::INVENTORY_RESERVING_STATUSES, true)) {
                throw new HotelAvailabilityException('The booking is not in an inventory-reserving status.');
            }

            [$checkIn, $checkOut] = $this->validatedDateRange($reservation->check_in_date, $reservation->check_out_date);
            $roomType = $this->hotelRoomTypeFor($booking->tourismService);
            $this->validateGuestCapacity($roomType, (int) $reservation->guest_count);

            if ($reservation->room_id !== null) {
                $room = $reservation->hotelRoom;

                if (! $room || (int) $room->room_type_id !== (int) $roomType->room_type_id) {
                    throw new HotelAvailabilityException('The allocated room does not belong to the booked room type.');
                }

                if (! $this->isRoomAvailable($room, $checkIn, $checkOut, (int) $reservation->guest_count, $reservation)) {
                    throw new HotelAvailabilityException('The allocated room is no longer available.');
                }

                return $room;
            }

            $room = $this->availableRoomsQuery($roomType, $checkIn, $checkOut)
                ->lockForUpdate()
                ->first();

            if (! $room) {
                throw new HotelAvailabilityException('No available room matches this request.');
            }

            $reservation->forceFill(['room_id' => $room->room_id])->save();

            return $room;
        }, attempts: 3);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function validatedDateRange(string|CarbonInterface $checkInDate, string|CarbonInterface $checkOutDate): array
    {
        try {
            $checkIn = CarbonImmutable::parse($checkInDate);
            $checkOut = CarbonImmutable::parse($checkOutDate);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['check_in_date' => 'Check-in and check-out dates must be valid dates.']);
        }

        if ($checkOut->lte($checkIn)) {
            throw ValidationException::withMessages(['check_out_date' => 'Check-out date must be after check-in date.']);
        }

        return [$checkIn, $checkOut];
    }

    private function hotelRoomTypeFor(TourismService $tourismService): HotelRoomType
    {
        $tourismService->loadMissing('serviceProvider', 'hotelRoomType');

        if ($tourismService->serviceProvider?->provider_type !== 'hotel') {
            throw new HotelAvailabilityException('Only hotel tourism services can use room availability.');
        }

        if (! $tourismService->hotelRoomType) {
            throw new HotelAvailabilityException('The tourism service has no hotel room type.');
        }

        return $tourismService->hotelRoomType;
    }

    private function availableRoomsQuery(HotelRoomType $roomType, CarbonImmutable $checkIn, CarbonImmutable $checkOut): HasMany
    {
        return $roomType->hotelRooms()
            ->where('status', 'active')
            ->whereDoesntHave('hotelRoomReservations', function (Builder $query) use ($checkIn, $checkOut): void {
                $query->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->whereIn('status', self::INVENTORY_RESERVING_STATUSES))
                    ->whereDate('check_in_date', '<', $checkOut->toDateString())
                    ->whereDate('check_out_date', '>', $checkIn->toDateString());
            });
    }
}
