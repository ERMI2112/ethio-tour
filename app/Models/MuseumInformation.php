<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuseumInformation extends Model
{
    protected $table = 'museum_information';

    protected $primaryKey = 'museum_id';

    protected $fillable = ['officer_id', 'museum_name', 'description', 'location', 'opening_hours', 'entrance_fee', 'contact_information', 'media_path'];

    protected function casts(): array
    {
        return ['entrance_fee' => 'decimal:2'];
    }

    public function officer()
    {
        return $this->belongsTo(TourismBureauOfficer::class, 'officer_id', 'officer_id');
    }
}
