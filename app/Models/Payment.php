<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    protected $fillable = ['booking_id', 'amount', 'status', 'payment_method', 'gateway_reference'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
