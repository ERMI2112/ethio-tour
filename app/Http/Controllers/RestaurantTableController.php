<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantTableRequest;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantTableController extends Controller
{
    public function index(Request $request): View
    {
        $tables = $request->user()->serviceProvider->restaurantTables()
            ->withCount('restaurantReservations')
            ->orderBy('table_number')
            ->get();

        return view('restaurant.tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('restaurant.tables.create');
    }

    public function store(RestaurantTableRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->restaurantTables()->create($request->validated());

        return to_route('restaurant.tables.index')->with('success', 'Restaurant table added.');
    }

    public function edit(Request $request, RestaurantTable $restaurantTable): View
    {
        $this->ensureOwned($request, $restaurantTable);

        return view('restaurant.tables.edit', ['table' => $restaurantTable]);
    }

    public function update(RestaurantTableRequest $request, RestaurantTable $restaurantTable): RedirectResponse
    {
        $this->ensureOwned($request, $restaurantTable);
        $restaurantTable->update($request->validated());

        return to_route('restaurant.tables.index')->with('success', 'Restaurant table updated.');
    }

    public function destroy(Request $request, RestaurantTable $restaurantTable): RedirectResponse
    {
        $this->ensureOwned($request, $restaurantTable);

        if ($restaurantTable->restaurantReservations()->exists()) {
            return back()->with('error', 'This table has reservation history. Mark it inactive instead.');
        }

        $restaurantTable->delete();

        return to_route('restaurant.tables.index')->with('success', 'Restaurant table removed.');
    }

    private function ensureOwned(Request $request, RestaurantTable $table): void
    {
        abort_unless((int) $table->provider_id === (int) $request->user()->serviceProvider->provider_id, 403);
    }
}
