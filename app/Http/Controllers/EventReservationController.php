<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventReservationController extends Controller
{
    public function index(Request $request): View
    {
        $eventIds = $request->user()->serviceProvider->events()->pluck('event_id');
        $bookings = Booking::with(['tourist', 'eventReservation.ticketType.event'])->whereHas('eventReservation.ticketType', fn ($q) => $q->whereIn('event_id', $eventIds))->orderByDesc('booking_id')->paginate(10);

        return view('event-organizer.bookings.index', compact('bookings'));
    }
}
