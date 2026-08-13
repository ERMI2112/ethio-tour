<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourismService extends Model
{
    protected $primaryKey = 'service_id';

    protected $fillable = ['provider_id', 'category_id', 'destination_id', 'service_name', 'price', 'description'];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_id', 'service_id');
    }
}
