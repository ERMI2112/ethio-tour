<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderOnboardingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'service_provider' && $this->user()->serviceProvider !== null;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_title' => ['nullable', 'string', 'max:255'],
            'secondary_contact_name' => ['nullable', 'string', 'max:255'],
            'secondary_contact_title' => ['nullable', 'string', 'max:255'],
            'manager_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'tin_number' => ['nullable', 'string', 'max:50'],
            'trade_license_number' => ['nullable', 'string', 'max:100'],
            'permit_number' => ['nullable', 'string', 'max:100'],
            'star_rating' => ['nullable', 'string', 'max:50'],
            'destination_id' => ['nullable', 'exists:destinations,destination_id'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'total_rooms_count' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'capacity_count' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'check_in_time' => ['nullable', 'string', 'max:50'],
            'check_out_time' => ['nullable', 'string', 'max:50'],
            'operating_hours' => ['nullable', 'string', 'max:100'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'payout_bank_name' => ['nullable', 'string', 'max:100'],
            'payout_account_number' => ['nullable', 'string', 'max:100'],
            'payout_account_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
