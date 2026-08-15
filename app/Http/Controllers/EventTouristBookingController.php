<?php

namespace App\Http\Controllers;

use App\Exceptions\EventInventoryException;
use App\Http\Requests\StoreEventReservationRequest;
use App\Models\CulturalEvent;
use App\Services\EventInventoryService;
use Illuminate\Http\RedirectResponse;

class EventTouristBookingController extends Controller
{
    public function store(StoreEventReservationRequest $request, CulturalEvent $culturalEvent, EventInventoryService $inventory): RedirectResponse
    {
        try {
            $booking = $inventory->reserve($request->user()->tourist, $culturalEvent, (int) $request->validated()['ticket_type_id'], (int) $request->validated()['quantity']);
        } catch (EventInventoryException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return to_route('tourist.reservations.show', $booking)->with('success', 'Event tickets reserved successfully. Payment will be handled later.');
    }
}
