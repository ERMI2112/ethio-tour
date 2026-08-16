<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tourist extends Model
{
    protected $primaryKey = 'tourist_id';

    protected $fillable = ['user_id', 'full_name', 'nationality'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tourist_id', 'tourist_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'tourist_id', 'tourist_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'user_id', 'user_id');
    }
}
