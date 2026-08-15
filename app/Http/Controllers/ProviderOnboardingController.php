<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderOnboardingProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderOnboardingController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()?->role === 'service_provider' && $request->user()->serviceProvider, 403);

        return view('provider.onboarding-status', ['provider' => $request->user()->serviceProvider]);
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->role === 'service_provider' && $request->user()->serviceProvider, 403);

        return view('provider.onboarding-profile', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(ProviderOnboardingProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('provider.status')->with('success', 'Provider profile updated.');
    }
}
