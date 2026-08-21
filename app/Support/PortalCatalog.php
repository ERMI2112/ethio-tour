<?php

namespace App\Support;

class PortalCatalog
{
    /**
     * The nine operational surfaces of the platform.
     *
     * @return list<array{key:string,name:string,audience:string,summary:string,capabilities:list<string>,entry:string}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'public',
                'name' => 'Public discovery',
                'audience' => 'Guests and travelers',
                'summary' => 'Search destinations, heritage, stays, dining, transport, guides, and events from verified public records.',
                'capabilities' => ['Destination guides', 'Map discovery', 'Smart Trip planning'],
                'entry' => 'home',
            ],
            [
                'key' => 'tourist',
                'name' => 'Traveler workspace',
                'audience' => 'Registered tourists',
                'summary' => 'Manage bookings, payments, itineraries, notifications, and reviews after completed journeys.',
                'capabilities' => ['Reservations', 'Chapa payments', 'Reviews'],
                'entry' => 'login',
            ],
            [
                'key' => 'tour_guide',
                'name' => 'Tour guide operations',
                'audience' => 'Licensed tour guides',
                'summary' => 'Maintain a verified profile, publish availability, and accept or decline booking requests.',
                'capabilities' => ['Profile', 'Availability', 'Booking requests'],
                'entry' => 'login',
            ],
            [
                'key' => 'hotel',
                'name' => 'Hotel operations',
                'audience' => 'Verified hotel providers',
                'summary' => 'Publish room types, manage physical rooms, and process stay reservations through a dedicated workspace.',
                'capabilities' => ['Room types', 'Inventory', 'Reservations'],
                'entry' => 'login',
            ],
            [
                'key' => 'restaurant',
                'name' => 'Restaurant operations',
                'audience' => 'Verified dining providers',
                'summary' => 'Offer public menus or services, configure tables, and accept or decline dining reservations.',
                'capabilities' => ['Services', 'Tables', 'Reservations'],
                'entry' => 'login',
            ],
            [
                'key' => 'transportation',
                'name' => 'Transportation operations',
                'audience' => 'Verified car-rental operators',
                'summary' => 'List vehicles and transfer services, then manage pickup reservations from one operations desk.',
                'capabilities' => ['Services', 'Vehicles', 'Reservations'],
                'entry' => 'login',
            ],
            [
                'key' => 'event_organizer',
                'name' => 'Event operations',
                'audience' => 'Verified event organizers',
                'summary' => 'Publish cultural events, configure ticket types, and monitor attendee bookings.',
                'capabilities' => ['Events', 'Tickets', 'Bookings'],
                'entry' => 'login',
            ],
            [
                'key' => 'bureau',
                'name' => 'Tourism bureau operations',
                'audience' => 'Provisioned bureau officers',
                'summary' => 'Verify guides and providers, maintain museum records, and review regional operational reports.',
                'capabilities' => ['Guide verification', 'Provider verification', 'Museum records'],
                'entry' => 'login',
            ],
            [
                'key' => 'administrator',
                'name' => 'Platform administration',
                'audience' => 'Provisioned administrators',
                'summary' => 'Activate verified providers, govern users and subscriptions, moderate reviews, and audit platform changes.',
                'capabilities' => ['Provider activation', 'User access', 'Audit log'],
                'entry' => 'login',
            ],
        ];
    }
}
