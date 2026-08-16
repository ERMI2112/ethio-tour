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
        try {
            $booking = $inventory->reserve($request->user()->tourist, $culturalEvent, (int) $request->validated()['ticket_type_id'], (int) $request->validated()['quantity']);
        } catch (EventInventoryException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        $notifications->createForUser($culturalEvent->serviceProvider?->user, 'event_booking', 'New event ticket booking', 'A tourist reserved tickets for '.$culturalEvent->event_name.'.');
        $notifications->createForUser($request->user(), 'event_booking', 'Event tickets reserved', 'Your event tickets were reserved successfully. Payment remains deferred.');

        return to_route('tourist.reservations.show', $booking)->with('success', 'Event tickets reserved successfully. Payment will be handled later.');
    }
}
