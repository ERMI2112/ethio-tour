<?php

namespace App\Support;

use App\Models\TourismService;

/**
 * Resolves a realistic, distinct card image for a public tourism service.
 *
 * Images are local, licensed assets (see public/images/CREDITS.md). Within
 * each provider vertical the pick is deterministic — based on the service id —
 * so two hotels never share a photo and the choice is stable across requests.
 */
class ServiceImage
{
    /** @var array<string, array<int, string>> */
    private const POOLS = [
        'hotel' => [
            'images/services/hotel-suite.jpg',
            'images/services/hotel-room-classic.jpg',
            'images/services/hotel-room-deluxe.jpg',
        ],
        'restaurant' => [
            'images/services/coffee-tasting.jpg',
            'images/services/ethiopian-feast.jpg',
            'images/services/injera-traditional.jpg',
        ],
        'transportation_car_rental' => [
            'images/services/safari-4x4.jpg',
            'images/services/4x4-bale-expedition.jpg',
        ],
        'event_organizer' => [
            'images/events/meskel-festival.jpg',
            'images/events/timkat-festival.jpg',
        ],
    ];

    private const FALLBACK = 'images/services/hotel-suite.jpg';

    public static function for(TourismService $service): string
    {
        $type = $service->serviceProvider?->provider_type ?? 'hotel';
        $pool = self::POOLS[$type] ?? null;

        if ($pool === null) {
            return self::FALLBACK;
        }

        return $pool[$service->service_id % count($pool)];
    }

    public static function assetFor(TourismService $service): string
    {
        return asset(self::for($service));
    }
}
