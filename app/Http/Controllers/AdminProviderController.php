<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProviderStatusRequest;
use App\Models\ServiceProvider;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProviderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $verification = $request->string('verification')->trim()->value();
        $type = $request->string('type')->trim()->value();
        $search = $request->string('q')->trim()->value();
        $providers = ServiceProvider::with('user')->when(in_array($status, ['pending', 'approved', 'suspended', 'rejected'], true), fn ($q) => $q->where('status', $status))->when(in_array($verification, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $verification))->when(in_array($type, ['hotel', 'restaurant', 'transportation_car_rental', 'event_organizer'], true), fn ($q) => $q->where('provider_type', $type))->when($search, fn ($q) => $q->where('business_name', 'like', "%{$search}%"))->orderBy('status')->orderBy('business_name')->paginate(15)->withQueryString();

        return view('admin.providers.index', compact('providers', 'status', 'verification', 'type', 'search'));
    }

    public function show(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load(['user', 'providerSubscriptions.subscriptionPlan']);

        return view('admin.providers.show', ['provider' => $serviceProvider]);
    }

    public function updateStatus(AdminProviderStatusRequest $request, ServiceProvider $serviceProvider, AuditService $audit): RedirectResponse
    {
        $target = $request->validated()['status'];
        $previous = $serviceProvider->status;
        if ($serviceProvider->verification_status !== 'verified') {
            return back()->withErrors(['status' => 'Only Bureau-verified providers can be activated or managed.']);
        }
        $allowed = ($serviceProvider->status === 'pending' && in_array($target, ['approved', 'rejected'], true))
            || ($serviceProvider->status === 'approved' && $target === 'suspended')
            || ($serviceProvider->status === 'suspended' && $target === 'approved');
        abort_unless($allowed, 422, 'Invalid provider status transition.');

        $serviceProvider->forceFill(['status' => $target])->save();
        $audit->record($request->user(), 'provider_status_changed', ServiceProvider::class, $serviceProvider->provider_id, ['from' => $previous, 'to' => $target]);

        return to_route('admin.providers.show', $serviceProvider)->with('success', 'Provider operational status updated.');
    }
}
