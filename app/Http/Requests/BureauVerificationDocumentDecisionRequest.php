<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BureauVerificationDocumentDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tourism_bureau_officer';
    }

    public function rules(): array
    {
        $decision = $this->input('decision');

        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'review_notes' => [Rule::requiredIf($decision === 'rejected'), 'nullable', 'string', 'max:2000'],
        ];
    }
}
