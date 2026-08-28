<?php

namespace App\Console\Commands;

use App\Models\ProviderSubscription;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire active provider subscriptions past their end date and send renewal reminders';

    public function handle(NotificationService $notifications): int
    {
        $today = today();

        // Remind providers whose plan expires in exactly 7 or 1 day(s).
        $reminded = 0;
        ProviderSubscription::query()
            ->with(['serviceProvider.user', 'subscriptionPlan'])
            ->where('status', 'active')
            ->where(function ($query) use ($today): void {
                $query->whereDate('end_date', $today->copy()->addDays(7))
                    ->orWhereDate('end_date', $today->copy()->addDay());
            })
            ->get()
            ->each(function (ProviderSubscription $subscription) use ($notifications, $today, &$reminded): void {
                $days = $today->diffInDays($subscription->end_date);
                $notifications->createForUser(
                    $subscription->serviceProvider?->user,
                    'subscription_expiring',
                    'Subscription expiring soon',
                    "Your {$subscription->subscriptionPlan?->plan} plan expires in {$days} day(s) on {$subscription->end_date->toDateString()}. Renew to keep receiving bookings."
                );
                $reminded++;
            });

        // Expire active subscriptions whose end date has passed.
        $expired = 0;
        ProviderSubscription::query()
            ->with(['serviceProvider.user', 'subscriptionPlan'])
            ->where('status', 'active')
            ->whereDate('end_date', '<', $today->toDateString())
            ->get()
            ->each(function (ProviderSubscription $subscription) use ($notifications, &$expired): void {
                $subscription->update(['status' => 'expired']);
                $notifications->createForUserAndAdministrators(
                    $subscription->serviceProvider?->user,
                    'subscription_expired',
                    'Subscription expired',
                    "The {$subscription->subscriptionPlan?->plan} plan for {$subscription->serviceProvider?->business_name} expired on {$subscription->end_date->toDateString()}."
                );
                $expired++;
            });

        $this->info("Reminded {$reminded} provider(s), expired {$expired} subscription(s).");

        return self::SUCCESS;
    }
}
