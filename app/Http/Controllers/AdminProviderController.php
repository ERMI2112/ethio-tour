<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProviderStatusRequest;
use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\ProviderBalanceService;
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
        $providers = ServiceProvider::with(['user', 'providerSubscriptions.subscriptionPlan'])
            ->when(in_array($status, ['pending', 'approved', 'suspended', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when(in_array($verification, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $verification))
            ->when(in_array($type, ['hotel', 'restaurant', 'transportation_car_rental', 'event_organizer'], true), fn ($q) => $q->where('provider_type', $type))
            ->when($search, fn ($q) => $q->where('business_name', 'like', "%{$search}%"))
            ->orderBy('status')
            ->orderBy('business_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.providers.index', compact('providers', 'status', 'verification', 'type', 'search'));
    }

    public function show(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load(['user', 'providerSubscriptions.subscriptionPlan', 'destination']);
        $subscriptionPlans = SubscriptionPlan::where('active', true)->orderBy('plan')->get();

        return view('admin.providers.show', [
            'provider' => $serviceProvider,
            'subscriptionPlans' => $subscriptionPlans,
            'ledgerTotals' => app(ProviderBalanceService::class)->totalsFor($serviceProvider),
        ]);
    }

    public function updateStatus(AdminProviderStatusRequest $request, ServiceProvider $serviceProvider, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        $target = $request->validated()['status'];
        $previous = $serviceProvider->status;

        // Invariant: Final platform activation requires prior Bureau regulatory verification
        if ($target === 'approved' && $serviceProvider->verification_status !== 'verified') {
            return back()->withErrors(['status' => 'Cannot activate provider: Municipal Tourism Bureau regulatory verification is required first.']);
        }

        $allowed = ($serviceProvider->status === 'pending' && in_array($target, ['approved', 'rejected'], true))
            || ($serviceProvider->status === 'approved' && $target === 'suspended')
            || ($serviceProvider->status === 'suspended' && $target === 'approved');
        abort_unless($allowed, 422, 'Invalid provider status transition.');

        $serviceProvider->forceFill(['status' => $target])->save();
        $audit->record($request->user(), 'provider_status_changed', ServiceProvider::class, $serviceProvider->provider_id, ['from' => $previous, 'to' => $target]);
        $notifications->createForUserAndAdministrators($serviceProvider->user, 'provider_'.($target === 'approved' ? 'approved' : $target), 'Provider platform status updated', 'Your provider platform status is now '.str_replace('_', ' ', $target).'.', $request->user()->user_id);

        return to_route('admin.providers.show', $serviceProvider)->with('success', 'Provider platform status updated successfully.');
    }

    public function assignSubscription(Request $request, ServiceProvider $serviceProvider, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,plan_id'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Deactivate existing active subscriptions for this provider
        $serviceProvider->providerSubscriptions()->where('status', 'active')->update(['status' => 'inactive']);

        // Create new active commercial subscription
        $subscription = ProviderSubscription::create([
            'provider_id' => $serviceProvider->provider_id,
            'plan_id' => $plan->plan_id,
            'start_date' => today(),
            'end_date' => today()->addDays($plan->duration),
            'status' => 'active',
        ]);

        $audit->record($request->user(), 'provider_subscription_assigned', ProviderSubscription::class, $subscription->provider_subscription_id, [
            'provider_id' => $serviceProvider->provider_id,
            'plan_id' => $plan->plan_id,
            'plan_name' => $plan->plan,
            'commission_rate' => $plan->commission_rate,
        ]);

        $notifications->createForUserAndAdministrators(
            $serviceProvider->user,
            'subscription_assigned',
            'Commercial subscription assigned',
            "Your Ethio Tour commercial package has been updated to the {$plan->plan} tier ({$plan->commission_rate}% commission).",
            $request->user()->user_id
        );

        return to_route('admin.providers.show', $serviceProvider)->with('success', "Commercial plan '{$plan->plan}' assigned to {$serviceProvider->business_name}.");
    }
}
