<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TourPackageController extends Controller
{
    public function index(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide, 403, 'Tour guide profile not found.');

        $packages = $guide->tourPackages()->with('destination')->latest()->get();

        return view('tour-guide.tours', compact('guide', 'packages'));
    }

    public function create(Request $request): View
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide, 403, 'Tour guide profile not found.');

        $destinations = Destination::orderBy('name')->get();

        return view('tour-guide.packages.create', compact('guide', 'destinations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide, 403, 'Tour guide profile not found.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'destination_id' => ['nullable', 'exists:destinations,destination_id'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_group_size' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty_level' => ['required', 'in:easy,moderate,challenging'],
            'description' => ['required', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'itinerary_days' => ['nullable', 'array'],
            'itinerary_days.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary_days.*.description' => ['nullable', 'string', 'max:2000'],
            'included' => ['nullable', 'string', 'max:2000'],
            'excluded' => ['nullable', 'string', 'max:2000'],
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('packages', 'public');
        }

        // Format itinerary JSON
        $itinerary = [];
        if (! empty($validated['itinerary_days'])) {
            foreach ($validated['itinerary_days'] as $idx => $dayData) {
                if (! empty($dayData['title'])) {
                    $itinerary[] = [
                        'day' => $idx + 1,
                        'title' => $dayData['title'],
                        'description' => $dayData['description'] ?? '',
                    ];
                }
            }
        }

        // Format included / excluded
        $included = ! empty($validated['included'])
            ? array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['included']))))
            : [];
        $excluded = ! empty($validated['excluded'])
            ? array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['excluded']))))
            : [];

        TourPackage::create([
            'guide_id' => $guide->guide_id,
            'destination_id' => $validated['destination_id'] ?? null,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(5),
            'duration_days' => $validated['duration_days'],
            'price' => $validated['price'],
            'max_group_size' => $validated['max_group_size'],
            'difficulty_level' => $validated['difficulty_level'],
            'description' => $validated['description'],
            'itinerary' => $itinerary,
            'included' => array_values($included),
            'excluded' => array_values($excluded),
            'cover_image' => $coverImagePath,
            'is_active' => true,
        ]);

        return redirect()->route('tour-guide.tours')->with('success', 'Tour package created and published successfully.');
    }

    public function edit(Request $request, TourPackage $package): View
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide || (int) $package->guide_id !== (int) $guide->guide_id, 403, 'Unauthorized access to tour package.');

        $destinations = Destination::orderBy('name')->get();

        return view('tour-guide.packages.edit', compact('guide', 'package', 'destinations'));
    }

    public function update(Request $request, TourPackage $package): RedirectResponse
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide || (int) $package->guide_id !== (int) $guide->guide_id, 403, 'Unauthorized access to tour package.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'destination_id' => ['nullable', 'exists:destinations,destination_id'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_group_size' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty_level' => ['required', 'in:easy,moderate,challenging'],
            'description' => ['required', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'itinerary_days' => ['nullable', 'array'],
            'itinerary_days.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary_days.*.description' => ['nullable', 'string', 'max:2000'],
            'included' => ['nullable', 'string', 'max:2000'],
            'excluded' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('cover_image')) {
            $package->cover_image = $request->file('cover_image')->store('packages', 'public');
        }

        $itinerary = [];
        if (! empty($validated['itinerary_days'])) {
            foreach ($validated['itinerary_days'] as $idx => $dayData) {
                if (! empty($dayData['title'])) {
                    $itinerary[] = [
                        'day' => $idx + 1,
                        'title' => $dayData['title'],
                        'description' => $dayData['description'] ?? '',
                    ];
                }
            }
        }

        $included = ! empty($validated['included'])
            ? array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['included']))))
            : [];
        $excluded = ! empty($validated['excluded'])
            ? array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['excluded']))))
            : [];

        $package->update([
            'destination_id' => $validated['destination_id'] ?? null,
            'title' => $validated['title'],
            'duration_days' => $validated['duration_days'],
            'price' => $validated['price'],
            'max_group_size' => $validated['max_group_size'],
            'difficulty_level' => $validated['difficulty_level'],
            'description' => $validated['description'],
            'itinerary' => $itinerary,
            'included' => array_values($included),
            'excluded' => array_values($excluded),
        ]);

        return redirect()->route('tour-guide.tours')->with('success', 'Tour package updated successfully.');
    }

    public function toggle(Request $request, TourPackage $package): RedirectResponse
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide || (int) $package->guide_id !== (int) $guide->guide_id, 403, 'Unauthorized.');

        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('success', $package->is_active ? 'Tour package published.' : 'Tour package hidden.');
    }

    public function destroy(Request $request, TourPackage $package): RedirectResponse
    {
        $guide = $request->user()->tourGuide;
        abort_if(! $guide || (int) $package->guide_id !== (int) $guide->guide_id, 403, 'Unauthorized.');

        $package->delete();

        return redirect()->route('tour-guide.tours')->with('success', 'Tour package deleted.');
    }
}
