<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $primaryKey = 'provider_id';

    protected $fillable = ['user_id', 'business_name', 'provider_type', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function tourismServices()
    {
        return $this->hasMany(TourismService::class, 'provider_id', 'provider_id');
    }

    public function providerSubscriptions()
    {
        return $this->hasMany(ProviderSubscription::class, 'provider_id', 'provider_id');
    }

    public function culturalEvents()
    {
        return $this->hasMany(CulturalEvent::class, 'provider_id', 'provider_id');
    }

    public function restaurantTables()
    {
        return $this->hasMany(RestaurantTable::class, 'provider_id', 'provider_id');
    }
}
