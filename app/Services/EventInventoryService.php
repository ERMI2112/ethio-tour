<?php

namespace App\Services;

use App\Exceptions\EventInventoryException;
use App\Models\Booking;
use App\Models\CulturalEvent;
use App\Models\EventTicketType;
use App\Models\Tourist;
use Illuminate\Support\Facades\DB;

class EventInventoryService
{
    public const RESERVING_STATUSES = ['accepted', 'payment_pending', 'confirmed'];

    public function availableQuantity(EventTicketType $ticket): int
    {
        if ($ticket->status !== 'active') {
            return 0;
        }

        $claimed = (int) $ticket->reservations()->whereHas('booking', fn ($query) => $query->whereIn('status', self::RESERVING_STATUSES))->sum('quantity');

        return max(0, (int) $ticket->quantity - $claimed);
    }

    public function reserve(Tourist $tourist, CulturalEvent $event, int $ticketTypeId, int $quantity): Booking
    {
        if ($quantity < 1) {
            throw new EventInventoryException('Ticket quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($tourist, $event, $ticketTypeId, $quantity): Booking {
            $event = CulturalEvent::query()->whereKey($event->event_id)->lockForUpdate()->with('service.serviceProvider')->firstOrFail();
            $ticket = EventTicketType::query()->whereKey($ticketTypeId)->lockForUpdate()->first();
            if (! $ticket
                || (int) $ticket->event_id !== (int) $event->event_id
                || $ticket->status !== 'active'
                || $event->status !== 'published'
                || $event->event_date->isPast()
                || ! $event->service?->serviceProvider?->isOperational()) {
                throw new EventInventoryException('This event or ticket type is not bookable.');
            }
            if ($this->availableQuantity($ticket) < $quantity) {
                throw new EventInventoryException('The requested ticket quantity is no longer available.');
            }
            $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $event->service_id, 'guide_id' => null, 'status' => 'accepted', 'booking_date' => now(), 'total_amount' => (float) $ticket->price * $quantity, 'currency' => 'ETB']);
            $booking->eventReservation()->create(['ticket_type_id' => $ticket->ticket_type_id, 'quantity' => $quantity]);

            return $booking;
        }, attempts: 3);
    }
}
