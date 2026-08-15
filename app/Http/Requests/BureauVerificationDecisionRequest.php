<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BureauVerificationDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourism_bureau_officer';
    }

    public function rules(): array
    {
        $decision = $this->input('decision');

        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'verification_notes' => [Rule::requiredIf($decision === 'reject'), 'nullable', 'string', 'max:2000'],
        ];
    }
}
