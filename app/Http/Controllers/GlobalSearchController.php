<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalSearchController extends Controller
{
    public function index(Request $request, GlobalSearchService $searchService): View
    {
        return view('public.search.index', [
            'results' => $searchService->search($request),
            'types' => $searchService->types(),
            'categories' => Category::query()->orderBy('category_name')->get(),
            'destinations' => Destination::query()->orderBy('name')->get(['destination_id', 'name']),
            'filters' => [
                'q' => $request->string('q')->trim()->value(),
                'type' => $request->string('type')->trim()->value(),
                'category' => $request->integer('category') ?: null,
                'destination' => $request->integer('destination') ?: null,
                'date' => $searchService->normalizeDate($request->input('date')),
                'rating' => $request->input('rating'),
            ],
        ]);
    }
}
