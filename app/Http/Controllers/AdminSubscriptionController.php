<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminSubscriptionPlanRequest;
use App\Models\ProviderSubscription;
use App\Models\SubscriptionPlan;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function index(): View
    {
        return view('admin.subscriptions.index', ['plans' => SubscriptionPlan::withCount('providerSubscriptions')->orderBy('plan')->get(), 'subscriptions' => ProviderSubscription::with(['serviceProvider', 'subscriptionPlan'])->latest()->get()]);
    }

    public function store(AdminSubscriptionPlanRequest $request, AuditService $audit): RedirectResponse
    {
        $plan = SubscriptionPlan::create($request->validated());
        $audit->record($request->user(), 'subscription_plan_created', SubscriptionPlan::class, $plan->plan_id);

        return back()->with('success', 'Subscription plan created.');
    }

    public function update(AdminSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan, AuditService $audit): RedirectResponse
    {
        $subscriptionPlan->update($request->validated());
        $audit->record($request->user(), 'subscription_plan_updated', SubscriptionPlan::class, $subscriptionPlan->plan_id);

        return back()->with('success', 'Subscription plan updated.');
    }

    public function toggle(Request $request, SubscriptionPlan $subscriptionPlan, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);
        $active = (bool) $validated['active'];
        $previous = (bool) $subscriptionPlan->active;
        $subscriptionPlan->update(['active' => $active]);
        $audit->record($request->user(), $active ? 'subscription_plan_activated' : 'subscription_plan_deactivated', SubscriptionPlan::class, $subscriptionPlan->plan_id, ['from' => $previous, 'to' => $active]);

        return back()->with('success', $active ? 'Subscription plan activated.' : 'Subscription plan deactivated. Existing subscriptions were preserved.');
    }
}
