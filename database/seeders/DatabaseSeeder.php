<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * One standard command — `php artisan migrate --seed` (or `db:seed`) —
     * builds the full demo-ready environment. Every seeder in the chain is
     * idempotent, so re-running never creates uncontrolled duplicates.
     *
     * Chain:
     *  1. UatDemoSeeder      — demo accounts, providers, guides, services,
     *                          inventory, events, packages (calls
     *                          GondarPilotSeeder for destination content).
     *  2. DemoContentSeeder  — heritage sites, subscription plans, provider
     *                          subscription.
     *  3. DemoJourneySeeder  — completed booking chain with safe demo
     *                          payments, reviews, and in-app notifications.
     */
    public function run(): void
    {
        $this->call([
            UatDemoSeeder::class,
            DemoContentSeeder::class,
            DemoJourneySeeder::class,
        ]);
    }
}
