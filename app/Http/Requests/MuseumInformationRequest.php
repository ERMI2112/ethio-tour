<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MuseumInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $museum = $this->route('museumInformation');

        return $user?->role === 'tourism_bureau_officer'
            && $user->tourismBureauOfficer !== null
            && (! $museum || (int) $museum->officer_id === (int) $user->tourismBureauOfficer->officer_id);
    }

    public function rules(): array
    {
        return [
            'museum_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'location' => ['required', 'string', 'max:255'],
            'opening_hours' => ['required', 'string', 'max:255'],
            'entrance_fee' => ['nullable', 'numeric', 'min:0'],
            'contact_information' => ['nullable', 'string', 'max:255'],
            'media_path' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
        ];
    }
}
