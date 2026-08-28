<?php

namespace App\Http\Requests;

use App\Models\VerificationDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerificationDocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['tour_guide', 'service_provider'], true);
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(VerificationDocument::TYPES)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
