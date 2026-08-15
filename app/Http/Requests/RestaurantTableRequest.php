<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        $table = $this->route('restaurantTable');
        $provider = $this->user()?->serviceProvider;

        return $provider?->provider_type === 'restaurant'
            && (! $table instanceof RestaurantTable || (int) $table->provider_id === (int) $provider->provider_id);
    }

    public function rules(): array
    {
        return [
            'table_number' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(RestaurantTable::STATUSES)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $provider = $this->user()?->serviceProvider;

            if (! $provider || ! $this->filled('table_number')) {
                return;
            }

            $table = $this->route('restaurantTable');
            $query = RestaurantTable::query()
                ->where('provider_id', $provider->provider_id)
                ->where('table_number', trim((string) $this->input('table_number')));

            if ($table instanceof RestaurantTable) {
                $query->whereKeyNot($table->table_id);
            }

            if ($query->exists()) {
                $validator->errors()->add('table_number', 'This table number is already used by your restaurant.');
            }
        });
    }
}
