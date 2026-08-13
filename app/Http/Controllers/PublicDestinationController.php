<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDestinationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $destinations = Destination::query()
            ->withCount(['heritageSites', 'tourismServices'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->get();

        return view('public.destinations.index', compact('destinations', 'search'));
    }

    public function show(Destination $destination): View
    {
        $destination->load([
            'heritageSites' => fn ($query) => $query->orderBy('heritage_type'),
            'tourismServices' => fn ($query) => $query->with(['category', 'serviceProvider'])->orderBy('service_name'),
        ]);

        return view('public.destinations.show', compact('destination'));
    }
}
