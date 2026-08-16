<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripItem extends Model
{
    public const STATUSES = ['planned', 'skipped', 'completed'];

    public const SOURCES = ['suggested', 'manual'];

    protected $primaryKey = 'trip_item_id';

    protected $fillable = ['trip_id', 'item_type', 'item_id', 'planned_date', 'start_time', 'end_time', 'sequence', 'notes', 'status', 'source'];

    protected function casts(): array
    {
        return ['planned_date' => 'date'];
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'trip_id');
    }
}
