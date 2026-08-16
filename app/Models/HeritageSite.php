<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Illuminate\Database\Eloquent\Model;

class HeritageSite extends Model
{
    use HasCoordinates;

    protected $primaryKey = 'heritage_id';

    protected $fillable = ['destination_id', 'heritage_type', 'opening_hours', 'entrance_fee', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return ['entrance_fee' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }
}
