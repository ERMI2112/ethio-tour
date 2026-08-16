<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->is_active && $this->user()?->role === 'tourist');
    }

    public function rules(): array
    {
        return ['rating' => ['required', 'integer', 'min:1', 'max:5'], 'comment' => ['required', 'string', 'min:10', 'max:2000']];
    }
}
