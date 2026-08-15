<?php

namespace App\Http\Controllers;

use App\Http\Requests\BureauVerificationDecisionRequest;
use App\Models\ServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BureauProviderVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $providers = ServiceProvider::with('user')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->orderBy('status')->orderBy('business_name')->get();

        return view('bureau.providers.index', compact('providers', 'status'));
    }

    public function show(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load('user');

        return view('bureau.providers.show', ['provider' => $serviceProvider]);
    }

    public function decide(BureauVerificationDecisionRequest $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        abort_unless($serviceProvider->status === 'pending', 422, 'Only pending providers can be reviewed.');
        $data = $request->validated();
        $serviceProvider->update([
            'status' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
            'verification_notes' => $data['verification_notes'] ?? null,
        ]);

        return to_route('bureau.providers.show', $serviceProvider)->with('success', 'Provider verification decision saved.');
    }
}
