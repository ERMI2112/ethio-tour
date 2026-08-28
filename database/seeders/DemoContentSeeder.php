<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the public catalog and commercial reference data that the demo
 * journey depends on: heritage sites and subscription plans.
 *
 * Everything here is keyed on natural unique attributes, so re-running the
 * seeder never creates duplicates.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHeritageSites();
        $this->seedSubscriptionPlans();
        $this->seedProviderSubscription();
        $this->seedStarterSubscriptions();
    }

    private function seedHeritageSites(): void
    {
        $sites = [
            'Gondar' => [
                [
                    'heritage_type' => 'Fasil Ghebbi Royal Enclosure (UNESCO World Heritage Site)',
                    'opening_hours' => '08:30 - 17:30 daily',
                    'entrance_fee' => 200.00,
                    'latitude' => 12.6087000,
                    'longitude' => 37.4683000,
                ],
                [
                    'heritage_type' => 'Debre Berhan Selassie Church',
                    'opening_hours' => '08:00 - 17:00 daily',
                    'entrance_fee' => 150.00,
                    'latitude' => 12.6054000,
                    'longitude' => 37.4619000,
                ],
                [
                    'heritage_type' => 'Fasilides Bath (Timkat Festival Grounds)',
                    'opening_hours' => '08:30 - 17:30 daily',
                    'entrance_fee' => 100.00,
                    'latitude' => 12.5994000,
                    'longitude' => 37.4594000,
                ],
                [
                    'heritage_type' => 'Kuskuam Royal Complex (Empress Mentewab)',
                    'opening_hours' => '08:00 - 17:00 daily',
                    'entrance_fee' => 100.00,
                    'latitude' => 12.5858000,
                    'longitude' => 37.4264000,
                ],
            ],
            'Lalibela' => [
                [
                    'heritage_type' => 'Rock-Hewn Churches of Lalibela (UNESCO World Heritage Site)',
                    'opening_hours' => '08:00 - 17:30 daily',
                    'entrance_fee' => 1300.00,
                    'latitude' => 12.0318000,
                    'longitude' => 39.0410000,
                ],
            ],
            'Bahir Dar' => [
                [
                    'heritage_type' => 'Lake Tana Island Monasteries (Ura Kidane Mehret)',
                    'opening_hours' => '08:00 - 17:00 daily',
                    'entrance_fee' => 200.00,
                    'latitude' => 11.8780000,
                    'longitude' => 37.3820000,
                ],
            ],
        ];

        foreach ($sites as $destinationName => $destinationSites) {
            $destination = Destination::query()->where('name', $destinationName)->first();

            if (! $destination) {
                continue;
            }

            foreach ($destinationSites as $site) {
                HeritageSite::firstOrCreate(
                    [
                        'destination_id' => $destination->destination_id,
                        'heritage_type' => $site['heritage_type'],
                    ],
                    [
                        'opening_hours' => $site['opening_hours'],
                        'entrance_fee' => $site['entrance_fee'],
                        'latitude' => $site['latitude'],
                        'longitude' => $site['longitude'],
                    ],
                );
            }
        }
    }

    private function seedSubscriptionPlans(): void
    {
        $plans = [
            [
                'plan' => 'Starter',
                'price' => 0.00,
                'commission_rate' => 10.00,
                'duration' => 30,
            ],
            [
                'plan' => 'Growth',
                'price' => 500.00,
                'commission_rate' => 7.50,
                'duration' => 30,
            ],
            [
                'plan' => 'Premium',
                'price' => 1500.00,
                'commission_rate' => 5.00,
                'duration' => 90,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['plan' => $plan['plan']],
                [
                    'price' => $plan['price'],
                    'commission_rate' => $plan['commission_rate'],
                    'duration' => $plan['duration'],
                    'active' => true,
                ],
            );
        }
    }

    private function seedProviderSubscription(): void
    {
        $provider = ServiceProvider::query()
            ->where('business_name', 'Goha Hotel Gondar')
            ->first();
        $plan = SubscriptionPlan::query()->where('plan', 'Growth')->first();

        if (! $provider || ! $plan) {
            return;
        }

        $alreadyActive = ProviderSubscription::query()
            ->where('provider_id', $provider->provider_id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyActive) {
            return;
        }

        ProviderSubscription::create([
            'provider_id' => $provider->provider_id,
            'plan_id' => $plan->plan_id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
        ]);
    }

    /**
     * Every approved demo provider without a plan sits on the free Starter
     * tier, so the commission model is visible end-to-end in the demo.
     * Idempotent: providers with an active subscription are skipped.
     */
    private function seedStarterSubscriptions(): void
    {
        $starter = SubscriptionPlan::query()->where('plan', 'Starter')->first();

        if (! $starter) {
            return;
        }

        ServiceProvider::query()
            ->where('status', 'approved')
            ->whereDoesntHave('providerSubscriptions', fn ($query) => $query->where('status', 'active'))
            ->get()
            ->each(function (ServiceProvider $provider) use ($starter): void {
                ProviderSubscription::create([
                    'provider_id' => $provider->provider_id,
                    'plan_id' => $starter->plan_id,
                    'start_date' => today()->toDateString(),
                    'end_date' => today()->addDays($starter->duration)->toDateString(),
                    'status' => 'active',
                ]);
            });
    }
}
