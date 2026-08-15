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
            'expertise' => ['required', 'string', 'max:2000'],
            'availability_status' => ['required', Rule::in(['available', 'unavailable'])],
            'daily_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
