<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationReservation extends Model
{
    protected $primaryKey = 'transportation_reservation_id';

    protected $fillable = ['booking_id', 'vehicle_id', 'pickup_location', 'dropoff_location', 'pickup_at', 'dropoff_at', 'passenger_count'];

    protected function casts(): array
    {
        return ['pickup_at' => 'datetime', 'dropoff_at' => 'datetime'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportationVehicle::class, 'vehicle_id', 'vehicle_id');
    }
}
