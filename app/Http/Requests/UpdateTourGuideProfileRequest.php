<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourGuideProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tour_guide' && $this->user()?->tourGuide !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'max:150'],
            'expertise' => ['required', 'string', 'max:2000'],
            'availability_status' => ['required', Rule::in(['available', 'unavailable'])],
            'daily_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'languages' => ['nullable'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'primary_destination_id' => ['nullable', 'integer', 'exists:destinations,destination_id'],
            'specialties' => ['nullable'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ];
    }
}
