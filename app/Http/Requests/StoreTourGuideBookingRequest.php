<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourGuideBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourist'
            && $this->user()?->is_active
            && $this->user()?->tourist !== null;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'number_of_tourists' => ['required', 'integer', 'min:1', 'max:50'],
            'special_interests' => ['nullable', 'string', 'max:1000'],
            'language_preference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
