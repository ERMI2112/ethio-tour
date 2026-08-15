<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckTransportationAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pickup_at' => ['required', 'date', 'after_or_equal:now'],
            'dropoff_at' => ['required', 'date', 'after:pickup_at'],
            'passenger_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
