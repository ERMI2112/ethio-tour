<?php

namespace App\Http\Controllers;

use App\Http\Requests\BureauVerificationDecisionRequest;
use App\Models\ServiceProvider;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BureauProviderVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $providers = ServiceProvider::with('user')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $status))
            ->orderBy('verification_status')->orderBy('business_name')->get();

        return view('bureau.providers.index', compact('providers', 'status'));
    }

    public function show(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load('user');

        return view('bureau.providers.show', ['provider' => $serviceProvider]);
    }

    public function decide(BureauVerificationDecisionRequest $request, ServiceProvider $serviceProvider, NotificationService $notifications): RedirectResponse
    {
        abort_unless($serviceProvider->verification_status === 'pending', 422, 'Only pending providers can be reviewed.');
        $data = $request->validated();
        $serviceProvider->forceFill([
            'verification_status' => $data['decision'] === 'approve' ? 'verified' : 'rejected',
            'verification_notes' => $data['verification_notes'] ?? null,
        ])->save();

        $notifications->createForUser($serviceProvider->user, 'provider_verification', 'Provider verification decision', $data['decision'] === 'approve' ? 'Your provider profile has been verified by the Tourism Bureau and is awaiting administrator activation.' : 'Your provider profile was rejected by the Tourism Bureau.');

        return to_route('bureau.providers.show', $serviceProvider)->with('success', 'Provider verification decision saved.');
    }
}
