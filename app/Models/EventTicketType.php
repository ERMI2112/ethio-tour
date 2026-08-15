<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketType extends Model
{
    public const STATUSES = ['active', 'inactive'];

    protected $primaryKey = 'ticket_type_id';

    protected $fillable = ['event_id', 'name', 'price', 'quantity', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function event()
    {
        return $this->belongsTo(CulturalEvent::class, 'event_id', 'event_id');
    }

    public function reservations()
    {
        return $this->hasMany(EventReservation::class, 'ticket_type_id', 'ticket_type_id');
    }
}
