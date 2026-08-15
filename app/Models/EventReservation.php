<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventReservation extends Model
{
    protected $primaryKey = 'event_reservation_id';

    protected $fillable = ['booking_id', 'ticket_type_id', 'quantity'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function ticketType()
    {
        return $this->belongsTo(EventTicketType::class, 'ticket_type_id', 'ticket_type_id');
    }
}
