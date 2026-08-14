<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class HotelRoom extends Model
{
    public const STATUSES = ['active', 'inactive'];

    protected $primaryKey = 'room_id';

    protected $attributes = ['status' => 'active'];

    protected $fillable = ['room_type_id', 'room_number', 'status'];

    protected static function booted(): void
    {
        static::saving(function (self $room): void {
            $room->room_number = trim((string) $room->room_number);

            if ($room->room_number === '') {
                throw ValidationException::withMessages(['room_number' => 'Room number must not be empty.']);
            }

            if (! in_array($room->status, self::STATUSES, true)) {
                throw ValidationException::withMessages(['status' => 'Room status must be active or inactive.']);
            }
        });
    }

    public function hotelRoomType()
    {
        return $this->belongsTo(HotelRoomType::class, 'room_type_id', 'room_type_id');
    }

    public function hotelRoomReservations()
    {
        return $this->hasMany(HotelRoomReservation::class, 'room_id', 'room_id');
    }
}
