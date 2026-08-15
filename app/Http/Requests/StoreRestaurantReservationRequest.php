<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourist'
            && $this->user()->is_active
            && $this->user()->tourist !== null;
    }

    public function rules(): array
    {
        return [
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'guest_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
