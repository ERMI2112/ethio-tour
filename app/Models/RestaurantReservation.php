<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class RestaurantReservation extends Model
{
    protected $primaryKey = 'restaurant_reservation_id';

    protected $fillable = [
        'booking_id',
        'table_id',
        'reservation_date',
        'start_time',
        'end_time',
        'guest_count',
    ];

    protected function casts(): array
    {
        return ['reservation_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $reservation): void {
            $booking = $reservation->booking()->with('tourismService.serviceProvider')->first();

            if (! $booking || $booking->guide_id !== null || $booking->tourismService?->serviceProvider?->provider_type !== 'restaurant') {
                throw ValidationException::withMessages([
                    'booking_id' => 'A restaurant reservation requires a restaurant tourism-service booking.',
                ]);
            }

            if ((int) $reservation->guest_count < 1) {
                throw ValidationException::withMessages(['guest_count' => 'Guest count must be greater than zero.']);
            }

            try {
                $start = Carbon::parse($reservation->start_time);
                $end = Carbon::parse($reservation->end_time);
            } catch (\Throwable) {
                throw ValidationException::withMessages(['start_time' => 'Reservation times must be valid.']);
            }

            if ($end->lte($start)) {
                throw ValidationException::withMessages(['end_time' => 'End time must be after start time.']);
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id', 'table_id');
    }
}
