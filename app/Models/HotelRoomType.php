<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class HotelRoomType extends Model
{
    protected $primaryKey = 'room_type_id';

    protected $fillable = ['service_id', 'capacity', 'amenities'];

    protected function casts(): array
    {
        return ['amenities' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $roomType): void {
            if ((int) $roomType->capacity < 1) {
                throw ValidationException::withMessages(['capacity' => 'Capacity must be greater than zero.']);
            }

            if (! is_array($roomType->amenities)) {
                throw ValidationException::withMessages(['amenities' => 'Amenities must be an array.']);
            }
        });
    }

    public function tourismService()
    {
        return $this->belongsTo(TourismService::class, 'service_id', 'service_id');
    }

    public function hotelRooms()
    {
        return $this->hasMany(HotelRoom::class, 'room_type_id', 'room_type_id');
    }
}
