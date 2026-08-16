<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicMapController extends Controller
{
    public function index(Request $request): View
    {
        return view('public.map.index', [
            'destinations' => Destination::query()->orderBy('name')->get(['destination_id', 'name']),
            'selectedCategory' => $request->string('category')->trim()->value(),
            'selectedDestination' => $request->integer('destination') ?: null,
            'search' => $request->string('q')->trim()->value(),
        ]);
    }
}
