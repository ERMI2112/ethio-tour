<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';

    protected $fillable = ['tourist_id', 'service_id', 'guide_id', 'status', 'booking_date'];

    protected function casts(): array
    {
        return ['booking_date' => 'datetime'];
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class, 'tourist_id', 'tourist_id');
    }

    public function tourismService()
    {
        return $this->belongsTo(TourismService::class, 'service_id', 'service_id');
    }

    public function tourGuide()
    {
        return $this->belongsTo(TourGuide::class, 'guide_id', 'guide_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'booking_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'booking_id', 'booking_id');
    }
}
