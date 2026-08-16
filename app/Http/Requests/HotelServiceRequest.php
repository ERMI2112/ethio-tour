<?php

namespace App\Http\Requests;

use App\Models\TourismService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HotelServiceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $amenities = $this->input('amenities', []);

        if (is_string($amenities)) {
            $amenities = preg_split('/\r\n|\r|\n/', $amenities) ?: [];
        }

        $this->merge([
            'amenities' => collect($amenities)->map(fn ($amenity) => trim((string) $amenity))->filter()->values()->all(),
        ]);
    }

    public function authorize(): bool
    {
        $service = $this->route('tourismService');

        if (! $this->user()?->serviceProvider || $this->user()->serviceProvider->provider_type !== 'hotel') {
            return false;
        }

        return ! $service instanceof TourismService
            || (int) $service->provider_id === (int) $this->user()->serviceProvider->provider_id;
    }

    public function rules(): array
    {
        return [
            'service_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'category_id')],
            'destination_id' => ['required', 'integer', Rule::exists('destinations', 'destination_id')],
            'capacity' => ['required', 'integer', 'min:1'],
            'amenities' => ['array'],
            'amenities.*' => ['string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ];
    }
}
