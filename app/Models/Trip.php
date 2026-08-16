<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    public const STATUSES = ['draft', 'planned', 'completed'];

    protected $primaryKey = 'trip_id';

    protected $fillable = ['user_id', 'title', 'start_date', 'end_date', 'status', 'preferences'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'preferences' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function destinations()
    {
        return $this->belongsToMany(Destination::class, 'trip_destinations', 'trip_id', 'destination_id', 'trip_id', 'destination_id');
    }

    public function items()
    {
        return $this->hasMany(TripItem::class, 'trip_id', 'trip_id');
    }

    public function isOwnedBy(User $user): bool
    {
        return $user->role === 'tourist'
            && $user->is_active
            && (int) $this->user_id === (int) $user->user_id;
    }
}
