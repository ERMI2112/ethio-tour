<?php

namespace App\Policies;

use App\Models\ProviderLedgerEntry;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\User;

/**
 * Ledger visibility: owners see their own entries, administrators inspect
 * everything, everyone else sees nothing. There are intentionally no
 * update/delete abilities — the ledger is append-only.
 */
class ProviderLedgerEntryPolicy
{
    public function view(User $user, ProviderLedgerEntry $entry): bool
    {
        if ($user->role === 'administrator') {
            return true;
        }

        return match ($entry->payable_type) {
            ServiceProvider::class => (int) $entry->payable_id === (int) ($user->serviceProvider?->provider_id ?? 0),
            TourGuide::class => (int) $entry->payable_id === (int) ($user->tourGuide?->guide_id ?? 0),
            default => false,
        };
    }
}
