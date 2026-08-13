<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeritageSite extends Model
{
    protected $primaryKey = 'heritage_id';

    protected $fillable = ['destination_id', 'heritage_type', 'opening_hours', 'entrance_fee'];

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }
}
