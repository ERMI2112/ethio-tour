<?php

namespace App\Services;

use App\Models\TourGuide;
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

    private function multiplyMoney(string $amount, int $multiplier): string
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return number_format(($cents * $multiplier) / 100, 2, '.', '');
    }
}
