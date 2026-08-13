<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $primaryKey = 'destination_id';

    protected $fillable = ['officer_id', 'name', 'location', 'description'];

    public function officer()
    {
        return $this->belongsTo(TourismBureauOfficer::class, 'officer_id', 'officer_id');
    }

    public function heritageSites()
    {
        return $this->hasMany(HeritageSite::class, 'destination_id', 'destination_id');
    }

    public function culturalEvents()
    {
        return $this->hasMany(CulturalEvent::class, 'destination_id', 'destination_id');
    }

    public function tourismServices()
    {
        return $this->hasMany(TourismService::class, 'destination_id', 'destination_id');
    }
}
