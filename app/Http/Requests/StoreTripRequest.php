<?php

namespace App\Http\Requests;

use App\Services\SmartTripRecommendationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active
            && $this->user()?->role === 'tourist'
            && $this->user()?->tourist !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'destination_ids' => ['required', 'array', 'min:1', 'max:10'],
            'destination_ids.*' => ['integer', 'distinct', 'exists:destinations,destination_id'],
            'preferences' => ['nullable', 'array', 'max:6'],
            'preferences.*' => ['string', 'distinct', 'in:'.implode(',', SmartTripRecommendationService::INTERESTS)],
        ];
    }
}
