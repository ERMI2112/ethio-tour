<?php

namespace App\Http\Requests;

use App\Models\TransportationVehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportationVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('transportationVehicle');
        $provider = $this->user()?->serviceProvider;

        return $provider?->provider_type === 'transportation_car_rental'
            && (! $vehicle instanceof TransportationVehicle || (int) $vehicle->provider_id === (int) $provider->provider_id);
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', Rule::exists('tourism_services', 'service_id')],
            'vehicle_identifier' => ['required', 'string', 'max:100'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(TransportationVehicle::STATUSES)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $provider = $this->user()?->serviceProvider;
            $service = $this->integer('service_id');
            if (! $provider || ! $service) {
                return;
            }
            if (! $provider->tourismServices()->whereKey($service)->exists()) {
                $validator->errors()->add('service_id', 'The selected service must belong to your transportation provider.');
            }
            $query = TransportationVehicle::query()->where('provider_id', $provider->provider_id)->where('vehicle_identifier', trim((string) $this->input('vehicle_identifier')));
            if ($this->route('transportationVehicle') instanceof TransportationVehicle) {
                $query->whereKeyNot($this->route('transportationVehicle')->vehicle_id);
            }
            if ($query->exists()) {
                $validator->errors()->add('vehicle_identifier', 'This vehicle identifier is already used by your provider.');
            }
        });
    }
}
