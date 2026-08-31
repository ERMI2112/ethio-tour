<?php

namespace App\Http\Controllers;

use App\Exceptions\EventInventoryException;
use App\Http\Requests\StoreEventReservationRequest;
use App\Models\CulturalEvent;
use App\Services\EventInventoryService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;

class EventTouristBookingController extends Controller
{
    public function store(StoreEventReservationRequest $request, CulturalEvent $culturalEvent, EventInventoryService $inventory, NotificationService $notifications): RedirectResponse
    {
        $culturalEvent->loadMissing('serviceProvider');

        if ($culturalEvent->serviceProvider?->hasExpiredSubscription()) {
            return back()->with('error', 'This provider\'s subscription has expired, so new bookings are temporarily unavailable.');
        }

        try {
            $booking = $inventory->reserve($request->user()->tourist, $culturalEvent, (int) $request->validated()['ticket_type_id'], (int) $request->validated()['quantity']);
        } catch (EventInventoryException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        $notifications->createForUserAndAdministrators(
            $culturalEvent->serviceProvider?->user,
            'event_booking',
            'New event ticket booking',
            'A tourist reserved tickets for '.$culturalEvent->event_name.'.',
            null,
            route('event-organizer.events.index'),
        );
        $notifications->createForUserAndAdministrators(
            $request->user(),
            'event_booking',
            'Event tickets reserved',
            'Your event tickets were reserved successfully. Complete payment from your booking when it is ready.',
            null,
            route('tourist.reservations.show', $booking),
        );

        return to_route('tourist.reservations.show', $booking)->with('success', 'Event tickets reserved successfully. Review your booking to continue to payment.');
    }
}
