<?php

namespace App\Http\Controllers;

use App\Http\Requests\BureauVerificationDecisionRequest;
use App\Models\ServiceProvider;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BureauProviderVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $platformStatus = $request->string('platform_status')->trim()->value();
        $search = $request->string('q')->trim()->value();
        $type = $request->string('type')->trim()->value();
        $providers = ServiceProvider::with('user')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $status))
            ->when(in_array($platformStatus, ['pending', 'approved', 'suspended', 'rejected'], true), fn ($q) => $q->where('status', $platformStatus))
            ->when($type !== '', fn ($q) => $q->where('provider_type', $type))
            ->when($search !== '', fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$search}%"));
            }))
            ->orderBy('verification_status')->orderBy('business_name')->paginate(15)->withQueryString();

        return view('bureau.providers.index', compact('providers', 'status', 'platformStatus', 'search', 'type'));
    }

    public function show(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load(['user', 'verificationDocuments']);

        return view('bureau.providers.show', ['provider' => $serviceProvider]);
    }

    public function decide(BureauVerificationDecisionRequest $request, ServiceProvider $serviceProvider, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        abort_unless($serviceProvider->verification_status === 'pending', 422, 'Only pending providers can be reviewed.');
        $data = $request->validated();
        $serviceProvider->forceFill([
            'verification_status' => $data['decision'] === 'approve' ? 'verified' : 'rejected',
            'verification_notes' => $data['verification_notes'] ?? null,
        ])->save();
        $audit->record($request->user(), 'provider_verification_decided', ServiceProvider::class, $serviceProvider->provider_id, [
            'decision' => $data['decision'],
            'verification_notes' => $data['verification_notes'] ?? null,
        ]);

        $notifications->createForUserAndAdministrators($serviceProvider->user, 'provider_verification', 'Provider verification decision', $data['decision'] === 'approve' ? 'Your provider profile has been verified by the Tourism Bureau and is awaiting administrator activation.' : 'Your provider profile was rejected by the Tourism Bureau.', $request->user()->user_id);

        return to_route('bureau.providers.show', $serviceProvider)->with('success', 'Provider verification decision saved.');
    }
}
