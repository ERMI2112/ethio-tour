<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveTripItemRequest;
use App\Http\Requests\ReorderTripItemRequest;
use App\Http\Requests\StoreTripItemRequest;
use App\Http\Requests\UpdateTripItemNotesRequest;
use App\Models\Trip;
use App\Models\TripItem;
use App\Services\GlobalSearchService;
use App\Services\TripItemTargetResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SmartTripItemController extends Controller
{
    public function create(Request $request, Trip $trip, GlobalSearchService $search): View
    {
        Gate::authorize('view', $trip);

        return view('smart-trip.items.create', [
            'trip' => $trip->load('items'),
            'results' => $search->search($request),
            'query' => $request->string('q')->trim()->value(),
            'type' => $request->string('type')->trim()->value(),
        ]);
    }

    public function store(StoreTripItemRequest $request, Trip $trip, TripItemTargetResolver $resolver): RedirectResponse
    {
        $validated = $request->validated();
        $target = $resolver->resolve($validated['item_type'], (int) $validated['item_id']);
        abort_unless($target !== null, 422, 'This tourism resource is not currently public or bookable.');
        $this->ensureDateWithinTrip($trip, $validated['planned_date']);

        $trip->items()->firstOrCreate([
            'item_type' => $validated['item_type'],
            'item_id' => $validated['item_id'],
        ], [
            'planned_date' => $validated['planned_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'sequence' => ((int) $trip->items()->max('sequence')) + 1,
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'status' => 'planned',
        ]);
        if ($trip->status === 'draft') {
            $trip->update(['status' => 'planned']);
        }

        return to_route('smart-trip.show', $trip)->with('success', 'Item added to your itinerary.');
    }

    public function destroy(Trip $trip, TripItem $tripItem): RedirectResponse
    {
        Gate::authorize('update', $trip);
        $this->ensureItemBelongsToTrip($trip, $tripItem);
        $tripItem->delete();

        return back()->with('success', 'Itinerary item removed.');
    }

    public function move(MoveTripItemRequest $request, Trip $trip, TripItem $tripItem): RedirectResponse
    {
        $this->ensureItemBelongsToTrip($trip, $tripItem);
        $this->ensureDateWithinTrip($trip, $request->validated()['planned_date']);
        $tripItem->update(['planned_date' => $request->validated()['planned_date']]);

        return back()->with('success', 'Itinerary item moved to the selected day.');
    }

    public function notes(UpdateTripItemNotesRequest $request, Trip $trip, TripItem $tripItem): RedirectResponse
    {
        $tripItem->update(['notes' => $request->validated()['notes'] ?? null]);

        return back()->with('success', 'Itinerary notes updated.');
    }

    public function position(ReorderTripItemRequest $request, Trip $trip, TripItem $tripItem): RedirectResponse
    {
        $this->ensureItemBelongsToTrip($trip, $tripItem);
        $direction = $request->validated()['direction'];
        $neighbor = $trip->items()
            ->where('planned_date', $tripItem->planned_date)
            ->where('sequence', $direction === 'up' ? '<' : '>', $tripItem->sequence)
            ->orderBy('sequence', $direction === 'up' ? 'desc' : 'asc')
            ->first();
        if ($neighbor !== null) {
            [$tripItem->sequence, $neighbor->sequence] = [$neighbor->sequence, $tripItem->sequence];
            $tripItem->save();
            $neighbor->save();
        }

        return back()->with('success', 'Itinerary order updated.');
    }

    private function ensureItemBelongsToTrip(Trip $trip, TripItem $item): void
    {
        Gate::authorize('update', $trip);
        abort_unless((int) $item->trip_id === (int) $trip->trip_id, 404);
    }

    private function ensureDateWithinTrip(Trip $trip, string $date): void
    {
        abort_unless($date >= $trip->start_date->toDateString() && $date <= $trip->end_date->toDateString(), 422, 'The itinerary date must be within the trip dates.');
    }
}
