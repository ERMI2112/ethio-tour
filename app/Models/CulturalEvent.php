<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CulturalEvent extends Model
{
    protected $primaryKey = 'event_id';

    protected $fillable = ['destination_id', 'provider_id', 'event_name', 'event_date', 'venue'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }
}
