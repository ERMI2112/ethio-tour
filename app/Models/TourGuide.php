<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuide extends Model
{
    protected $primaryKey = 'guide_id';

    protected $fillable = ['user_id', 'license_number', 'expertise', 'availability_status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'guide_id', 'guide_id');
    }
}
