<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'service_provider'
            && $this->user()->serviceProvider?->provider_type === 'restaurant';
    }

    public function rules(): array
    {
        return ['business_name' => ['required', 'string', 'max:255']];
    }
}
