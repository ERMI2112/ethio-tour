<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class TourGuideReservation extends Model
{
    protected $primaryKey = 'guide_reservation_id';

    protected $fillable = ['booking_id', 'start_date', 'end_date', 'number_of_tourists'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'number_of_tourists' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $reservation): void {
            $booking = $reservation->booking()->first();

            if (! $booking || $booking->guide_id === null || $booking->service_id !== null) {
                throw ValidationException::withMessages([
                    'booking_id' => 'A tour guide reservation requires a guide-targeted central booking.',
                ]);
            }

            if ((int) $reservation->number_of_tourists < 1) {
                throw ValidationException::withMessages([
                    'number_of_tourists' => 'Number of tourists must be greater than zero.',
                ]);
            }

            try {
                $startDate = CarbonImmutable::parse($reservation->start_date);
                $endDate = CarbonImmutable::parse($reservation->end_date);
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'start_date' => 'Tour dates must be valid dates.',
                ]);
            }

            if ($endDate->lte($startDate)) {
                throw ValidationException::withMessages([
                    'end_date' => 'End date must be after start date.',
                ]);
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
