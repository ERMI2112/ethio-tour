<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTouristProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->is_active)
            && $this->user()?->role === 'tourist'
            && $this->user()?->tourist !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
        ];
    }
}
