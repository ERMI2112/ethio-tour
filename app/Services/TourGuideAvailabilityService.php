<?php

namespace App\Services;

use App\Exceptions\TourGuideAvailabilityException;
use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class TourGuideAvailabilityService
{
    public const INVENTORY_RESERVING_STATUSES = [
        'accepted',
        'payment_pending',
        'confirmed',
    ];

    public function conflictingReservations(
        TourGuide $guide,
        string|CarbonInterface $startDate,
        string|CarbonInterface $endDate,
        ?Booking $ignoreBooking = null,
    ): Collection {
        [$start, $end] = $this->validatedDateRange($startDate, $endDate);

        return TourGuideReservation::query()
            ->with(['booking.tourist'])
            ->whereHas('booking', function (Builder $query) use ($guide, $ignoreBooking): void {
                $query->where('guide_id', $guide->guide_id)
                    ->whereIn('status', self::INVENTORY_RESERVING_STATUSES)
                    ->when($ignoreBooking, fn (Builder $bookingQuery) => $bookingQuery->whereKeyNot($ignoreBooking->booking_id));
            })
            ->whereDate('start_date', '<', $end->toDateString())
            ->whereDate('end_date', '>', $start->toDateString())
            ->orderBy('start_date')
            ->get();
    }

    public function isGuideAvailable(
        TourGuide $guide,
        string|CarbonInterface $startDate,
        string|CarbonInterface $endDate,
        ?Booking $ignoreBooking = null,
    ): bool {
        $this->validatedDateRange($startDate, $endDate);

        return $guide->availability_status === 'available'
            && $this->conflictingReservations($guide, $startDate, $endDate, $ignoreBooking)->isEmpty();
    }

    public function assertGuideAvailable(TourGuide $guide, TourGuideReservation $reservation): void
    {
        $this->validatedDateRange($reservation->start_date, $reservation->end_date);

        if ($guide->availability_status !== 'available') {
            throw new TourGuideAvailabilityException('This guide is currently marked unavailable.');
        }

        if (! $this->conflictingReservations($guide, $reservation->start_date, $reservation->end_date, $reservation->booking)->isEmpty()) {
            throw new TourGuideAvailabilityException('The guide already has a conflicting accepted or confirmed reservation.');
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function validatedDateRange(string|CarbonInterface $startDate, string|CarbonInterface $endDate): array
    {
        try {
            $start = CarbonImmutable::parse($startDate);
            $end = CarbonImmutable::parse($endDate);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'start_date' => 'Tour start and end dates must be valid dates.',
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tour end date must be after the start date.',
            ]);
        }

        return [$start, $end];
    }
}
