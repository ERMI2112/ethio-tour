<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckHotelAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'check_in_date.after_or_equal' => 'Check-in date cannot be in the past.',
            'check_out_date.after' => 'Check-out date must be after check-in date.',
            'guest_count.min' => 'Guest count must be at least 1.',
        ];
    }
}
