<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\TourismBureauOfficer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BureauAttractionController extends Controller
{
    public function index(Request $request): View
    {
        $officer = $this->officerOrAbort($request);
        $destinationIds = $officer->destinations()->pluck('destination_id');
        $destinations = $officer->destinations()->orderBy('name')->get();

        $search = $request->string('q')->trim()->value();
        $category = $request->string('category')->trim()->value();
        $selectedDestination = $request->input('destination_id');

        $attractions = Attraction::query()
            ->whereIn('destination_id', $destinationIds)
            ->with('destination')
            ->when($search !== '', fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->when($selectedDestination, fn ($q) => $q->where('destination_id', $selectedDestination))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('bureau.attractions.index', compact('attractions', 'destinations', 'search', 'category', 'selectedDestination'));
    }

    public function create(Request $request): View
    {
        $officer = $this->officerOrAbort($request);
        $destinations = $officer->destinations()->orderBy('name')->get();
        $categories = Attraction::CATEGORIES;

        return view('bureau.attractions.create', compact('destinations', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $officer = $this->officerOrAbort($request);
        $destinationIds = $officer->destinations()->pluck('destination_id')->all();

        $validated = $request->validate([
            'destination_id' => ['required', 'exists:destinations,destination_id', Rule::in($destinationIds)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(Attraction::CATEGORIES)],
            'description' => ['required', 'string'],
            'location_address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'entry_fee' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $images = [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('attractions', 'public');
            $images[] = [
                'path' => $path,
                'alt' => $validated['name'],
                'attribution' => 'Tourism Bureau Official Photography',
                'is_primary' => true,
            ];
        } elseif (!empty($validated['image_url'])) {
            $images[] = [
                'path' => $validated['image_url'],
                'alt' => $validated['name'],
                'attribution' => 'Curated Regional Photography',
                'is_primary' => true,
            ];
        }

        Attraction::create([
            'destination_id' => $validated['destination_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'category' => $validated['category'],
            'description' => $validated['description'],
            'location_address' => $validated['location_address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'entry_fee' => $validated['entry_fee'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'images' => !empty($images) ? $images : null,
        ]);

        return redirect()->route('bureau.attractions.index')->with('success', 'Attraction created and published successfully.');
    }

    public function edit(Request $request, Attraction $attraction): View
    {
        $officer = $this->officerOrAbort($request);
        $this->ensureOwned($request, $attraction);

        $destinations = $officer->destinations()->orderBy('name')->get();
        $categories = Attraction::CATEGORIES;

        return view('bureau.attractions.edit', compact('attraction', 'destinations', 'categories'));
    }

    public function update(Request $request, Attraction $attraction): RedirectResponse
    {
        $officer = $this->officerOrAbort($request);
        $this->ensureOwned($request, $attraction);

        $destinationIds = $officer->destinations()->pluck('destination_id')->all();

        $validated = $request->validate([
            'destination_id' => ['required', 'exists:destinations,destination_id', Rule::in($destinationIds)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(Attraction::CATEGORIES)],
            'description' => ['required', 'string'],
            'location_address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'entry_fee' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $images = $attraction->images ?? [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('attractions', 'public');
            $images = [
                [
                    'path' => $path,
                    'alt' => $validated['name'],
                    'attribution' => 'Tourism Bureau Official Photography',
                    'is_primary' => true,
                ],
            ];
        } elseif (!empty($validated['image_url'])) {
            $images = [
                [
                    'path' => $validated['image_url'],
                    'alt' => $validated['name'],
                    'attribution' => 'Curated Regional Photography',
                    'is_primary' => true,
                ],
            ];
        }

        $attraction->update([
            'destination_id' => $validated['destination_id'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'location_address' => $validated['location_address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'entry_fee' => $validated['entry_fee'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'images' => !empty($images) ? $images : $attraction->images,
        ]);

        return redirect()->route('bureau.attractions.index')->with('success', 'Attraction details updated successfully.');
    }

    public function destroy(Request $request, Attraction $attraction): RedirectResponse
    {
        $this->ensureOwned($request, $attraction);
        $attraction->delete();

        return redirect()->route('bureau.attractions.index')->with('success', 'Attraction removed from publication.');
    }

    private function ensureOwned(Request $request, Attraction $attraction): void
    {
        $officer = $this->officerOrAbort($request);
        $destinationIds = $officer->destinations()->pluck('destination_id')->all();

        abort_unless(in_array((int) $attraction->destination_id, $destinationIds, true), 403, 'Unauthorized access to attraction outside your jurisdiction.');
    }

    private function officerOrAbort(Request $request): TourismBureauOfficer
    {
        return $request->user()->tourismBureauOfficer ?? abort(403);
    }
}
