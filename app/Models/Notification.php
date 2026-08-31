<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'channel', 'sent_date', 'read_status', 'action_url'];

    protected function casts(): array
    {
        return ['sent_date' => 'datetime', 'read_status' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getTargetUrlAttribute(): string
    {
        if (! empty($this->action_url)) {
            return $this->action_url;
        }

        $user = $this->user;
        $role = $user?->role;
        $type = (string) $this->type;
        $message = (string) $this->message;

        // Parse booking ID from message if available (e.g., "#9", "#BK-00009")
        $bookingId = null;
        if (preg_match('/#(?:BK-)?0*(\d+)/i', $message, $matches)) {
            $bookingId = (int) $matches[1];
        }

        if ($role === 'tourist') {
            if ($bookingId) {
                return route('tourist.reservations.show', $bookingId);
            }
            if ($type === 'review_available') {
                return route('tourist.reviews.index');
            }
            if (in_array($type, [
                'booking_accepted', 'booking_rejected', 'booking_confirmed',
                'booking_request', 'booking_cancelled', 'payment_success',
                'event_booking', 'reservation_accepted', 'reservation_rejected',
                'reservation_request', 'booking_completed',
            ], true)) {
                return route('tourist.reservations.index');
            }

            return route('tourist.dashboard');
        }

        if ($role === 'tour_guide') {
            if ($bookingId) {
                return route('tour-guide.requests.show', $bookingId);
            }
            if (in_array($type, ['booking_request', 'booking_accepted', 'booking_rejected', 'booking_cancelled'], true)) {
                return route('tour-guide.requests.index');
            }
            if ($type === 'review_available') {
                return route('tour-guide.reviews');
            }
            if (in_array($type, ['guide_verification', 'guide_final_approval', 'verification_document'], true)) {
                return route('tour-guide.profile');
            }

            return route('tour-guide.dashboard');
        }

        if ($role === 'service_provider') {
            $providerType = $user?->serviceProvider?->provider_type;

            if (in_array($type, ['booking_request', 'reservation_request', 'booking_accepted', 'reservation_accepted', 'booking_rejected', 'reservation_rejected'], true)) {
                if ($providerType === 'hotel') {
                    return $bookingId ? route('hotel.reservations.show', $bookingId) : route('hotel.reservations.index');
                }
                if ($providerType === 'restaurant') {
                    return $bookingId ? route('restaurant.reservations.show', $bookingId) : route('restaurant.reservations.index');
                }
                if ($providerType === 'transportation') {
                    return $bookingId ? route('transportation.reservations.show', $bookingId) : route('transportation.reservations.index');
                }
                if ($providerType === 'event_organizer') {
                    return route('event-organizer.events.index');
                }
            }

            if (in_array($type, ['provider_verification', 'verification_document', 'provider_approved'], true)) {
                return route('provider.profile');
            }

            if (in_array($type, ['subscription_expiring', 'subscription_expired'], true)) {
                if ($providerType === 'hotel') {
                    return route('hotel.earnings');
                }
                if ($providerType === 'restaurant') {
                    return route('restaurant.earnings');
                }
                if ($providerType === 'transportation') {
                    return route('transportation.earnings');
                }
                if ($providerType === 'event_organizer') {
                    return route('event-organizer.earnings');
                }
            }

            return route('provider.dashboard');
        }

        if ($role === 'tourism_bureau_officer') {
            if (in_array($type, ['guide_registration', 'guide_verification'], true)) {
                return route('bureau.guides.index');
            }
            if (in_array($type, ['provider_registration', 'provider_verification'], true)) {
                return route('bureau.providers.index');
            }
            if ($type === 'verification_document') {
                return route('bureau.documents.index');
            }

            return route('bureau.dashboard');
        }

        if ($role === 'administrator') {
            if (in_array($type, ['guide_final_approval', 'guide_registration'], true)) {
                return route('admin.guides.index');
            }
            if (in_array($type, ['provider_registration', 'provider_approved'], true)) {
                return route('admin.providers.index');
            }
            if ($type === 'review_available') {
                return route('admin.reviews.index');
            }

            return route('admin.dashboard');
        }

        return route('notifications.index');
    }

    public function getActionLabelAttribute(): string
    {
        $type = (string) $this->type;
        $role = $this->user?->role;

        if (in_array($type, ['booking_accepted', 'reservation_accepted'], true)) {
            return $role === 'tourist' ? 'View & Pay' : 'View Reservation';
        }
        if (in_array($type, ['payment_success', 'booking_confirmed'], true)) {
            return 'View Booking';
        }
        if (in_array($type, ['booking_request', 'reservation_request'], true)) {
            return in_array($role, ['service_provider', 'tour_guide'], true) ? 'Review Request' : 'View Reservation';
        }
        if ($type === 'review_available') {
            return 'Leave Review';
        }
        if (in_array($type, ['guide_verification', 'guide_final_approval', 'provider_verification', 'provider_approved'], true)) {
            return 'View Status';
        }
        if ($type === 'verification_document') {
            return 'View Documents';
        }

        return 'View Details';
    }

    public function getCategoryIconAttribute(): string
    {
        $type = (string) $this->type;
        $title = strtolower((string) $this->title);
        $message = strtolower((string) $this->message);

        if (str_contains($type, 'payment') || str_contains($title, 'payment') || str_contains($message, 'payment')) {
            return 'bi-credit-card-2-front';
        }
        if (str_contains($type, 'hotel') || str_contains($title, 'hotel') || str_contains($message, 'hotel') || str_contains($message, 'room')) {
            return 'bi-building';
        }
        if (str_contains($type, 'restaurant') || str_contains($title, 'restaurant') || str_contains($message, 'table') || str_contains($message, 'restaurant')) {
            return 'bi-cup-hot';
        }
        if (str_contains($type, 'transport') || str_contains($title, 'transport') || str_contains($message, 'vehicle')) {
            return 'bi-car-front';
        }
        if (str_contains($type, 'guide') || str_contains($title, 'guide') || str_contains($message, 'guide')) {
            return 'bi-person-badge';
        }
        if (str_contains($type, 'event') || str_contains($title, 'event') || str_contains($message, 'ticket')) {
            return 'bi-calendar-event';
        }
        if (str_contains($type, 'review') || str_contains($title, 'review')) {
            return 'bi-star-fill';
        }
        if (str_contains($type, 'verification') || str_contains($type, 'approval') || str_contains($title, 'verification')) {
            return 'bi-shield-check';
        }

        return 'bi-bell';
    }

    public function getCategoryBadgeAttribute(): string
    {
        $type = (string) $this->type;

        if (str_contains($type, 'accepted') || str_contains($type, 'confirmed') || str_contains($type, 'success') || str_contains($type, 'approved')) {
            return 'bg-success-subtle text-success border border-success-subtle';
        }
        if (str_contains($type, 'rejected') || str_contains($type, 'cancelled') || str_contains($type, 'expired')) {
            return 'bg-danger-subtle text-danger border border-danger-subtle';
        }
        if (str_contains($type, 'request') || str_contains($type, 'expiring')) {
            return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        }

        return 'bg-primary-subtle text-primary border border-primary-subtle';
    }
}
