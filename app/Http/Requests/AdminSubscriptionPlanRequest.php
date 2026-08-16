<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'administrator';
    }

    public function rules(): array
    {
        $planId = $this->route('subscriptionPlan')?->plan_id;

        return [
            'plan' => ['required', 'string', 'max:255', 'unique:subscription_plans,plan,'.$planId.',plan_id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'duration' => ['required', 'integer', 'min:1'],
        ];
    }
}
