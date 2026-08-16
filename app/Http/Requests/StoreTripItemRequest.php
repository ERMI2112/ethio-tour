<?php

namespace App\Http\Requests;

use App\Models\Trip;
use App\Services\TripItemTargetResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $trip = $this->route('trip');

        return $this->user()?->is_active
            && $this->user()?->role === 'tourist'
            && $trip instanceof Trip
            && $trip->isOwnedBy($this->user());
    }

    public function rules(): array
    {
        return [
            'item_type' => ['required', 'string', 'in:'.implode(',', TripItemTargetResolver::TYPES)],
            'item_id' => ['required', 'integer', 'min:1'],
            'planned_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
