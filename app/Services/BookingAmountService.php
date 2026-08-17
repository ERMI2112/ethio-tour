<?php

namespace App\Services;

use App\Models\TourGuide;
use App\Models\TourismService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class BookingAmountService
{
    public const DEFAULT_CURRENCY = 'ETB';

    /**
     * @return array{chargeable_days: int, total_amount: string, currency: string}
     */
    public function calculateGuideBooking(
        TourGuide $guide,
        string|CarbonInterface $startDate,
        string|CarbonInterface $endDate,
    ): array {
        [$start, $end] = $this->validatedDateRange($startDate, $endDate);
        $dailyRate = $this->validatedDailyRate($guide);
        $chargeableDays = (int) $start->diffInDays($end);

        return [
            'chargeable_days' => $chargeableDays,
            'total_amount' => $this->multiplyMoney($dailyRate, $chargeableDays),
            'currency' => self::DEFAULT_CURRENCY,
        ];
    }

    /**
     * Calculate the fixed price of a restaurant reservation offering.
     *
     * @return array{total_amount: string, currency: string}
     */
    public function calculateRestaurant(TourismService $service): array
    {
        return [
            'total_amount' => $this->validatedServicePrice($service),
            'currency' => self::DEFAULT_CURRENCY,
        ];
    }

    /**
     * Calculate a transportation rental in complete 24-hour rental blocks.
     * A valid window shorter than a day is one rental day; the half-open
     * end boundary means an exact 24-hour window remains one day.
     *
     * @return array{rental_days: int, total_amount: string, currency: string}
     */
    public function calculateTransportation(
        TourismService $service,
        string|CarbonInterface $pickupAt,
        string|CarbonInterface $dropoffAt,
    ): array {
        $rentalDays = $this->transportationRentalDays($pickupAt, $dropoffAt);

        return [
            'rental_days' => $rentalDays,
            'total_amount' => $this->multiplyMoney($this->validatedServicePrice($service), $rentalDays),
            'currency' => self::DEFAULT_CURRENCY,
        ];
    }

    public function transportationRentalDays(
        string|CarbonInterface $pickupAt,
        string|CarbonInterface $dropoffAt,
    ): int {
        try {
            $pickup = CarbonImmutable::parse($pickupAt);
            $dropoff = CarbonImmutable::parse($dropoffAt);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'pickup_at' => 'Rental pickup and drop-off times must be valid.',
            ]);
        }

        if ($dropoff->lte($pickup)) {
            throw ValidationException::withMessages([
                'dropoff_at' => 'The drop-off time must be after the pickup time.',
            ]);
        }

        return max(1, (int) ceil($pickup->diffInMinutes($dropoff) / (24 * 60)));
    }

    public function chargeableDays(string|CarbonInterface $startDate, string|CarbonInterface $endDate): int
    {
        [$start, $end] = $this->validatedDateRange($startDate, $endDate);

        return (int) $start->diffInDays($end);
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
                'start_date' => 'Booking dates must be valid dates.',
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be after the start date.',
            ]);
        }

        return [$start, $end];
    }

    private function validatedDailyRate(TourGuide $guide): string
    {
        $dailyRate = trim((string) $guide->daily_rate);

        if ($guide->daily_rate === null || ! preg_match('/^\d+(?:\.\d{1,2})?$/', $dailyRate)) {
            throw ValidationException::withMessages([
                'daily_rate' => 'This guide does not have a valid daily rate configured.',
            ]);
        }

        [$whole, $fraction] = array_pad(explode('.', $dailyRate, 2), 2, '0');

        return $whole.'.'.str_pad($fraction, 2, '0');
    }

    private function validatedServicePrice(TourismService $service): string
    {
        $price = trim((string) $service->price);

        if ($service->price === null || ! preg_match('/^\d+(?:\.\d{1,2})?$/', $price)) {
            throw ValidationException::withMessages([
                'price' => 'This service does not have a valid price configured.',
            ]);
        }

        [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '0');
        $normalized = $whole.'.'.str_pad($fraction, 2, '0');

        if (((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0') <= 0) {
            throw ValidationException::withMessages([
                'price' => 'This service must have a price greater than zero before it can be booked.',
            ]);
        }

        return $normalized;
    }

    private function multiplyMoney(string $amount, int $multiplier): string
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return number_format(($cents * $multiplier) / 100, 2, '.', '');
    }
}
