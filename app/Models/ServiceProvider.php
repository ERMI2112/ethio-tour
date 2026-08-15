<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $primaryKey = 'provider_id';

    protected $fillable = ['user_id', 'business_name', 'provider_type', 'status', 'verification_notes'];

    protected $attributes = ['verification_status' => 'pending', 'status' => 'pending'];

    protected static function booted(): void
    {
        static::creating(function (self $provider): void {
            // Existing fixtures and legacy approved records represent both completed gates.
            if ($provider->status === 'approved' && ! $provider->isDirty('verification_status')) {
                $provider->verification_status = 'verified';
            }
        });
    }

    public function isOperational(): bool
    {
        return $this->verification_status === 'verified'
            && $this->status === 'approved'
            && (bool) $this->user?->is_active;
    }

    public function scopePubliclyOperational($query)
    {
        return $query->where('verification_status', 'verified')
            ->where('status', 'approved')
            ->whereHas('user', fn ($user) => $user->where('is_active', true));
    }

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

    public function transportationVehicles()
    {
        return $this->hasMany(TransportationVehicle::class, 'provider_id', 'provider_id');
    }

    public function events()
    {
        return $this->hasMany(CulturalEvent::class, 'provider_id', 'provider_id');
    }
}
