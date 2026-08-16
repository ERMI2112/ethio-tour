<?php

namespace App\Http\Requests;

use App\Models\Trip;
use App\Models\TripItem;
use Illuminate\Foundation\Http\FormRequest;

class ReorderTripItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $trip = $this->route('trip');
        $item = $this->route('tripItem');

        return $this->user()?->is_active
            && $this->user()?->role === 'tourist'
            && $trip instanceof Trip
            && $item instanceof TripItem
            && $trip->isOwnedBy($this->user())
            && (int) $item->trip_id === (int) $trip->trip_id;
    }

    public function rules(): array
    {
        return ['direction' => ['required', 'string', 'in:up,down']];
    }
}
