<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventTicketTypeRequest;
use App\Models\CulturalEvent;
use App\Models\EventTicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTicketController extends Controller
{
    public function index(CulturalEvent $culturalEvent): View
    {
        $this->owned($culturalEvent);

        return view('event-organizer.tickets.index', ['event' => $culturalEvent, 'tickets' => $culturalEvent->ticketTypes()->orderBy('name')->get()]);
    }

    public function store(EventTicketTypeRequest $request, CulturalEvent $culturalEvent): RedirectResponse
    {
        $culturalEvent->ticketTypes()->create($request->validated());

        return back()->with('success', 'Ticket type added.');
    }

    public function update(EventTicketTypeRequest $request, CulturalEvent $culturalEvent, EventTicketType $eventTicketType): RedirectResponse
    {
        $eventTicketType->update($request->validated());

        return back()->with('success', 'Ticket type updated.');
    }

    public function destroy(Request $request, CulturalEvent $culturalEvent, EventTicketType $eventTicketType): RedirectResponse
    {
        $this->owned($culturalEvent);
        abort_unless((int) $eventTicketType->event_id === (int) $culturalEvent->event_id, 403);
        if ($eventTicketType->reservations()->exists()) {
            return back()->with('error', 'Ticket type has booking history and cannot be deleted.');
        }
        $eventTicketType->delete();

        return back()->with('success', 'Ticket type removed.');
    }

    private function owned(CulturalEvent $event): void
    {
        abort_unless((int) $event->provider_id === (int) request()->user()->serviceProvider->provider_id, 403);
    }
}
