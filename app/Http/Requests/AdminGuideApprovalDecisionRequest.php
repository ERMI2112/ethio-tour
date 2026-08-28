<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminGuideApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrator';
    }

    public function rules(): array
    {
        $decision = $this->input('decision');

        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'approval_notes' => [Rule::requiredIf($decision === 'reject'), 'nullable', 'string', 'max:2000'],
        ];
    }
}
