<?php

namespace App\Http\Controllers;

use App\Http\Requests\CulturalEventRequest;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CulturalEventController extends Controller
{
    public function index(Request $request): View
    {
        $events = $request->user()->serviceProvider->events()->with('destination')->orderByDesc('event_date')->get();

        return view('event-organizer.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('event-organizer.events.create', $this->formData());
    }

    public function store(CulturalEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $provider = $request->user()->serviceProvider;
        $event = DB::transaction(function () use ($provider, $data): CulturalEvent {
            $service = $provider->tourismServices()->create(['category_id' => $data['category_id'], 'destination_id' => $data['destination_id'], 'service_name' => $data['event_name'], 'price' => 0, 'description' => $data['description']]);

            return $provider->events()->create(array_merge($data, ['service_id' => $service->service_id]));
        });

        return to_route('event-organizer.events.show', $event)->with('success', 'Event created. Add ticket types before publishing.');
    }

    public function show(CulturalEvent $culturalEvent): View
    {
        $this->owned($culturalEvent, request());
        $culturalEvent->load(['destination', 'ticketTypes']);

        return view('event-organizer.events.show', ['event' => $culturalEvent]);
    }

    public function edit(Request $request, CulturalEvent $culturalEvent): View
    {
        $this->owned($culturalEvent, $request);

        return view('event-organizer.events.edit', array_merge(['event' => $culturalEvent], $this->formData()));
    }

    public function update(CulturalEventRequest $request, CulturalEvent $culturalEvent): RedirectResponse
    {
        $this->owned($culturalEvent, $request);
        $data = $request->validated();
        DB::transaction(function () use ($culturalEvent, $data): void {
            $culturalEvent->update($data);
            $culturalEvent->service?->update(['service_name' => $data['event_name'], 'description' => $data['description'], 'destination_id' => $data['destination_id'], 'category_id' => $data['category_id']]);
        });

        return to_route('event-organizer.events.show', $culturalEvent)->with('success', 'Event updated.');
    }

    public function destroy(Request $request, CulturalEvent $culturalEvent): RedirectResponse
    {
        $this->owned($culturalEvent, $request);
        if ($culturalEvent->service?->bookings()->exists() || $culturalEvent->ticketTypes()->whereHas('reservations')->exists()) {
            return back()->with('error', 'This event has booking history and cannot be deleted. Cancel or unpublish it instead.');
        }
        $culturalEvent->delete();

        return to_route('event-organizer.events.index')->with('success', 'Event removed.');
    }

    private function formData(): array
    {
        return ['categories' => Category::orderBy('category_name')->get(), 'destinations' => Destination::orderBy('name')->get()];
    }

    private function owned(CulturalEvent $event, Request $request): void
    {
        abort_unless((int) $event->provider_id === (int) $request->user()->serviceProvider->provider_id, 403);
    }
}
