<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationVehicle extends Model
{
    public const STATUSES = ['active', 'inactive'];

    protected $primaryKey = 'vehicle_id';

    protected $fillable = ['provider_id', 'service_id', 'vehicle_identifier', 'vehicle_type', 'make', 'model', 'year', 'capacity', 'status'];

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }

    public function tourismService()
    {
        return $this->belongsTo(TourismService::class, 'service_id', 'service_id');
    }

    public function reservations()
    {
        return $this->hasMany(TransportationReservation::class, 'vehicle_id', 'vehicle_id');
    }
}
