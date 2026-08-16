<?php

namespace App\Http\Requests;

use App\Models\CulturalEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CulturalEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('culturalEvent');
        $provider = $this->user()?->serviceProvider;

        return $provider?->provider_type === 'event_organizer' && (! $event instanceof CulturalEvent || (int) $event->provider_id === (int) $provider->provider_id);
    }

    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'venue' => ['required', 'string', 'max:255'],
            'destination_id' => ['required', 'integer', Rule::exists('destinations', 'destination_id')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'category_id')],
            'status' => ['required', Rule::in(CulturalEvent::STATUSES)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }
}
