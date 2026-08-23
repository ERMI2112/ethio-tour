<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $primaryKey = 'provider_id';

    protected $fillable = [
        'user_id',
        'business_name',
        'provider_type',
        'status',
        'verification_status',
        'verification_notes',
        'manager_name',
        'manager_title',
        'manager_phone',
        'contact_email',
        'tin_number',
        'trade_license_number',
        'star_rating',
        'destination_id',
        'physical_address',
        'total_rooms_count',
        'check_in_time',
        'check_out_time',
        'amenities',
        'payout_bank_name',
        'payout_account_number',
        'payout_account_name',
        'description',
        'cover_image',
        'application_step',
    ];

    protected $attributes = [
        'verification_status' => 'pending',
        'status' => 'pending',
        'application_step' => 1,
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'total_rooms_count' => 'integer',
            'application_step' => 'integer',
        ];
    }

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

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
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

    public function events()
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

    public function coverImageUrl(): string
    {
        if ($this->cover_image) {
            return $this->cover_image;
        }

        return match ($this->provider_type) {
            'hotel' => '/images/services/hotel-suite.jpg',
            'restaurant' => '/images/services/restaurant-dining.jpg',
            'transportation_car_rental' => '/images/services/transport-vehicle.jpg',
            'event_organizer' => '/images/events/timkat.jpg',
            default => '/images/services/hotel-suite.jpg',
        };
    }
}
