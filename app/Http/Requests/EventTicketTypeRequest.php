<?php

namespace App\Http\Requests;

use App\Models\CulturalEvent;
use App\Models\EventTicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('culturalEvent');
        $ticket = $this->route('eventTicketType');
        $provider = $this->user()?->serviceProvider;

        return $provider?->provider_type === 'event_organizer'
            && $event instanceof CulturalEvent && (int) $event->provider_id === (int) $provider->provider_id
            && (! $ticket instanceof EventTicketType || (int) $ticket->event_id === (int) $event->event_id);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'price' => ['required', 'numeric', 'min:0'], 'quantity' => ['required', 'integer', 'min:1'], 'status' => ['required', Rule::in(EventTicketType::STATUSES)]];
    }
}
