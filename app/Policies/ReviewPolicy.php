<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewEligibilityService;

class ReviewPolicy
{
    public function create(User $user, Booking $booking): bool
    {
        return $user->is_active
            && $user->role === 'tourist'
            && $user->tourist?->tourist_id === $booking->tourist_id
            && ($booking->review || app(ReviewEligibilityService::class)->isEligible($booking));
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->is_active && $user->role === 'administrator';
    }
}
