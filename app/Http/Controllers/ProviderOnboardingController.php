<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderOnboardingProfileRequest;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderOnboardingController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()?->role === 'service_provider' && $request->user()->serviceProvider, 403);

        $provider = $request->user()->serviceProvider->load(['providerSubscriptions.subscriptionPlan', 'destination']);

        return view('provider.onboarding-status', compact('provider'));
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->role === 'service_provider' && $request->user()->serviceProvider, 403);

        $provider = $request->user()->serviceProvider->load(['destination', 'verificationDocuments']);
        $destinations = Destination::orderBy('name')->get();

        return view('provider.onboarding-profile', compact('provider', 'destinations'));
    }

    public function update(ProviderOnboardingProfileRequest $request): RedirectResponse
    {
        $provider = $request->user()->serviceProvider;
        $data = $request->validated();

        // Track that application details have been filled
        $data['application_step'] = 2;

        $provider->update($data);

        return to_route('provider.status')->with('success', 'Provider application profile updated successfully.');
    }
}
