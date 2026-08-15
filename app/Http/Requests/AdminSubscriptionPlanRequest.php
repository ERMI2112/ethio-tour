<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrator';
    }

    public function rules(): array
    {
        return ['plan' => ['required', 'string', 'max:255'], 'price' => ['nullable', 'numeric', 'min:0'], 'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'], 'duration' => ['required', 'integer', 'min:1']];
    }
}
