<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourismBureauOfficer extends Model
{
    protected $primaryKey = 'officer_id';

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function destinations()
    {
        return $this->hasMany(Destination::class, 'officer_id', 'officer_id');
    }

    public function museumInformation()
    {
        return $this->hasMany(MuseumInformation::class, 'officer_id', 'officer_id');
    }
}
