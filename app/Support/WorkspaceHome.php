<?php

namespace App\Support;

use App\Models\User;

class WorkspaceHome
{
    public static function routeNameFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return match ($user->role) {
            'administrator' => 'admin.dashboard',
            'tourism_bureau_officer' => 'bureau.dashboard',
            'tour_guide' => 'tour-guide.dashboard',
            'tourist' => 'tourist.dashboard',
            'service_provider' => match ($user->serviceProvider?->provider_type) {
                'hotel' => $user->serviceProvider->isOperational() ? 'hotel.dashboard' : 'provider.status',
                'restaurant' => $user->serviceProvider->isOperational() ? 'restaurant.dashboard' : 'provider.status',
                'transportation_car_rental' => $user->serviceProvider->isOperational() ? 'transportation.dashboard' : 'provider.status',
                'event_organizer' => $user->serviceProvider->isOperational() ? 'event-organizer.dashboard' : 'provider.status',
                default => 'provider.status',
            },
            default => 'account',
        };
    }

    public static function labelFor(?User $user): string
    {
        if (! $user) {
            return 'Account';
        }

        return match ($user->role) {
            'administrator' => 'Admin Dashboard',
            'tourism_bureau_officer' => 'Bureau Dashboard',
            'tour_guide' => 'Tour Guide Portal',
            'tourist' => 'Traveler Workspace',
            'service_provider' => $user->serviceProvider?->isOperational()
                ? 'Provider Workspace'
                : 'Application Status',
            default => 'Account',
        };
    }
}
