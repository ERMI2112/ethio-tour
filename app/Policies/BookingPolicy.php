<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewTourist(User $user, Booking $booking): bool
    {
        return $user->role === 'tourist'
            && $user->tourist !== null
            && (int) $booking->tourist_id === (int) $user->tourist->tourist_id;
    }

    public function cancelTourist(User $user, Booking $booking): bool
    {
        return $this->viewTourist($user, $booking)
            && $booking->status === 'pending';
    }

    public function manageHotelProvider(User $user, Booking $booking): bool
    {
        return $user->role === 'service_provider'
            && $user->serviceProvider !== null
            && $user->serviceProvider->provider_type === 'hotel'
            && $booking->tourismService !== null
            && (int) $booking->tourismService->provider_id === (int) $user->serviceProvider->provider_id;
    }
}
