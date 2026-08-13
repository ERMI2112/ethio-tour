<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RoleProfileProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, RoleProfileProvisioner $provisioner): RedirectResponse
    {
        $validated = $request->validate([
            'account_type' => ['required', Rule::in(['tourist', 'tour_guide', 'service_provider'])],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'full_name' => [Rule::requiredIf($request->input('account_type') === 'tourist'), 'nullable', 'string', 'max:255'],
            'nationality' => [Rule::requiredIf($request->input('account_type') === 'tourist'), 'nullable', 'string', 'max:255'],
            'license_number' => [Rule::requiredIf($request->input('account_type') === 'tour_guide'), 'nullable', 'string', 'max:255', 'unique:tour_guides,license_number'],
            'expertise' => [Rule::requiredIf($request->input('account_type') === 'tour_guide'), 'nullable', 'string'],
            'business_name' => [Rule::requiredIf($request->input('account_type') === 'service_provider'), 'nullable', 'string', 'max:255'],
            'provider_type' => [Rule::requiredIf($request->input('account_type') === 'service_provider'), 'nullable', Rule::in(['hotel', 'restaurant', 'transportation_car_rental', 'event_organizer'])],
        ]);

        $user = $provisioner->registerPublicUser($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account');
    }
}
