<?php

namespace App\Http\Requests;

use App\Models\TourismService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportationServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('tourismService');
        $provider = $this->user()?->serviceProvider;

        return $provider?->provider_type === 'transportation_car_rental'
            && (! $service instanceof TourismService || (int) $service->provider_id === (int) $provider->provider_id);
    }

    public function rules(): array
    {
        return [
            'service_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'category_id')],
            'destination_id' => ['required', 'integer', Rule::exists('destinations', 'destination_id')],
        ];
    }
}
