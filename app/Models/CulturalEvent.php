<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Illuminate\Database\Eloquent\Model;

class CulturalEvent extends Model
{
    use HasCoordinates;

    public const STATUSES = ['draft', 'published', 'cancelled'];

    protected $primaryKey = 'event_id';

    protected $fillable = ['destination_id', 'provider_id', 'service_id', 'event_name', 'description', 'event_date', 'start_time', 'end_time', 'venue', 'status', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }

    public function service()
    {
        return $this->belongsTo(TourismService::class, 'service_id', 'service_id');
    }

    public function ticketTypes()
    {
        return $this->hasMany(EventTicketType::class, 'event_id', 'event_id');
    }
}
