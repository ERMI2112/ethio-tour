<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourist' && $this->user()->is_active && $this->user()->tourist !== null;
    }

    public function rules(): array
    {
        return ['ticket_type_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1']];
    }
}
