<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportationReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourist' && $this->user()->is_active && $this->user()->tourist !== null;
    }

    public function rules(): array
    {
        return [
            'pickup_location' => ['required', 'string', 'max:255'],
            'dropoff_location' => ['required', 'string', 'max:255'],
            'pickup_at' => ['required', 'date', 'after_or_equal:now'],
            'dropoff_at' => ['required', 'date', 'after:pickup_at'],
            'passenger_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
