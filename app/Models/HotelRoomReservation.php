<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class HotelRoomReservation extends Model
{
    protected $primaryKey = 'hotel_reservation_id';

    protected $fillable = ['booking_id', 'room_id', 'check_in_date', 'check_out_date', 'guest_count'];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $reservation): void {
            if ((int) $reservation->guest_count < 1) {
                throw ValidationException::withMessages(['guest_count' => 'Guest count must be greater than zero.']);
            }

            if (Carbon::parse($reservation->check_out_date)->lte(Carbon::parse($reservation->check_in_date))) {
                throw ValidationException::withMessages(['check_out_date' => 'Check-out date must be after check-in date.']);
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'room_id', 'room_id');
    }
}
